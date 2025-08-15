/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import wrap from '@vue/web-component-wrapper'
import Vue from 'vue'

import ConvertToPdf from './ConvertToPdf.vue'

const ConvertToPdfComponent = wrap(Vue, ConvertToPdf)
const customElementId = 'oca-workflow_pdf_converter-operation-convert'

window.customElements.define(customElementId, ConvertToPdfComponent)

// In Vue 2, wrap doesn't support disabling shadow :(
// Disable with a hack
Object.defineProperty(ConvertToPdfComponent.prototype, 'attachShadow', { value() { return this } })
Object.defineProperty(ConvertToPdfComponent.prototype, 'shadowRoot', { get() { return this } })

OCA.WorkflowEngine.registerOperator({
	id: 'OCA\\WorkflowPDFConverter\\Operation',
	operation: 'keep;preserve',
	element: customElementId,
	color: '#dc5047',
})
