<?php
/**
 * @copyright Copyright (c) 2018 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
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

namespace OCA\PDF_Converter;

use OCA\PDF_Converter\BackgroundJobs\Convert;
use OCP\BackgroundJob\IJobList;
use OCP\IL10N;
use OCP\WorkflowEngine\IManager;
use OCP\WorkflowEngine\IOperation;

class Operation implements IOperation {

	const MODES = [
		'keep;preserve',
		'keep;overwrite',
		'delete;preserve',
		'delete;overwrite',
	];

	/** @var IManager */
	private $workflowEngineManager;
	/** @var IJobList */
	private $jobList;
	/** @var IL10N */
	private $l;

	public function __construct(IManager $workflowEngineManager, IJobList $jobList, IL10N $l) {
		$this->workflowEngineManager = $workflowEngineManager;
		$this->jobList = $jobList;
		$this->l = $l;
	}

	public function considerConversion(\OCP\Files\Node $node) {
		try {
			$this->workflowEngineManager->setFileInfo($node->getStorage(), $node->getPath());
			$matches = $this->workflowEngineManager->getMatchingOperations(Operation::class, false);
			$originalFileMode = $targetPdfMode = null;
			foreach($matches as $match) {
				$fileModes = explode(';', $match['operation']);
				if($originalFileMode !== 'keep') {
					$originalFileMode = $fileModes[0];
				}
				if($targetPdfMode !== 'preserve') {
					$targetPdfMode = $fileModes[1];
				}
				if($originalFileMode === 'keep' && $targetPdfMode === 'preserve') {
					// most conservative setting, no need to look into other modes
					break;
				}
			}
			if(!empty($originalFileMode) && !empty($targetPdfMode)) {
				$this->jobList->add(Convert::class, [
					'path' => $node->getPath(),
					'originalFileMode' => $originalFileMode,
					'targetPdfMode' => $targetPdfMode,
				]);
			}
		} catch(\OCP\Files\NotFoundException $e) {
		}
	}

	/**
	 * @param string $name
	 * @param array[] $checks
	 * @param string $operation
	 * @throws \UnexpectedValueException
	 * @since 9.1
	 */
	public function validateOperation($name, array $checks, $operation) {
		if(!in_array($operation, Operation::MODES)) {
			throw new \UnexpectedValueException($this->l->t('Please choose a mode.'));
		}
	}
}
