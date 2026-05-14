/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

/* global globalRootUrl, Config, Form */

const moduleAutoprovision = {
	idUrl: 'module-autoprovision/module-autoprovision',
	$formObj: $('#module-autoprovision-form'),

	/**
	 * Mapping of form-section prefix → server-side model class.
	 * Must stay in sync with App/Controllers/ModuleAutoprovisionController::TABLE_MAP
	 * and App/Views/ModuleAutoprovision/index.volt (data-table-key / data-model attrs).
	 */
	tableMap: {
		templates: 'Templates',
		templates_uri: 'TemplatesUri',
		phone_settings: 'TemplatesUsers',
		other_pbx: 'OtherPBX',
	},

	initialize() {
		moduleAutoprovision.initializeForm();
		moduleAutoprovision.initInputElements();
		moduleAutoprovision.bindAddRowButtons();
		moduleAutoprovision.bindRowActions();
	},

	/**
	 * Wires the "+ Add" button of each editable table to a single row-cloner.
	 * The button id encodes the table key: "add-new-{tableKey}-button".
	 */
	bindAddRowButtons() {
		$('body').on('click', '[id^="add-new-"][id$="-button"]', function handleAddRow() {
			const buttonId = $(this).attr('id');
			// Strip "add-new-" prefix and "-button" suffix to get the table key.
			let tableKey = buttonId.slice('add-new-'.length, -('-button'.length));
			// Special case: the "templates" tab uses singular "template" in its button id.
			if (tableKey === 'template') {
				tableKey = 'templates';
			}
			const $table = $(`#${tableKey}`);
			if ($table.length === 0) {
				return;
			}
			const newId = `none_${Date.now()}`;
			const $newRow = $('<tr>').attr('id', newId);
			const templateHtml = $table.find('#emptyTemplateRow').html();
			$newRow.html(templateHtml.replace(/emptyTemplateRow/g, newId));
			$table.find('tbody').append($newRow);
			moduleAutoprovision.initInputElements();
			moduleAutoprovision.$formObj.form();
			Form.setEvents();
		});
	},

	/**
	 * Single handler for row delete and template-edit buttons.
	 * Targets are identified by .remove-row / .show-template-options classes
	 * and the table is read from the nearest [data-table-key] / [data-model] table element.
	 */
	bindRowActions() {
		const $body = $('body');
		$body.on('click', '.remove-row', function handleRemove(e) {
			e.preventDefault();
			if (!$(this).find('i').hasClass('close')) {
				return;
			}
			const $row = $(this).closest('tr');
			const $table = $row.closest('table');
			const tableKey = $table.attr('data-table-key');
			const model = $table.attr('data-model');
			const rowId = $row.attr('id');
			if (!model || !tableKey || !rowId) {
				return;
			}
			$.ajax({
				type: 'POST',
				url: `${globalRootUrl}${moduleAutoprovision.idUrl}/delete`,
				data: { table: model, id: rowId },
				success() {
					$row.remove();
				},
				error(xhr, status, error) {
					/* eslint-disable-next-line no-console */
					console.debug('Delete request failed', status, error);
				},
			});
		});

		$body.on('click', '.show-template-options', function handleEditTemplate(e) {
			e.preventDefault();
			const id = $(this).closest('tr').attr('id');
			moduleAutoprovision.showTemplateOptions(id);
		});
	},

	initInputElements() {
		$('.menu .item').tab();
		$('div.dropdown').dropdown();
		$('input, textarea').change(function syncValueAttribute() {
			$(this).attr('value', $(this).val());
		});
	},

	/**
	 * Notifies the backend that module-level settings changed so the worker can reload.
	 */
	applyConfigurationChanges() {
		$.api({
			url: `${Config.pbxUrl}/pbxcore/api/modules/ModuleAutoprovision/reload`,
			on: 'now',
			successTest(response) {
				return Object.keys(response).length > 0 && response.result === true;
			},
		});
	},

	cbBeforeSendForm(settings) {
		const result = settings;
		result.data = moduleAutoprovision.$formObj.form('get values');
		return result;
	},

	cbAfterSendForm(response) {
		// Re-bind freshly inserted rows from mock ids to real database ids.
		Object.entries(response.resultSaveTables || {}).forEach(([table, mapping]) => {
			Object.entries(mapping).forEach(([oldId, newId]) => {
				const $syncTr = $(`#${table} tr#${oldId}`);
				const html = $syncTr.html();
				if (html) {
					$syncTr.html(html.replace(new RegExp(oldId, 'g'), newId));
				}
				$syncTr.attr('id', newId);
				$(`.ui.modal[data-id="${oldId}"][data-id-table="${table}"]`).attr('data-id', newId);
			});
		});

		// Rebuild every template-dropdown's option list from the current state of #templates.
		const templates = $('#templates td[data-label="name"]').map(function buildEntry() {
			return {
				id: $(this).parent().attr('id'),
				name: $(this).find('input').val(),
			};
		}).get();
		$('td[data-label="template"] div.scrolling.menu').each(function rebuild() {
			const $menu = $(this).empty();
			templates.forEach(({ id, name }) => {
				$menu.append($('<div>').attr('data-value', id).attr('class', 'item').text(name));
			});
		});

		moduleAutoprovision.initInputElements();
		moduleAutoprovision.applyConfigurationChanges();
		moduleAutoprovision.$formObj.form();
		Form.setEvents();
	},

	initializeForm() {
		Form.$formObj = moduleAutoprovision.$formObj;
		Form.url = `${globalRootUrl}${moduleAutoprovision.idUrl}/save`;
		Form.cbBeforeSendForm = moduleAutoprovision.cbBeforeSendForm;
		Form.cbAfterSendForm = moduleAutoprovision.cbAfterSendForm;
		Form.initialize();
	},

	showTemplateOptions(id) {
		$(`.ui.modal[data-id="${id}"][data-id-table="templates"]`).modal({
			closable: true,
			onApprove() {
				const value = $(this).find('textarea').val();
				$(`textarea[name="templates-template-${$(this).attr('data-id')}"]`).val(value);
				Form.checkValues();
				return true;
			},
		}).modal('show');
	},
};

$(document).ready(() => {
	moduleAutoprovision.initialize();
});
