<?php

/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OC\Files {
	class View {
		public function toTmpFile($path): string|false {
		}

		public function fromTmpFile(string $tmpFile, string $path) {
		}
	}
}
