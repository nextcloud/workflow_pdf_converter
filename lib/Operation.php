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

namespace OCA\Workflow_DocToPdf;

use OCA\Workflow_DocToPdf\BackgroundJobs\Convert;
use OCP\BackgroundJob\IJobList;
use OCP\WorkflowEngine\IManager;
use OCP\WorkflowEngine\IOperation;

class Operation implements IOperation {

	/** @var IManager */
	private $workflowEngineManager;
	/** @var IJobList */
	private $jobList;

	public function __construct(IManager $workflowEngineManager, IJobList $jobList) {
		$this->workflowEngineManager = $workflowEngineManager;
		$this->jobList = $jobList;
	}

	public function considerConversion(\OCP\Files\Node $node) {
		try {
			$this->workflowEngineManager->setFileInfo($node->getStorage(), $node->getPath());
			$matches = $this->workflowEngineManager->getMatchingOperations(Operation::class);
			if(!empty($matches)) {
				$this->jobList->add(Convert::class, $node->getPath());
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
	}
}
