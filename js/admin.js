/**
 * @copyright Copyright (c) 2016 Morris Jobke <hey@morrisjobke.de>
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

(function() {
	OCA.WorkflowPDFConverter = OCA.WorkflowPDFConverter || {};

	/**
	 * @class OCA.WorkflowPDFConverter.Operation
	 */
	OCA.WorkflowPDFConverter.Operation =
		OCA.WorkflowEngine.Operation.extend({
			defaults: {
				'class': 'OCA\\WorkflowPDFConverter\\Operation',
				'name': '',
				'checks': [],
				'operation': ''
			}
		});

	/**
	 * @class OCA.WorkflowPDFConverter.OperationsCollection
	 *
	 * collection for all configured operations
	 */
	OCA.WorkflowPDFConverter.OperationsCollection =
		OCA.WorkflowEngine.OperationsCollection.extend({
			model: OCA.WorkflowPDFConverter.Operation
		});

	/**
	 * @class OCA.WorkflowPDFConverter.OperationView
	 *
	 * this creates the view for a single operation
	 */
	OCA.WorkflowPDFConverter.OperationView =
		OCA.WorkflowEngine.OperationView.extend({
			model: OCA.WorkflowPDFConverter.Operation,
			render: function() {
				var $el = OCA.WorkflowEngine.OperationView.prototype.render.apply(this);
				$el.find('input.operation-operation')
					.css('width', '400px')
					.select2({
						placeholder: t('workflow_pdf_converter', 'Mode…'),
						data: [
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
						],
					});
			}
		});

	/**
	 * @class OCA.WorkflowPDFConverter.OperationsView
	 *
	 * this creates the view for configured operations
	 */
	OCA.WorkflowPDFConverter.OperationsView =
		OCA.WorkflowEngine.OperationsView.extend({
			initialize: function() {
				OCA.WorkflowEngine.OperationsView.prototype.initialize.apply(this, [
					'OCA\\WorkflowPDFConverter\\Operation'
				]);
			},
			renderOperation: function(operation) {
				var subView = new OCA.WorkflowPDFConverter.OperationView({
					model: operation
				});

				OCA.WorkflowEngine.OperationsView.prototype.renderOperation.apply(this, [
					subView
				]);
			}
		});
})();


$(document).ready(function() {
	OC.SystemTags.collection.fetch({
		success: function() {
			new OCA.WorkflowPDFConverter.OperationsView({
				el: '#workflow_pdf_converter .rules',
				collection: new OCA.WorkflowPDFConverter.OperationsCollection()
			});
		}
	});
});
