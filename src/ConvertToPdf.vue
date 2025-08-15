<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcSelect v-model="currentValue"
		:options="options"
		track-by="id"
		label="text"
		@input="emitInput" />
</template>

<script>
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'

const pdfConvertOptions = [
	{
		id: 'keep;preserve',
		text: t('workflow_pdf_converter', 'Keep original, preserve existing PDFs'),
	},
	{
		id: 'keep;overwrite',
		text: t('workflow_pdf_converter', 'Keep original, overwrite existing PDF'),
	},
	{
		id: 'delete;preserve',
		text: t('workflow_pdf_converter', 'Delete original, preserve existing PDFs'),
	},
	{
		id: 'delete;overwrite',
		text: t('workflow_pdf_converter', 'Delete original, overwrite existing PDF'),
	},
]
export default {
	name: 'ConvertToPdf',
	components: { NcSelect },
	props: {
		modelValue: {
			default: pdfConvertOptions[0],
			type: String,
		},
	},
	emits: ['update:model-value'],
	data() {
		return {
			options: pdfConvertOptions,
		}
	},
	computed: {
		currentValue() {
			const newValue = pdfConvertOptions.find(option => option.id === this.modelValue)
			if (typeof newValue === 'undefined') {
				return pdfConvertOptions[0]
			}
			return newValue
		},
	},
	methods: {
		emitInput(value) {
			this.$emit('update:model-value', '' + value.id)
		},
	},
}
</script>

<style scoped>
	.multiselect {
		width: 100%;
		margin: auto;
		text-align: center;
	}
</style>
