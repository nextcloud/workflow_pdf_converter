<template>
	<Multiselect :value="currentValue"
		:options="options"
		track-by="id"
		label="text"
		@input="(newValue) => newValue !== null && $emit('input', newValue.id)" />
</template>

<script>
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect.js'

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
	components: { Multiselect },
	props: {
		value: {
			default: pdfConvertOptions[0],
			type: String,
		},
	},
	data() {
		return {
			options: pdfConvertOptions,
		}
	},
	computed: {
		currentValue() {
			const newValue = pdfConvertOptions.find(option => option.id === this.value)
			if (typeof newValue === 'undefined') {
				return pdfConvertOptions[0]
			}
			return newValue
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
