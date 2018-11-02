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

namespace OCA\WorkflowPDFConverter\AppInfo;

use OCA\WorkflowPDFConverter\Operation;
use OCP\AppFramework\QueryException;
use OCP\ILogger;

class Application extends \OCP\AppFramework\App {

	/**
	 * Application constructor.
	 */
	public function __construct() {
		parent::__construct('workflow_pdf_converter');
	}

	public function onCreateOrUpdate(\OCP\Files\Node $node) {
		try {
			// '', admin, 'files', 'path/to/file.txt'
			list(,, $folder,) = explode('/', $node->getPath(), 4);
			if($folder !== 'files') {
				return;
			}

			// avoid converting pdfs into pdfs - would become infinite
			// also some types we know would not succeed
			if($node->getMimetype() === 'application/pdf'
				|| $node->getMimePart() === 'video'
				|| $node->getMimePart() === 'audio'
			) {
				return;
			}

			$operation = $this->getContainer()->query(Operation::class);
			/** @var $operation Operation */
			$operation->considerConversion($node);
		} catch (QueryException $e) {
			$logger = $this->getContainer()->getServer()->getLogger();
			$logger->logException($e, ['app' => 'workflow_pdf_converter', 'level' => ILogger::ERROR]);
		}
	}

	/**
	 * Register the app to several events
	 */
	public function registerHooksAndListeners() {
		$root = $this->getContainer()->getServer()->getRootFolder();
		$root->listen('\OC\Files', 'postCreate', [$this, 'onCreateOrUpdate']);
		$root->listen('\OC\Files', 'postWrite', [$this, 'onCreateOrUpdate']);
	}

}
