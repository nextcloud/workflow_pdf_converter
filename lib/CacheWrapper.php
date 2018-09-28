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

use OC\Files\Cache\Wrapper\CacheWrapper as Wrapper;
use OCP\Files\Cache\ICache;
use OCP\Files\Storage\IStorage;

class CacheWrapper extends Wrapper {
	/** @var IStorage */
	protected $storage;

	/** @var Operation */
	protected $operation;

	/** @var string */
	protected $mountPoint;

	public function __construct(ICache $cache, IStorage $storage, Operation $operation, $mountPoint) {
		parent::__construct($cache);
		$this->storage = $storage;
		$this->operation = $operation;
		$this->mountPoint = $mountPoint;
	}

	public function update($id, array $data) {
		parent::update($id, $data);

		$file = $this->getPathById($id);
		if ($id > -1 && $this->isTaggingPath($file)) {
			$this->operation->checkOperations($this->storage, $id, $file);
		}
	}

	public function insert($file, array $data) {
		$fileId = parent::insert($file, $data);

		if ($fileId > -1 && $this->isTaggingPath($file)) {
			$this->operation->checkOperations($this->storage, $fileId, $file);
		}

		return $fileId;
	}

}
