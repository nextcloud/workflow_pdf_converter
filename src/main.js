import ConvertToPdf from './ConvertToPdf.vue'

OCA.WorkflowEngine.registerOperator({
	id: 'OCA\\WorkflowPDFConverter\\Operation',
	operation: 'keep;preserve',
	options: ConvertToPdf,
	color: '#dc5047',
})
