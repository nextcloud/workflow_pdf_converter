<?php

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\WorkflowPDFConverter\BackgroundJobs;

use Exception;
use OC\Files\Filesystem;
use OC\Files\View;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\QueuedJob;
use OCP\Files\InvalidPathException;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IConfig;
use OCP\ITempManager;
use Psr\Log\LoggerInterface;

/**
 * @psalm-api
 */
class Convert extends QueuedJob {
	protected IConfig $config;
	protected ITempManager $tempManager;
	protected LoggerInterface $logger;
	private IRootFolder $rootFolder;

	public function __construct(
		IConfig $config,
		ITempManager $tempManager,
		LoggerInterface $logger,
		IRootFolder $rootFolder,
		ITimeFactory $timeFactory,
	) {
		parent::__construct($timeFactory);
		$this->config = $config;
		$this->tempManager = $tempManager;
		$this->logger = $logger;
		$this->rootFolder = $rootFolder;
	}

	/**
	 * @param mixed $argument
	 *
	 * @throws Exception
	 * @throws InvalidPathException
	 * @throws NotPermittedException
	 * @throws NotFoundException
	 *
	 * @return void
	 */
	#[\Override]
	protected function run($argument) {
		$command = $this->getCommand();
		if ($command === null) {
			$this->logger->error('Can not find office path. Please make sure to configure "preview_libreoffice_path" in your config file.');
		}

		$path = (string)$argument['path'];
		$originalFileMode = (string)$argument['originalFileMode'];
		$targetPdfMode = (string)$argument['targetPdfMode'];

		$pathSegments = explode('/', $path, 4);
		$file = basename($path);

		Filesystem::init($pathSegments[1], '/' . $pathSegments[1] . '/files');
		try {
			$node = $this->rootFolder->get($path);
		} catch (NotFoundException $e) {
			return;
		}
		$view = new View($dir);

		$tmpPath = $view->toTmpFile($file);
		$tmpDir = $this->tempManager->getTempBaseDir();

		$defaultParameters = ' -env:UserInstallation=file://' . escapeshellarg($tmpDir . '/nextcloud-' . $this->config->getSystemValue('instanceid') . '/') . ' --headless --nologo --nofirststartwizard --invisible --norestore --convert-to pdf --outdir ';
		$clParameters = $this->config->getSystemValue('preview_office_cl_parameters', $defaultParameters);

		// FIXME if passing an ogg, for instance, libreoffice would just run on infinitely, causing the background job to hang
		// Thus, for one, we should blacklist some mimetypes (e.g. audio and video – DONE), but with application it is tricky
		// since all the office things have their own custom mime types, as well as several applications
		// pickin raisins is thus not really feasible
		// so we should switch from exec to proc_open etc.
		$exec = $command . $clParameters . escapeshellarg($tmpDir) . ' ' . escapeshellarg($tmpPath);

		$exitCode = 0;
		exec($exec, $out, $exitCode);
		if ($exitCode !== 0) {
			$this->logger->error('could not convert {file}, reason: {out}',
				[
					'app' => 'workflow_pdf_converter',
					'file' => $node->getPath(),
					'out' => $out
				]
			);
			return;
		}

		$newTmpPath = pathinfo($tmpPath, PATHINFO_FILENAME) . '.pdf';
		$newFileBaseName = pathinfo($file, PATHINFO_FILENAME);
		$newFileName = $newFileBaseName . '.pdf';

		$folder = $node->getParent();

		$index = 0;
		while ($targetPdfMode === 'preserve' && $folder->nodeExists($newFileName)) {
			$index++;
			$newFileName = $newFileBaseName . ' (' . $index . ').pdf';
		}

		$view->fromTmpFile($tmpDir . '/' . $newTmpPath, $newFileName);

		if ($originalFileMode === 'delete') {
			// FIXME: sometimes causes "unable to rename, destination directory is not writable" because the trashbin url
			// looses the user part in \OC\Files\Storage\Local::moveFromStorage() line 460
			// return $rootStorage->rename($sourceStorage->getSourcePath($sourceInternalPath), $this->getSourcePath($targetInternalPath));
			//                                                                                 ^
			$node->delete();
		}
	}

	protected function getCommand(): ?string {
		$libreOfficePath = $this->config->getSystemValue('preview_libreoffice_path', null);
		if (is_string($libreOfficePath)) {
			return escapeshellcmd($libreOfficePath);
		}

		$whichLibreOffice = shell_exec('command -v libreoffice');
		if (!empty($whichLibreOffice)) {
			return 'libreoffice';
		}

		$whichOpenOffice = shell_exec('command -v openoffice');
		if (!empty($whichOpenOffice)) {
			return 'openoffice';
		}

		return null;
	}
}
