"use strict";

function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Alexey Portnov, 12 2018
 */

/* global globalRootUrl,Config, Form */
var idUrl = 'module-autoprovision';
var moduleAutoprovision = {
  $formObj: $('#module-autoprovision-form'),
  initialize: function initialize() {
    // Динамическая проверка свободен ли внутренний номер
    moduleAutoprovision.initializeForm();
    moduleAutoprovision.initInputElements();
    var body = $('body');
    body.on('click', '#add-new-template-button', function (e) {
      var id = 'none_' + Date.now();
      var newRow = $('<tr>').attr('id', id);
      var data = $('#templates #emptyTemplateRow').html();
      newRow.html(data.replace(/emptyTemplateRow/g, id));
      $('#templates tbody').append(newRow); // Новые элементы нужно инициализировать на форме

      moduleAutoprovision.initInputElements();
      moduleAutoprovision.$formObj.form();
      Form.setEvents();
    });
    body.on('click', '#add-new-phone_settings-button', function (e) {
      var id = 'none_' + Date.now();
      var newRow = $('<tr>').attr('id', id);
      var data = $('#phone_settings #emptyTemplateRow').html();
      newRow.html(data.replace(/emptyTemplateRow/g, id));
      $('#phone_settings tbody').append(newRow); // Новые элементы нужно инициализировать на форме

      moduleAutoprovision.initInputElements();
      moduleAutoprovision.$formObj.form();
      Form.setEvents();
    });
    body.on('click', '#add-new-templates_uri-button', function (e) {
      var id = 'none_' + Date.now();
      var newRow = $('<tr>').attr('id', id);
      var data = $('#templates_uri #emptyTemplateRow').html();
      newRow.html(data.replace(/emptyTemplateRow/g, id));
      $('#templates_uri tbody').append(newRow); // Новые элементы нужно инициализировать на форме

      moduleAutoprovision.initInputElements();
      moduleAutoprovision.$formObj.form();
      Form.setEvents();
    });
    body.on('click', '#add-new-other_pbx-button', function (e) {
      var id = 'none_' + Date.now();
      var newRow = $('<tr>').attr('id', id);
      var data = $('#other_pbx #emptyTemplateRow').html();
      newRow.html(data.replace(/emptyTemplateRow/g, id));
      $('#other_pbx tbody').append(newRow); // Новые элементы нужно инициализировать на форме

      moduleAutoprovision.initInputElements();
      moduleAutoprovision.$formObj.form();
      Form.setEvents();
    });
  },
  initInputElements: function initInputElements() {
    $('.menu .item').tab();
    $('div.dropdown').dropdown();
    $('input, textarea').change(function () {
      $(this).attr('value', $(this).val());
    });
  },

  /**
      * Применение настроек модуля после изменения данных формы
      */
  applyConfigurationChanges: function applyConfigurationChanges() {
    $.api({
      url: "".concat(Config.pbxUrl, "/pbxcore/api/modules/ModuleAutoprovision/reload"),
      on: 'now',
      successTest: function successTest(response) {
        return Object.keys(response).length > 0 && response.result === true;
      },
      onSuccess: function onSuccess() {// moduleAutoprovision.testConnection();
      }
    });
  },
  cbBeforeSendForm: function cbBeforeSendForm(settings) {
    var result = settings;
    result.data = moduleAutoprovision.$formObj.form('get values');
    return result;
  },
  cbAfterSendForm: function cbAfterSendForm(response) {
    for (var table in response.resultSaveTables) {
      for (var key in response.resultSaveTables[table]) {
        var syncTr = $("#".concat(table, " tr#").concat(key));
        var html = syncTr.html();
        syncTr.html(html.replace(new RegExp(key, "g"), response.resultSaveTables[table][key]));
        syncTr.attr('id', response.resultSaveTables[table][key]);
        $(".ui.modal[data-id=\"".concat(key, "\"][data-id-table=\"").concat(table, "\"]")).attr('data-id', response.resultSaveTables[table][key]);
      }
    }

    var templates = [];
    $('#templates td[data-label="name"]').each(function (index, item) {
      templates.push({
        id: $(item).parent().attr('id'),
        name: $(item).find('input').val()
      });
    });
    $('td[data-label="template"] div.scrolling.menu').each(function (index, item) {
      $(item).empty();

      var _iterator = _createForOfIteratorHelper(templates),
          _step;

      try {
        for (_iterator.s(); !(_step = _iterator.n()).done;) {
          var element = _step.value;
          var newRow = $('<div>').attr('data-value', element.id).attr('class', 'item').text(element.name);
          $(item).append(newRow);
        }
      } catch (err) {
        _iterator.e(err);
      } finally {
        _iterator.f();
      }
    });
    moduleAutoprovision.initInputElements();
    moduleAutoprovision.applyConfigurationChanges();
    moduleAutoprovision.$formObj.form();
    Form.setEvents();
  },
  initializeForm: function initializeForm() {
    Form.$formObj = moduleAutoprovision.$formObj;
    Form.url = "".concat(globalRootUrl, "module-autoprovision/save");
    Form.cbBeforeSendForm = moduleAutoprovision.cbBeforeSendForm;
    Form.cbAfterSendForm = moduleAutoprovision.cbAfterSendForm;
    Form.initialize();
  },
  showTemplateOptions: function showTemplateOptions(id) {
    $('.ui.modal[data-id="' + id + '"][data-id-table="templates"]').modal({
      closable: true,
      onApprove: function onApprove() {
        var value = $(this).find("textarea").val();
        var resEl = $('textarea[name="templates-template-' + $(this).attr('data-id') + '"]');
        resEl.val(value);
        Form.checkValues();
        return true;
      }
    }).modal('show');
  },
  removeTemplate: function removeTemplate(id, button) {
    if (!$(button).find('i').hasClass('close')) {
      return;
    }

    $.ajax({
      type: "POST",
      url: globalRootUrl + idUrl + "/delete",
      data: {
        table: 'Templates',
        id: id
      },
      success: function success(response) {
        $('#templates tr[id="' + id + '"]').remove();
      },
      error: function error(xhr, status, _error) {
        console.debug("Ошибка запроса", status, _error);
      }
    });
  },
  removeUserTemplate: function removeUserTemplate(id, button) {
    if (!$(button).find('i').hasClass('close')) {
      return;
    }

    $.ajax({
      type: "POST",
      url: globalRootUrl + idUrl + "/delete",
      data: {
        table: 'TemplatesUsers',
        id: id
      },
      success: function success(response) {
        $('#phone_settings tr[id="' + id + '"]').remove();
      },
      error: function error(xhr, status, _error2) {
        console.debug("Ошибка запроса", status, _error2);
      }
    });
  },
  removeUriTemplate: function removeUriTemplate(id, button) {
    if (!$(button).find('i').hasClass('close')) {
      return;
    }

    $.ajax({
      type: "POST",
      url: globalRootUrl + idUrl + "/delete",
      data: {
        table: 'TemplatesUri',
        id: id
      },
      success: function success(response) {
        $('#templates_uri tr[id="' + id + '"]').remove();
      },
      error: function error(xhr, status, _error3) {
        console.debug("Ошибка запроса", status, _error3);
      }
    });
  },
  removePbxRow: function removePbxRow(id, button) {
    if (!$(button).find('i').hasClass('close')) {
      return;
    }

    $.ajax({
      type: "POST",
      url: globalRootUrl + idUrl + "/delete",
      data: {
        table: 'OtherPBX',
        id: id
      },
      success: function success(response) {
        $('#other_pbx tr[id="' + id + '"]').remove();
      },
      error: function error(xhr, status, _error4) {
        console.debug("Ошибка запроса", status, _error4);
      }
    });
  }
};
$(document).ready(function () {
  moduleAutoprovision.initialize();
});
//# sourceMappingURL=module-autoprovision-index.js.map