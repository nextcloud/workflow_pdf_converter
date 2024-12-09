<?php

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\WorkflowPDFConverter;

use OCA\WorkflowEngine\Entity\File;
use OCA\WorkflowPDFConverter\BackgroundJobs\Convert;
use OCP\BackgroundJob\IJobList;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\GenericEvent;
use OCP\Files\Folder;
use OCP\Files\Node;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\WorkflowEngine\IRuleMatcher;
use OCP\WorkflowEngine\ISpecificOperation;
use UnexpectedValueException;

class Operation implements ISpecificOperation {
	public const MODES = [
		'keep;preserve',
		'keep;overwrite',
		'delete;preserve',
		'delete;overwrite',
	];

	private IJobList $jobList;
	private IL10N $l;
	private IURLGenerator $urlGenerator;

	public function __construct(IJobList $jobList, IL10N $l, IURLGenerator $urlGenerator) {
		$this->jobList = $jobList;
		$this->l = $l;
		$this->urlGenerator = $urlGenerator;
	}

	public function validateOperation(string $name, array $checks, string $operation): void {
		if (!in_array($operation, Operation::MODES)) {
			throw new UnexpectedValueException($this->l->t('Please choose a mode.'));
		}
	}

	public function getDisplayName(): string {
		return $this->l->t('PDF conversion');
	}

	public function getDescription(): string {
		return $this->l->t('Convert documents into the PDF format on upload and write.');
	}

	public function getIcon(): string {
		return $this->urlGenerator->imagePath('workflow_pdf_converter', 'app.svg');
	}

	public function isAvailableForScope(int $scope): bool {
		return true;
	}

	public function onEvent(string $eventName, Event $event, IRuleMatcher $ruleMatcher): void {
		if (!$event instanceof GenericEvent) {
			return;
		}
		try {
			if ($eventName === '\OCP\Files::postRename' || $eventName === '\OCP\Files::postCopy') {
				/** @var Node $oldNode */
				[, $node] = $event->getSubject();
			} else {
				$node = $event->getSubject();
			}
			/** @var Node $node */

			// '', admin, 'files', 'path/to/file.txt'
			[,, $folder,] = explode('/', $node->getPath(), 4);
			if ($folder !== 'files' || $node instanceof Folder) {
				return;
			}

			// avoid converting pdfs into pdfs - would become infinite
			// also some types we know would not succeed
			if ($node->getMimetype() === 'application/pdf'
				|| $node->getMimePart() === 'video'
				|| $node->getMimePart() === 'audio'
			) {
				return;
			}

			$matches = $ruleMatcher->getFlows(false);
			$originalFileMode = $targetPdfMode = null;
			foreach ($matches as $match) {
				$fileModes = explode(';', $match['operation']);
				if ($originalFileMode !== 'keep') {
					$originalFileMode = $fileModes[0];
				}
				if ($targetPdfMode !== 'preserve') {
					$targetPdfMode = $fileModes[1];
				}
				if ($originalFileMode === 'keep' && $targetPdfMode === 'preserve') {
					// most conservative setting, no need to look into other modes
					break;
				}
			}
			if (!empty($originalFileMode) && !empty($targetPdfMode)) {
				$this->jobList->add(Convert::class, [
					'path' => $node->getPath(),
					'originalFileMode' => $originalFileMode,
					'targetPdfMode' => $targetPdfMode,
				]);
			}
		} catch (\OCP\Files\NotFoundException $e) {
		}
	}

	public function getEntityId(): string {
		return File::class;
	}
}
