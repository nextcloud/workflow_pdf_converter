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
	OCA.Workflow_DocToPdf = OCA.Workflow_DocToPdf || {};

	/**
	 * @class OCA.Workflow_DocToPdf.Operation
	 */
	OCA.Workflow_DocToPdf.Operation =
		OCA.WorkflowEngine.Operation.extend({
			defaults: {
				'class': 'OCA\\Workflow_DocToPdf\\Operation',
				'name': '',
				'checks': [],
				'operation': ''
			}
		});

	/**
	 * @class OCA.Workflow_DocToPdf.OperationsCollection
	 *
	 * collection for all configured operations
	 */
	OCA.Workflow_DocToPdf.OperationsCollection =
		OCA.WorkflowEngine.OperationsCollection.extend({
			model: OCA.Workflow_DocToPdf.Operation
		});

	/**
	 * @class OCA.Workflow_DocToPdf.OperationView
	 *
	 * this creates the view for a single operation
	 */
	OCA.Workflow_DocToPdf.OperationView =
		OCA.WorkflowEngine.OperationView.extend({
			model: OCA.Workflow_DocToPdf.Operation,
			render: function() {
				var $el = OCA.WorkflowEngine.OperationView.prototype.render.apply(this);
				$el.find('input.operation-operation').addClass('hidden');
			}
		});

	/**
	 * @class OCA.Workflow_DocToPdf.OperationsView
	 *
	 * this creates the view for configured operations
	 */
	OCA.Workflow_DocToPdf.OperationsView =
		OCA.WorkflowEngine.OperationsView.extend({
			initialize: function() {
				OCA.WorkflowEngine.OperationsView.prototype.initialize.apply(this, [
					'OCA\\Workflow_DocToPdf\\Operation'
				]);
			},
			renderOperation: function(operation) {
				var subView = new OCA.Workflow_DocToPdf.OperationView({
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
			new OCA.Workflow_DocToPdf.OperationsView({
				el: '#workflow_doctopdf .rules',
				collection: new OCA.Workflow_DocToPdf.OperationsCollection()
			});
		}
	});
});
