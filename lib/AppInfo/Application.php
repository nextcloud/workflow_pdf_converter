<?php

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\WorkflowPDFConverter\AppInfo;

use OCA\WorkflowPDFConverter\Listener\RegisterFlowOperationsListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\WorkflowEngine\Events\RegisterOperationsEvent;

class Application extends App implements IBootstrap {
	public function __construct() {
		parent::__construct('workflow_pdf_converter');
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(RegisterOperationsEvent::class, RegisterFlowOperationsListener::class);
	}

	public function boot(IBootContext $context): void {
	}
}
