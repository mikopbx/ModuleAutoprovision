"use strict";

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

/* global globalRootUrl, Config, Form */
var moduleAutoprovision = {
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
    other_pbx: 'OtherPBX'
  },
  initialize: function initialize() {
    moduleAutoprovision.initializeForm();
    moduleAutoprovision.initInputElements();
    moduleAutoprovision.bindAddRowButtons();
    moduleAutoprovision.bindRowActions();
  },

  /**
   * Wires the "+ Add" button of each editable table to a single row-cloner.
   * The button id encodes the table key: "add-new-{tableKey}-button".
   */
  bindAddRowButtons: function bindAddRowButtons() {
    $('body').on('click', '[id^="add-new-"][id$="-button"]', function handleAddRow() {
      var buttonId = $(this).attr('id'); // Strip "add-new-" prefix and "-button" suffix to get the table key.

      var tableKey = buttonId.slice('add-new-'.length, -'-button'.length); // Special case: the "templates" tab uses singular "template" in its button id.

      if (tableKey === 'template') {
        tableKey = 'templates';
      }

      var $table = $("#".concat(tableKey));

      if ($table.length === 0) {
        return;
      }

      var newId = "none_".concat(Date.now());
      var $newRow = $('<tr>').attr('id', newId);
      var templateHtml = $table.find('#emptyTemplateRow').html();
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
  bindRowActions: function bindRowActions() {
    var $body = $('body');
    $body.on('click', '.remove-row', function handleRemove(e) {
      e.preventDefault();

      if (!$(this).find('i').hasClass('close')) {
        return;
      }

      var $row = $(this).closest('tr');
      var $table = $row.closest('table');
      var tableKey = $table.attr('data-table-key');
      var model = $table.attr('data-model');
      var rowId = $row.attr('id');

      if (!model || !tableKey || !rowId) {
        return;
      }

      $.ajax({
        type: 'POST',
        url: "".concat(globalRootUrl).concat(moduleAutoprovision.idUrl, "/delete"),
        data: {
          table: model,
          id: rowId
        },
        success: function success() {
          $row.remove();
        },
        error: function error(xhr, status, _error) {
          /* eslint-disable-next-line no-console */
          console.debug('Delete request failed', status, _error);
        }
      });
    });
    $body.on('click', '.show-template-options', function handleEditTemplate(e) {
      e.preventDefault();
      var id = $(this).closest('tr').attr('id');
      moduleAutoprovision.showTemplateOptions(id);
    });
  },
  initInputElements: function initInputElements() {
    $('.menu .item').tab();
    $('div.dropdown').dropdown();
    $('input, textarea').change(function syncValueAttribute() {
      $(this).attr('value', $(this).val());
    });
  },

  /**
   * Notifies the backend that module-level settings changed so the worker can reload.
   */
  applyConfigurationChanges: function applyConfigurationChanges() {
    $.api({
      url: "".concat(Config.pbxUrl, "/pbxcore/api/modules/ModuleAutoprovision/reload"),
      on: 'now',
      successTest: function successTest(response) {
        return Object.keys(response).length > 0 && response.result === true;
      }
    });
  },
  cbBeforeSendForm: function cbBeforeSendForm(settings) {
    var result = settings;
    result.data = moduleAutoprovision.$formObj.form('get values');
    return result;
  },
  cbAfterSendForm: function cbAfterSendForm(response) {
    // Re-bind freshly inserted rows from mock ids to real database ids.
    Object.entries(response.resultSaveTables || {}).forEach(function (_ref) {
      var _ref2 = _slicedToArray(_ref, 2),
          table = _ref2[0],
          mapping = _ref2[1];

      Object.entries(mapping).forEach(function (_ref3) {
        var _ref4 = _slicedToArray(_ref3, 2),
            oldId = _ref4[0],
            newId = _ref4[1];

        var $syncTr = $("#".concat(table, " tr#").concat(oldId));
        var html = $syncTr.html();

        if (html) {
          $syncTr.html(html.replace(new RegExp(oldId, 'g'), newId));
        }

        $syncTr.attr('id', newId);
        $(".ui.modal[data-id=\"".concat(oldId, "\"][data-id-table=\"").concat(table, "\"]")).attr('data-id', newId);
      });
    }); // Rebuild every template-dropdown's option list from the current state of #templates.

    var templates = $('#templates td[data-label="name"]').map(function buildEntry() {
      return {
        id: $(this).parent().attr('id'),
        name: $(this).find('input').val()
      };
    }).get();
    $('td[data-label="template"] div.scrolling.menu').each(function rebuild() {
      var $menu = $(this).empty();
      templates.forEach(function (_ref5) {
        var id = _ref5.id,
            name = _ref5.name;
        $menu.append($('<div>').attr('data-value', id).attr('class', 'item').text(name));
      });
    });
    moduleAutoprovision.initInputElements();
    moduleAutoprovision.applyConfigurationChanges();
    moduleAutoprovision.$formObj.form();
    Form.setEvents();
  },
  initializeForm: function initializeForm() {
    Form.$formObj = moduleAutoprovision.$formObj;
    Form.url = "".concat(globalRootUrl).concat(moduleAutoprovision.idUrl, "/save");
    Form.cbBeforeSendForm = moduleAutoprovision.cbBeforeSendForm;
    Form.cbAfterSendForm = moduleAutoprovision.cbAfterSendForm;
    Form.initialize();
  },
  showTemplateOptions: function showTemplateOptions(id) {
    $(".ui.modal[data-id=\"".concat(id, "\"][data-id-table=\"templates\"]")).modal({
      closable: true,
      onApprove: function onApprove() {
        var value = $(this).find('textarea').val();
        $("textarea[name=\"templates-template-".concat($(this).attr('data-id'), "\"]")).val(value);
        Form.checkValues();
        return true;
      }
    }).modal('show');
  }
};
$(document).ready(function () {
  moduleAutoprovision.initialize();
});
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbInNyYy9tb2R1bGUtYXV0b3Byb3Zpc2lvbi5qcyJdLCJuYW1lcyI6WyJtb2R1bGVBdXRvcHJvdmlzaW9uIiwiaWRVcmwiLCIkZm9ybU9iaiIsIiQiLCJ0YWJsZU1hcCIsInRlbXBsYXRlcyIsInRlbXBsYXRlc191cmkiLCJwaG9uZV9zZXR0aW5ncyIsIm90aGVyX3BieCIsImluaXRpYWxpemUiLCJpbml0aWFsaXplRm9ybSIsImluaXRJbnB1dEVsZW1lbnRzIiwiYmluZEFkZFJvd0J1dHRvbnMiLCJiaW5kUm93QWN0aW9ucyIsIm9uIiwiaGFuZGxlQWRkUm93IiwiYnV0dG9uSWQiLCJhdHRyIiwidGFibGVLZXkiLCJzbGljZSIsImxlbmd0aCIsIiR0YWJsZSIsIm5ld0lkIiwiRGF0ZSIsIm5vdyIsIiRuZXdSb3ciLCJ0ZW1wbGF0ZUh0bWwiLCJmaW5kIiwiaHRtbCIsInJlcGxhY2UiLCJhcHBlbmQiLCJmb3JtIiwiRm9ybSIsInNldEV2ZW50cyIsIiRib2R5IiwiaGFuZGxlUmVtb3ZlIiwiZSIsInByZXZlbnREZWZhdWx0IiwiaGFzQ2xhc3MiLCIkcm93IiwiY2xvc2VzdCIsIm1vZGVsIiwicm93SWQiLCJhamF4IiwidHlwZSIsInVybCIsImdsb2JhbFJvb3RVcmwiLCJkYXRhIiwidGFibGUiLCJpZCIsInN1Y2Nlc3MiLCJyZW1vdmUiLCJlcnJvciIsInhociIsInN0YXR1cyIsImNvbnNvbGUiLCJkZWJ1ZyIsImhhbmRsZUVkaXRUZW1wbGF0ZSIsInNob3dUZW1wbGF0ZU9wdGlvbnMiLCJ0YWIiLCJkcm9wZG93biIsImNoYW5nZSIsInN5bmNWYWx1ZUF0dHJpYnV0ZSIsInZhbCIsImFwcGx5Q29uZmlndXJhdGlvbkNoYW5nZXMiLCJhcGkiLCJDb25maWciLCJwYnhVcmwiLCJzdWNjZXNzVGVzdCIsInJlc3BvbnNlIiwiT2JqZWN0Iiwia2V5cyIsInJlc3VsdCIsImNiQmVmb3JlU2VuZEZvcm0iLCJzZXR0aW5ncyIsImNiQWZ0ZXJTZW5kRm9ybSIsImVudHJpZXMiLCJyZXN1bHRTYXZlVGFibGVzIiwiZm9yRWFjaCIsIm1hcHBpbmciLCJvbGRJZCIsIiRzeW5jVHIiLCJSZWdFeHAiLCJtYXAiLCJidWlsZEVudHJ5IiwicGFyZW50IiwibmFtZSIsImdldCIsImVhY2giLCJyZWJ1aWxkIiwiJG1lbnUiLCJlbXB0eSIsInRleHQiLCJtb2RhbCIsImNsb3NhYmxlIiwib25BcHByb3ZlIiwidmFsdWUiLCJjaGVja1ZhbHVlcyIsImRvY3VtZW50IiwicmVhZHkiXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7O0FBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUVBLElBQU1BLG1CQUFtQixHQUFHO0FBQzNCQyxFQUFBQSxLQUFLLEVBQUUsMkNBRG9CO0FBRTNCQyxFQUFBQSxRQUFRLEVBQUVDLENBQUMsQ0FBQyw0QkFBRCxDQUZnQjs7QUFJM0I7QUFDRDtBQUNBO0FBQ0E7QUFDQTtBQUNDQyxFQUFBQSxRQUFRLEVBQUU7QUFDVEMsSUFBQUEsU0FBUyxFQUFFLFdBREY7QUFFVEMsSUFBQUEsYUFBYSxFQUFFLGNBRk47QUFHVEMsSUFBQUEsY0FBYyxFQUFFLGdCQUhQO0FBSVRDLElBQUFBLFNBQVMsRUFBRTtBQUpGLEdBVGlCO0FBZ0IzQkMsRUFBQUEsVUFoQjJCLHdCQWdCZDtBQUNaVCxJQUFBQSxtQkFBbUIsQ0FBQ1UsY0FBcEI7QUFDQVYsSUFBQUEsbUJBQW1CLENBQUNXLGlCQUFwQjtBQUNBWCxJQUFBQSxtQkFBbUIsQ0FBQ1ksaUJBQXBCO0FBQ0FaLElBQUFBLG1CQUFtQixDQUFDYSxjQUFwQjtBQUNBLEdBckIwQjs7QUF1QjNCO0FBQ0Q7QUFDQTtBQUNBO0FBQ0NELEVBQUFBLGlCQTNCMkIsK0JBMkJQO0FBQ25CVCxJQUFBQSxDQUFDLENBQUMsTUFBRCxDQUFELENBQVVXLEVBQVYsQ0FBYSxPQUFiLEVBQXNCLGlDQUF0QixFQUF5RCxTQUFTQyxZQUFULEdBQXdCO0FBQ2hGLFVBQU1DLFFBQVEsR0FBR2IsQ0FBQyxDQUFDLElBQUQsQ0FBRCxDQUFRYyxJ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TTtBQUN2Qm5HLEVBQUFBLG1CQUFtQixDQUFDUyxVQUFwQjtBQUNBLENBRkQiLCJzb3VyY2VzQ29udGVudCI6WyIvKlxuICogQ29weXJpZ2h0IMKpIE1JS08gTExDIC0gQWxsIFJpZ2h0cyBSZXNlcnZlZFxuICogVW5hdXRob3JpemVkIGNvcHlpbmcgb2YgdGhpcyBmaWxlLCB2aWEgYW55IG1lZGl1bSBpcyBzdHJpY3RseSBwcm9oaWJpdGVkXG4gKiBQcm9wcmlldGFyeSBhbmQgY29uZmlkZW50aWFsXG4gKi9cblxuLyogZ2xvYmFsIGdsb2JhbFJvb3RVcmwsIENvbmZpZywgRm9ybSAqL1xuXG5jb25zdCBtb2R1bGVBdXRvcHJvdmlzaW9uID0ge1xuXHRpZFVybDogJ21vZHVsZS1hdXRvcHJvdmlzaW9uL21vZHVsZS1hdXRvcHJvdmlzaW9uJyxcblx0JGZvcm1PYmo6ICQoJyNtb2R1bGUtYXV0b3Byb3Zpc2lvbi1mb3JtJyksXG5cblx0LyoqXG5cdCAqIE1hcHBpbmcgb2YgZm9ybS1zZWN0aW9uIHByZWZpeCDihpIgc2VydmVyLXNpZGUgbW9kZWwgY2xhc3MuXG5cdCAqIE11c3Qgc3RheSBpbiBzeW5jIHdpdGggQXBwL0NvbnRyb2xsZXJzL01vZHVsZUF1dG9wcm92aXNpb25Db250cm9sbGVyOjpUQUJMRV9NQVBcblx0ICogYW5kIEFwcC9WaWV3cy9Nb2R1bGVBdXRvcHJvdmlzaW9uL2luZGV4LnZvbHQgKGRhdGEtdGFibGUta2V5IC8gZGF0YS1tb2RlbCBhdHRycykuXG5cdCAqL1xuXHR0YWJsZU1hcDoge1xuXHRcdHRlbXBsYXRlczogJ1RlbXBsYXRlcycsXG5cdFx0dGVtcGxhdGVzX3VyaTogJ1RlbXBsYXRlc1VyaScsXG5cdFx0cGhvbmVfc2V0dGluZ3M6ICdUZW1wbGF0ZXNVc2VycycsXG5cdFx0b3RoZXJfcGJ4OiAnT3RoZXJQQlgnLFxuXHR9LFxuXG5cdGluaXRpYWxpemUoKSB7XG5cdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbi5pbml0aWFsaXplRm9ybSgpO1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb24uaW5pdElucHV0RWxlbWVudHMoKTtcblx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uLmJpbmRBZGRSb3dCdXR0b25zKCk7XG5cdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbi5iaW5kUm93QWN0aW9ucygpO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBXaXJlcyB0aGUgXCIrIEFkZFwiIGJ1dHRvbiBvZiBlYWNoIGVkaXRhYmxlIHRhYmxlIHRvIGEgc2luZ2xlIHJvdy1jbG9uZXIuXG5cdCAqIFRoZSBidXR0b24gaWQgZW5jb2RlcyB0aGUgdGFibGUga2V5OiBcImFkZC1uZXcte3RhYmxlS2V5fS1idXR0b25cIi5cblx0ICovXG5cdGJpbmRBZGRSb3dCdXR0b25zKCkge1xuXHRcdCQoJ2JvZHknKS5vbignY2xpY2snLCAnW2lkXj1cImFkZC1uZXctXCJdW2lkJD1cIi1idXR0b25cIl0nLCBmdW5jdGlvbiBoYW5kbGVBZGRSb3coKSB7XG5cdFx0XHRjb25zdCBidXR0b25JZCA9ICQodGhpcykuYXR0cignaWQnKTtcblx0XHRcdC8vIFN0cmlwIFwiYWRkLW5ldy1cIiBwcmVmaXggYW5kIFwiLWJ1dHRvblwiIHN1ZmZpeCB0byBnZXQgdGhlIHRhYmxlIGtleS5cblx0XHRcdGxldCB0YWJsZUtleSA9IGJ1dHRvbklkLnNsaWNlKCdhZGQtbmV3LScubGVuZ3RoLCAtKCctYnV0dG9uJy5sZW5ndGgpKTtcblx0XHRcdC8vIFNwZWNpYWwgY2FzZTogdGhlIFwidGVtcGxhdGVzXCIgdGFiIHVzZXMgc2luZ3VsYXIgXCJ0ZW1wbGF0ZVwiIGluIGl0cyBidXR0b24gaWQuXG5cdFx0XHRpZiAodGFibGVLZXkgPT09ICd0ZW1wbGF0ZScpIHtcblx0XHRcdFx0dGFibGVLZXkgPSAndGVtcGxhdGVzJztcblx0XHRcdH1cblx0XHRcdGNvbnN0ICR0YWJsZSA9ICQoYCMke3RhYmxlS2V5fWApO1xuXHRcdFx0aWYgKCR0YWJsZS5sZW5ndGggPT09IDApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXHRcdFx0Y29uc3QgbmV3SWQgPSBgbm9uZV8ke0RhdGUubm93KCl9YDtcblx0XHRcdGNvbnN0ICRuZXdSb3cgPSAkKCc8dHI+JykuYXR0cignaWQnLCBuZXdJZCk7XG5cdFx0XHRjb25zdCB0ZW1wbGF0ZUh0bWwgPSAkdGFibGUuZmluZCgnI2VtcHR5VGVtcGxhdGVSb3cnKS5odG1sKCk7XG5cdFx0XHQkbmV3Um93Lmh0bWwodGVtcGxhdGVIdG1sLnJlcGxhY2UoL2VtcHR5VGVtcGxhdGVSb3cvZywgbmV3SWQpKTtcblx0XHRcdCR0YWJsZS5maW5kKCd0Ym9keScpLmFwcGVuZCgkbmV3Um93KTtcblx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb24uaW5pdElucHV0RWxlbWVudHMoKTtcblx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb24uJGZvcm1PYmouZm9ybSgpO1xuXHRcdFx0Rm9ybS5zZXRFdmVudHMoKTtcblx0XHR9KTtcblx0fSxcblxuXHQvKipcblx0ICogU2luZ2xlIGhhbmRsZXIgZm9yIHJvdyBkZWxldGUgYW5kIHRlbXBsYXRlLWVkaXQgYnV0dG9ucy5cblx0ICogVGFyZ2V0cyBhcmUgaWRlbnRpZmllZCBieSAucmVtb3ZlLXJvdyAvIC5zaG93LXRlbXBsYXRlLW9wdGlvbnMgY2xhc3Nlc1xuXHQgKiBhbmQgdGhlIHRhYmxlIGlzIHJlYWQgZnJvbSB0aGUgbmVhcmVzdCBbZGF0YS10YWJsZS1rZXldIC8gW2RhdGEtbW9kZWxdIHRhYmxlIGVsZW1lbnQuXG5cdCAqL1xuXHRiaW5kUm93QWN0aW9ucygpIHtcblx0XHRjb25zdCAkYm9keSA9ICQoJ2JvZHknKTtcblx0XHQkYm9keS5vbignY2xpY2snLCAnLnJlbW92ZS1yb3cnLCBmdW5jdGlvbiBoYW5kbGVSZW1vdmUoZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0aWYgKCEkKHRoaXMpLmZpbmQoJ2knKS5oYXNDbGFzcygnY2xvc2UnKSkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cdFx0XHRjb25zdCAkcm93ID0gJCh0aGlzKS5jbG9zZXN0KCd0cicpO1xuXHRcdFx0Y29uc3QgJHRhYmxlID0gJHJvdy5jbG9zZXN0KCd0YWJsZScpO1xuXHRcdFx0Y29uc3QgdGFibGVLZXkgPSAkdGFibGUuYXR0cignZGF0YS10YWJsZS1rZXknKTtcblx0XHRcdGNvbnN0IG1vZGVsID0gJHRhYmxlLmF0dHIoJ2RhdGEtbW9kZWwnKTtcblx0XHRcdGNvbnN0IHJvd0lkID0gJHJvdy5hdHRyKCdpZCcpO1xuXHRcdFx0aWYgKCFtb2RlbCB8fCAhdGFibGVLZXkgfHwgIXJvd0lkKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblx0XHRcdCQuYWpheCh7XG5cdFx0XHRcdHR5cGU6ICdQT1NUJyxcblx0XHRcdFx0dXJsOiBgJHtnbG9iYWxSb290VXJsfSR7bW9kdWxlQXV0b3Byb3Zpc2lvbi5pZFVybH0vZGVsZXRlYCxcblx0XHRcdFx0ZGF0YTogeyB0YWJsZTogbW9kZWwsIGlkOiByb3dJZCB9LFxuXHRcdFx0XHRzdWNjZXNzKCkge1xuXHRcdFx0XHRcdCRyb3cucmVtb3ZlKCk7XG5cdFx0XHRcdH0sXG5cdFx0XHRcdGVycm9yKHhociwgc3RhdHVzLCBlcnJvcikge1xuXHRcdFx0XHRcdC8qIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBuby1jb25zb2xlICovXG5cdFx0XHRcdFx0Y29uc29sZS5kZWJ1ZygnRGVsZXRlIHJlcXVlc3QgZmFpbGVkJywgc3RhdHVzLCBlcnJvcik7XG5cdFx0XHRcdH0sXG5cdFx0XHR9KTtcblx0XHR9KTtcblxuXHRcdCRib2R5Lm9uKCdjbGljaycsICcuc2hvdy10ZW1wbGF0ZS1vcHRpb25zJywgZnVuY3Rpb24gaGFuZGxlRWRpdFRlbXBsYXRlKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGlkID0gJCh0aGlzKS5jbG9zZXN0KCd0cicpLmF0dHIoJ2lkJyk7XG5cdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uLnNob3dUZW1wbGF0ZU9wdGlvbnMoaWQpO1xuXHRcdH0pO1xuXHR9LFxuXG5cdGluaXRJbnB1dEVsZW1lbnRzKCkge1xuXHRcdCQoJy5tZW51IC5pdGVtJykudGFiKCk7XG5cdFx0JCgnZGl2LmRyb3Bkb3duJykuZHJvcGRvd24oKTtcblx0XHQkKCdpbnB1dCwgdGV4dGFyZWEnKS5jaGFuZ2UoZnVuY3Rpb24gc3luY1ZhbHVlQXR0cmlidXRlKCkge1xuXHRcdFx0JCh0aGlzKS5hdHRyKCd2YWx1ZScsICQodGhpcykudmFsKCkpO1xuXHRcdH0pO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBOb3RpZmllcyB0aGUgYmFja2VuZCB0aGF0IG1vZHVsZS1sZXZlbCBzZXR0aW5ncyBjaGFuZ2VkIHNvIHRoZSB3b3JrZXIgY2FuIHJlbG9hZC5cblx0ICovXG5cdGFwcGx5Q29uZmlndXJhdGlvbkNoYW5nZXMoKSB7XG5cdFx0JC5hcGkoe1xuXHRcdFx0dXJsOiBgJHtDb25maWcucGJ4VXJsfS9wYnhjb3JlL2FwaS9tb2R1bGVzL01vZHVsZUF1dG9wcm92aXNpb24vcmVsb2FkYCxcblx0XHRcdG9uOiAnbm93Jyxcblx0XHRcdHN1Y2Nlc3NUZXN0KHJlc3BvbnNlKSB7XG5cdFx0XHRcdHJldHVybiBPYmplY3Qua2V5cyhyZXNwb25zZSkubGVuZ3RoID4gMCAmJiByZXNwb25zZS5yZXN1bHQgPT09IHRydWU7XG5cdFx0XHR9LFxuXHRcdH0pO1xuXHR9LFxuXG5cdGNiQmVmb3JlU2VuZEZvcm0oc2V0dGluZ3MpIHtcblx0XHRjb25zdCByZXN1bHQgPSBzZXR0aW5ncztcblx0XHRyZXN1bHQuZGF0YSA9IG1vZHVsZUF1dG9wcm92aXNpb24uJGZvcm1PYmouZm9ybSgnZ2V0IHZhbHVlcycpO1xuXHRcdHJldHVybiByZXN1bHQ7XG5cdH0sXG5cblx0Y2JBZnRlclNlbmRGb3JtKHJlc3BvbnNlKSB7XG5cdFx0Ly8gUmUtYmluZCBmcmVzaGx5IGluc2VydGVkIHJvd3MgZnJvbSBtb2NrIGlkcyB0byByZWFsIGRhdGFiYXNlIGlkcy5cblx0XHRPYmplY3QuZW50cmllcyhyZXNwb25zZS5yZXN1bHRTYXZlVGFibGVzIHx8IHt9KS5mb3JFYWNoKChbdGFibGUsIG1hcHBpbmddKSA9PiB7XG5cdFx0XHRPYmplY3QuZW50cmllcyhtYXBwaW5nKS5mb3JFYWNoKChbb2xkSWQsIG5ld0lkXSkgPT4ge1xuXHRcdFx0XHRjb25zdCAkc3luY1RyID0gJChgIyR7dGFibGV9IHRyIyR7b2xkSWR9YCk7XG5cdFx0XHRcdGNvbnN0IGh0bWwgPSAkc3luY1RyLmh0bWwoKTtcblx0XHRcdFx0aWYgKGh0bWwpIHtcblx0XHRcdFx0XHQkc3luY1RyLmh0bWwoaHRtbC5yZXBsYWNlKG5ldyBSZWdFeHAob2xkSWQsICdnJyksIG5ld0lkKSk7XG5cdFx0XHRcdH1cblx0XHRcdFx0JHN5bmNUci5hdHRyKCdpZCcsIG5ld0lkKTtcblx0XHRcdFx0JChgLnVpLm1vZGFsW2RhdGEtaWQ9XCIke29sZElkfVwiXVtkYXRhLWlkLXRhYmxlPVwiJHt0YWJsZX1cIl1gKS5hdHRyKCdkYXRhLWlkJywgbmV3SWQpO1xuXHRcdFx0fSk7XG5cdFx0fSk7XG5cblx0XHQvLyBSZWJ1aWxkIGV2ZXJ5IHRlbXBsYXRlLWRyb3Bkb3duJ3Mgb3B0aW9uIGxpc3QgZnJvbSB0aGUgY3VycmVudCBzdGF0ZSBvZiAjdGVtcGxhdGVzLlxuXHRcdGNvbnN0IHRlbXBsYXRlcyA9ICQoJyN0ZW1wbGF0ZXMgdGRbZGF0YS1sYWJlbD1cIm5hbWVcIl0nKS5tYXAoZnVuY3Rpb24gYnVpbGRFbnRyeSgpIHtcblx0XHRcdHJldHVybiB7XG5cdFx0XHRcdGlkOiAkKHRoaXMpLnBhcmVudCgpLmF0dHIoJ2lkJyksXG5cdFx0XHRcdG5hbWU6ICQodGhpcykuZmluZCgnaW5wdXQnKS52YWwoKSxcblx0XHRcdH07XG5cdFx0fSkuZ2V0KCk7XG5cdFx0JCgndGRbZGF0YS1sYWJlbD1cInRlbXBsYXRlXCJdIGRpdi5zY3JvbGxpbmcubWVudScpLmVhY2goZnVuY3Rpb24gcmVidWlsZCgpIHtcblx0XHRcdGNvbnN0ICRtZW51ID0gJCh0aGlzKS5lbXB0eSgpO1xuXHRcdFx0dGVtcGxhdGVzLmZvckVhY2goKHsgaWQsIG5hbWUgfSkgPT4ge1xuXHRcdFx0XHQkbWVudS5hcHBlbmQoJCgnPGRpdj4nKS5hdHRyKCdkYXRhLXZhbHVlJywgaWQpLmF0dHIoJ2NsYXNzJywgJ2l0ZW0nKS50ZXh0KG5hbWUpKTtcblx0XHRcdH0pO1xuXHRcdH0pO1xuXG5cdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbi5pbml0SW5wdXRFbGVtZW50cygpO1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb24uYXBwbHlDb25maWd1cmF0aW9uQ2hhbmdlcygpO1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb24uJGZvcm1PYmouZm9ybSgpO1xuXHRcdEZvcm0uc2V0RXZlbnRzKCk7XG5cdH0sXG5cblx0aW5pdGlhbGl6ZUZvcm0oKSB7XG5cdFx0Rm9ybS4kZm9ybU9iaiA9IG1vZHVsZUF1dG9wcm92aXNpb24uJGZvcm1PYmo7XG5cdFx0Rm9ybS51cmwgPSBgJHtnbG9iYWxSb290VXJsfSR7bW9kdWxlQXV0b3Byb3Zpc2lvbi5pZFVybH0vc2F2ZWA7XG5cdFx0Rm9ybS5jYkJlZm9yZVNlbmRGb3JtID0gbW9kdWxlQXV0b3Byb3Zpc2lvbi5jYkJlZm9yZVNlbmRGb3JtO1xuXHRcdEZvcm0uY2JBZnRlclNlbmRGb3JtID0gbW9kdWxlQXV0b3Byb3Zpc2lvbi5jYkFmdGVyU2VuZEZvcm07XG5cdFx0Rm9ybS5pbml0aWFsaXplKCk7XG5cdH0sXG5cblx0c2hvd1RlbXBsYXRlT3B0aW9ucyhpZCkge1xuXHRcdCQoYC51aS5tb2RhbFtkYXRhLWlkPVwiJHtpZH1cIl1bZGF0YS1pZC10YWJsZT1cInRlbXBsYXRlc1wiXWApLm1vZGFsKHtcblx0XHRcdGNsb3NhYmxlOiB0cnVlLFxuXHRcdFx0b25BcHByb3ZlKCkge1xuXHRcdFx0XHRjb25zdCB2YWx1ZSA9ICQodGhpcykuZmluZCgndGV4dGFyZWEnKS52YWwoKTtcblx0XHRcdFx0JChgdGV4dGFyZWFbbmFtZT1cInRlbXBsYXRlcy10ZW1wbGF0ZS0keyQodGhpcykuYXR0cignZGF0YS1pZCcpfVwiXWApLnZhbCh2YWx1ZSk7XG5cdFx0XHRcdEZvcm0uY2hlY2tWYWx1ZXMoKTtcblx0XHRcdFx0cmV0dXJuIHRydWU7XG5cdFx0XHR9LFxuXHRcdH0pLm1vZGFsKCdzaG93Jyk7XG5cdH0sXG59O1xuXG4kKGRvY3VtZW50KS5yZWFkeSgoKSA9PiB7XG5cdG1vZHVsZUF1dG9wcm92aXNpb24uaW5pdGlhbGl6ZSgpO1xufSk7XG4iXX0=