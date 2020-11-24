/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 12 2018
 */
/* global globalRootUrl,Config, Form */

const moduleAutoprovision = {
	$formObj: $('#module-autoprovision-form'),
	initialize() {
		// Динамическая проверка свободен ли внутренний номер
		moduleAutoprovision.initializeForm();
	},

	/**
     * Применение настроек модуля после изменения данных формы
     */
	applyConfigurationChanges() {
		$.api({
			url: `${Config.pbxUrl}/pbxcore/api/modules/ModuleAutoprovision/reload`,
			on: 'now',
			successTest(response) {
				// test whether a JSON response is valid
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
	cbAfterSendForm() {
		moduleAutoprovision.applyConfigurationChanges();
	},
	initializeForm() {
		Form.$formObj = moduleAutoprovision.$formObj;
		Form.url = `${globalRootUrl}module-autoprovision/save`;
		// Form.validateRules = moduleAutoprovision.validateRules;
		Form.cbBeforeSendForm = moduleAutoprovision.cbBeforeSendForm;
		Form.cbAfterSendForm = moduleAutoprovision.cbAfterSendForm;
		Form.initialize();
	},
};


$(document).ready(() => {
	moduleAutoprovision.initialize();
});
