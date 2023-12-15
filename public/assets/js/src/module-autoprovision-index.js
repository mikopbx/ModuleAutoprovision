/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 12 2018
 */
/* global globalRootUrl,Config, Form */
const idUrl     = 'module-autoprovision';
const moduleAutoprovision = {
	$formObj: $('#module-autoprovision-form'),
	initialize() {
		// Динамическая проверка свободен ли внутренний номер
		moduleAutoprovision.initializeForm();
		moduleAutoprovision.initInputElements();

		let body = $('body');
		body.on('click', '#add-new-template-button', function (e) {
			let id = 'none_'+Date.now();
			let newRow 	= $('<tr>').attr('id', id);
			let data =$('#templates #emptyTemplateRow').html();
			newRow.html(data.replace(/emptyTemplateRow/g, id));
			$('#templates tbody').append(newRow);
			// Новые элементы нужно инициализировать на форме
			moduleAutoprovision.initInputElements();
			moduleAutoprovision.$formObj.form();
			Form.setEvents();
		});

		body.on('click', '#add-new-phone_settings-button', function (e) {
			let id = 'none_'+Date.now();
			let newRow 	= $('<tr>').attr('id', id);
			let data =$('#phone_settings #emptyTemplateRow').html();
			newRow.html(data.replace(/emptyTemplateRow/g, id));
			$('#phone_settings tbody').append(newRow);
			// Новые элементы нужно инициализировать на форме
			moduleAutoprovision.initInputElements();
			moduleAutoprovision.$formObj.form();
			Form.setEvents();
		});		
		body.on('click', '#add-new-templates_uri-button', function (e) {
			let id = 'none_'+Date.now();
			let newRow 	= $('<tr>').attr('id', id);
			let data =$('#templates_uri #emptyTemplateRow').html();
			newRow.html(data.replace(/emptyTemplateRow/g, id));
			$('#templates_uri tbody').append(newRow);
			// Новые элементы нужно инициализировать на форме
			moduleAutoprovision.initInputElements();
			moduleAutoprovision.$formObj.form();
			Form.setEvents();
		});
		body.on('click', '#add-new-other_pbx-button', function (e) {
			let id = 'none_'+Date.now();
			let newRow 	= $('<tr>').attr('id', id);
			let data =$('#other_pbx #emptyTemplateRow').html();
			newRow.html(data.replace(/emptyTemplateRow/g, id));
			$('#other_pbx tbody').append(newRow);
			// Новые элементы нужно инициализировать на форме
			moduleAutoprovision.initInputElements();
			moduleAutoprovision.$formObj.form();
			Form.setEvents();
		});
	},
	initInputElements(){
		$('.menu .item').tab();
		$('div.dropdown').dropdown();
		$('input, textarea').change(function() {
			$(this).attr('value', $(this).val());
		});
	},

	/**
     * Применение настроек модуля после изменения данных формы
     */
	applyConfigurationChanges() {
		$.api({
			url: `${Config.pbxUrl}/pbxcore/api/modules/ModuleAutoprovision/reload`,
			on: 'now',
			successTest(response) {
				return Object.keys(response).length > 0 && response.result === true;
			},
			onSuccess() {
				// moduleAutoprovision.testConnection();
			},
		});
	},
	cbBeforeSendForm(settings) {
		const result = settings;
		result.data = moduleAutoprovision.$formObj.form('get values');
		return result;
	},
	cbAfterSendForm(response) {
		for (let table in response.resultSaveTables) {
			for (let key in response.resultSaveTables[table]) {
				let syncTr = $(`#${table} tr#${key}`);
				let html = syncTr.html();
				syncTr.html(html.replace((new RegExp(key, "g")), response.resultSaveTables[table][key]));
				syncTr.attr('id', response.resultSaveTables[table][key]);
				$(`.ui.modal[data-id="${key}"][data-id-table="${table}"]`).attr('data-id', response.resultSaveTables[table][key]);
			}
		}
		let templates = [];
		$('#templates td[data-label="name"]').each(function(index, item) {
			templates.push({
				id: $(item).parent().attr('id'),
				name: $(item).find('input').val()
			});
		});
		$('td[data-label="template"] div.scrolling.menu').each(function(index, item) {
			$(item).empty();
			for (let element of templates) {
				let newRow 	= $('<div>')
					.attr('data-value', element.id)
					.attr('class', 'item')
					.text(element.name)
				;
				$(item).append(newRow);
			}
		});
		moduleAutoprovision.initInputElements();
		moduleAutoprovision.applyConfigurationChanges();
		moduleAutoprovision.$formObj.form();
		Form.setEvents();
	},
	initializeForm() {
		Form.$formObj = moduleAutoprovision.$formObj;
		Form.url = `${globalRootUrl}module-autoprovision/save`;
		Form.cbBeforeSendForm = moduleAutoprovision.cbBeforeSendForm;
		Form.cbAfterSendForm = moduleAutoprovision.cbAfterSendForm;
		Form.initialize();
	},

	showTemplateOptions(id){
		$('.ui.modal[data-id="'+id+'"][data-id-table="templates"]').modal({
			closable  : true,
			onApprove    : function(){
				let value = $(this).find("textarea").val();
				let resEl = $('textarea[name="templates-template-'+$(this).attr('data-id')+'"]');
				resEl.val(value);
				Form.checkValues()
				return true;
			},
		}).modal('show');
	},
	removeTemplate(id, button){
		if(!$(button).find('i').hasClass('close')){
			return;
		}
		$.ajax({
			type: "POST",
			url:  globalRootUrl + idUrl + "/delete",
			data: {
				table: 'Templates',
				id: id
			},
			success: function(response) {
				$('#templates tr[id="'+id+'"]').remove();
			},
			error: function(xhr, status, error) {
				console.debug("Ошибка запроса", status, error);
			}
		});
	},
	removeUserTemplate(id, button){
		if(!$(button).find('i').hasClass('close')){
			return;
		}
		$.ajax({
			type: "POST",
			url:  globalRootUrl + idUrl + "/delete",
			data: {
				table: 'TemplatesUsers',
				id: id
			},
			success: function(response) {
				$('#phone_settings tr[id="'+id+'"]').remove();
			},
			error: function(xhr, status, error) {
				console.debug("Ошибка запроса", status, error);
			}
		});
	},
	removeUriTemplate(id, button){
		if(!$(button).find('i').hasClass('close')){
			return;
		}
		$.ajax({
			type: "POST",
			url:  globalRootUrl + idUrl + "/delete",
			data: {
				table: 'TemplatesUri',
				id: id
			},
			success: function(response) {
				$('#templates_uri tr[id="'+id+'"]').remove();
			},
			error: function(xhr, status, error) {
				console.debug("Ошибка запроса", status, error);
			}
		});
	},
	removePbxRow(id, button){
		if(!$(button).find('i').hasClass('close')){
			return;
		}
		$.ajax({
			type: "POST",
			url:  globalRootUrl + idUrl + "/delete",
			data: {
				table: 'OtherPBX',
				id: id
			},
			success: function(response) {
				$('#other_pbx tr[id="'+id+'"]').remove();
			},
			error: function(xhr, status, error) {
				console.debug("Ошибка запроса", status, error);
			}
		});
	},

};


$(document).ready(() => {
	moduleAutoprovision.initialize();
});
