/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

/* global globalRootUrl, Config, Form, UserMessage */

const moduleAutoprovision = {
	saveUrl: 'module-autoprovision/module-autoprovision/save',
	loadExamplesUrl: 'module-autoprovision/module-autoprovision/load-example-templates',
	$formObj: $('#module-autoprovision-form'),

	initialize() {
		moduleAutoprovision.initializeForm();
		moduleAutoprovision.initInputElements();
		moduleAutoprovision.bindAddRowButtons();
		moduleAutoprovision.bindRowActions();
		moduleAutoprovision.bindLoadExamplesButton();
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
	 *
	 * Delete is deferred: unsaved rows (id "none_*") are dropped from the DOM, and persisted
	 * rows get a hidden "<table>[<id>][__delete]=1" input plus a `marked-for-delete` class.
	 * A second click on a marked row un-marks it. Deletion takes effect on Save.
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
			const rowId = $row.attr('id');
			if (!tableKey || !rowId) {
				return;
			}

			// Unsaved row — just drop it; nothing for the server to delete.
			if (rowId.startsWith('none_')) {
				$row.remove();
				Form.dataChanged();
				return;
			}

			// Toggle deletion mark for persisted rows.
			const deleteInputName = `${tableKey}[${rowId}][__delete]`;
			const $existing = $row.find(`input[name="${deleteInputName}"]`);
			if ($existing.length) {
				$existing.remove();
				$row.removeClass('marked-for-delete');
			} else {
				$('<input>', { type: 'hidden', name: deleteInputName, value: '1' }).appendTo($row);
				$row.addClass('marked-for-delete');
			}
			// Re-init form so Semantic UI's `form('get values')` picks up the dynamic input.
			moduleAutoprovision.$formObj.form();
			Form.dataChanged();
		});

		$body.on('click', '.show-template-options', function handleEditTemplate(e) {
			e.preventDefault();
			const id = $(this).closest('tr').attr('id');
			moduleAutoprovision.showTemplateOptions(id);
		});
	},

	/**
	 * One-shot bootstrap of the bundled vendor example templates.
	 *
	 * TemplateSeeder is idempotent (skips by name) and atomic per seed (rolls back
	 * the template row when its URI insert fails). The handler:
	 *   - confirms a discard if the surrounding form is dirty, since a successful
	 *     install reloads the page and would otherwise drop unsaved admin edits;
	 *   - reloads only when nothing failed, so partial failures stay on screen
	 *     instead of being hidden by the reload.
	 */
	bindLoadExamplesButton() {
		$('body').on('click', '#load-example-templates-button', function handleLoadExamples(e) {
			e.preventDefault();
			const $button = $(this);
			if ($button.hasClass('loading') || $button.hasClass('disabled')) {
				return;
			}

			// Form.$submitButton has class `disabled` while the form matches its initial
			// values; once any field changes, checkValues() removes it. Treat that as
			// dirty and warn before the post-install reload throws those edits away.
			const formIsDirty = Form.$submitButton && !Form.$submitButton.hasClass('disabled');
			// eslint-disable-next-line no-alert
			if (formIsDirty && !window.confirm($button.data('unsaved-msg'))) {
				return;
			}

			const failedHeader = $button.data('failed-msg');
			const alreadyMsg = $button.data('already-msg');
			const partialHeader = $button.data('partial-msg');
			$button.addClass('loading disabled');
			$.ajax({
				url: `${globalRootUrl}${moduleAutoprovision.loadExamplesUrl}`,
				type: 'POST',
				dataType: 'json',
			}).done((response) => {
				const { installed = [], failed = [] } = response ?? {};
				if (failed.length > 0) {
					$button.removeClass('loading disabled');
					const lines = [`Failed: ${failed.join(', ')}`];
					if (installed.length > 0) {
						lines.unshift(`Installed: ${installed.join(', ')}`);
					}
					UserMessage.showError(lines.join('<br>'), partialHeader || failedHeader);
					return;
				}
				if (installed.length > 0) {
					window.location.reload();
					return;
				}
				$button.removeClass('loading disabled');
				UserMessage.showInformation(alreadyMsg);
			}).fail((jqXHR) => {
				$button.removeClass('loading disabled');
				const detail = `HTTP ${jqXHR.status}${jqXHR.statusText ? `: ${jqXHR.statusText}` : ''}`;
				UserMessage.showError(detail, failedHeader);
			});
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
		// Form.js calls this on both success and failure paths. On failure the controller
		// rolled the transaction back, so leave the marked-for-delete rows visible — removing
		// them would lie about the DB state.
		if (response.success === true) {
			$('tr.marked-for-delete').remove();
		}

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
		Form.url = `${globalRootUrl}${moduleAutoprovision.saveUrl}`;
		Form.cbBeforeSendForm = moduleAutoprovision.cbBeforeSendForm;
		Form.cbAfterSendForm = moduleAutoprovision.cbAfterSendForm;
		Form.initialize();
	},

	showTemplateOptions(id) {
		$(`.ui.modal[data-id="${id}"][data-id-table="templates"]`).modal({
			closable: true,
			onApprove() {
				const value = $(this).find('textarea').val();
				$(`textarea[name="templates[${$(this).attr('data-id')}][template]"]`).val(value);
				Form.checkValues();
				return true;
			},
		}).modal('show');
	},
};

$(document).ready(() => {
	moduleAutoprovision.initialize();
});
