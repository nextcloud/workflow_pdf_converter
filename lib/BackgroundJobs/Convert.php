<?php
/**
 * @copyright Copyright (c) 2018 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Workflow_DocToPdf\BackgroundJobs;

use OCP\Files\NotFoundException;
use OCP\IConfig;
use OCP\ILogger;
use OCP\ITempManager;

class Convert extends \OC\BackgroundJob\QueuedJob {

	/** @var IConfig */
	protected $config;

	/** @var ITempManager */
	protected $tempManager;

	/** @var ILogger */
	protected $logger;

	/**
	 * BackgroundJob constructor.
	 *
	 * @param IConfig $config
	 * @param ITempManager $tempManager
	 * @param ILogger $logger
	 */
	public function __construct(IConfig $config, ITempManager $tempManager, ILogger $logger) {
		$this->config = $config;
		$this->tempManager = $tempManager;
		$this->logger = $logger;
	}

	/**
	 * @param mixed $argument
	 */
	protected function run($argument) {
		$command = $this->getCommand();
		if ($command === null) {
			$this->logger->error('Can not find office path. Please make sure to configure "preview_libreoffice_path" in your config file.');
		}

		$path = (string) $argument;

		$pathSegments = explode('/', $argument, 4);
		$dir = dirname($path);
		$file = basename($path);

		\OC\Files\Filesystem::init($pathSegments[1], '/' . $pathSegments[1] . '/files');
		try {
			$node = \OC::$server->getLazyRootFolder()->get($argument);
		} catch (NotFoundException $e) {
			return;
		}
		$view = new \OC\Files\View($dir);

		$tmpPath = $view->toTmpFile($file);
		$tmpDir = $this->tempManager->getTempBaseDir();

		$defaultParameters = ' -env:UserInstallation=file://' . escapeshellarg($tmpDir . '/nextcloud-' . $this->config->getSystemValue('instanceid') . '/') . ' --headless --nologo --nofirststartwizard --invisible --norestore --convert-to pdf --outdir ';
		$clParameters = $this->config->getSystemValue('preview_office_cl_parameters', $defaultParameters);

		$exec = $command . $clParameters . escapeshellarg($tmpDir) . ' ' . escapeshellarg($tmpPath);

		$exitCode = 0;
		exec($exec, $out, $exitCode);
		if($exitCode !== 0) {
			$this->logger->error("could not convert {file}, reason: {out}",
				[
					'app' => 'workflow_doctopdf',
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
		while ($folder->nodeExists($newFileName)) {
			$index++;
			$newFileName = $newFileBaseName . ' (' . $index . ').pdf';
		}

		$view = new \OC\Files\View($folder->getPath());
		$view->fromTmpFile($tmpDir . '/' . $newTmpPath, $newFileName);
	}

	protected function getCommand() {
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
