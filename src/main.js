/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
 
import ConvertToPdf from './ConvertToPdf.vue'

OCA.WorkflowEngine.registerOperator({
	id: 'OCA\\WorkflowPDFConverter\\Operation',
	operation: 'keep;preserve',
	options: ConvertToPdf,
	color: '#dc5047',
})
