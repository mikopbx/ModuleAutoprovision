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
  saveUrl: 'module-autoprovision/module-autoprovision/save',
  $formObj: $('#module-autoprovision-form'),
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
   *
   * Delete is deferred: unsaved rows (id "none_*") are dropped from the DOM, and persisted
   * rows get a hidden "<table>[<id>][__delete]=1" input plus a `marked-for-delete` class.
   * A second click on a marked row un-marks it. Deletion takes effect on Save.
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
      var rowId = $row.attr('id');

      if (!tableKey || !rowId) {
        return;
      } // Unsaved row — just drop it; nothing for the server to delete.


      if (rowId.startsWith('none_')) {
        $row.remove();
        Form.dataChanged();
        return;
      } // Toggle deletion mark for persisted rows.


      var deleteInputName = "".concat(tableKey, "[").concat(rowId, "][__delete]");
      var $existing = $row.find("input[name=\"".concat(deleteInputName, "\"]"));

      if ($existing.length) {
        $existing.remove();
        $row.removeClass('marked-for-delete');
      } else {
        $('<input>', {
          type: 'hidden',
          name: deleteInputName,
          value: '1'
        }).appendTo($row);
        $row.addClass('marked-for-delete');
      } // Re-init form so Semantic UI's `form('get values')` picks up the dynamic input.


      moduleAutoprovision.$formObj.form();
      Form.dataChanged();
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
    // Form.js calls this on both success and failure paths. On failure the controller
    // rolled the transaction back, so leave the marked-for-delete rows visible — removing
    // them would lie about the DB state.
    if (response.success === true) {
      $('tr.marked-for-delete').remove();
    } // Re-bind freshly inserted rows from mock ids to real database ids.


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
    Form.url = "".concat(globalRootUrl).concat(moduleAutoprovision.saveUrl);
    Form.cbBeforeSendForm = moduleAutoprovision.cbBeforeSendForm;
    Form.cbAfterSendForm = moduleAutoprovision.cbAfterSendForm;
    Form.initialize();
  },
  showTemplateOptions: function showTemplateOptions(id) {
    $(".ui.modal[data-id=\"".concat(id, "\"][data-id-table=\"templates\"]")).modal({
      closable: true,
      onApprove: function onApprove() {
        var value = $(this).find('textarea').val();
        $("textarea[name=\"templates[".concat($(this).attr('data-id'), "][template]\"]")).val(value);
        Form.checkValues();
        return true;
      }
    }).modal('show');
  }
};
$(document).ready(function () {
  moduleAutoprovision.initialize();
});
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbInNyYy9tb2R1bGUtYXV0b3Byb3Zpc2lvbi5qcyJdLCJuYW1lcyI6WyJtb2R1bGVBdXRvcHJvdmlzaW9uIiwic2F2ZVVybCIsIiRmb3JtT2JqIiwiJCIsImluaXRpYWxpemUiLCJpbml0aWFsaXplRm9ybSIsImluaXRJbnB1dEVsZW1lbnRzIiwiYmluZEFkZFJvd0J1dHRvbnMiLCJiaW5kUm93QWN0aW9ucyIsIm9uIiwiaGFuZGxlQWRkUm93IiwiYnV0dG9uSWQiLCJhdHRyIiwidGFibGVLZXkiLCJzbGljZSIsImxlbmd0aCIsIiR0YWJsZSIsIm5ld0lkIiwiRGF0ZSIsIm5vdyIsIiRuZXdSb3ciLCJ0ZW1wbGF0ZUh0bWwiLCJmaW5kIiwiaHRtbCIsInJlcGxhY2UiLCJhcHBlbmQiLCJmb3JtIiwiRm9ybSIsInNldEV2ZW50cyIsIiRib2R5IiwiaGFuZGxlUmVtb3ZlIiwiZSIsInByZXZlbnREZWZhdWx0IiwiaGFzQ2xhc3MiLCIkcm93IiwiY2xvc2VzdCIsInJvd0lkIiwic3RhcnRzV2l0aCIsInJlbW92ZSIsImRhdGFDaGFuZ2VkIiwiZGVsZXRlSW5wdXROYW1lIiwiJGV4aXN0aW5nIiwicmVtb3ZlQ2xhc3MiLCJ0eXBlIiwibmFtZSIsInZhbHVlIiwiYXBwZW5kVG8iLCJhZGRDbGFzcyIsImhhbmRsZUVkaXRUZW1wbGF0ZSIsImlkIiwic2hvd1RlbXBsYXRlT3B0aW9ucyIsInRhYiIsImRyb3Bkb3duIiwiY2hhbmdlIiwic3luY1ZhbHVlQXR0cmlidXRlIiwidmFsIiwiYXBwbHlDb25maWd1cmF0aW9uQ2hhbmdlcyIsImFwaSIsInVybCIsIkNvbmZpZyIsInBieFVybCIsInN1Y2Nlc3NUZXN0IiwicmVzcG9uc2UiLCJPYmplY3QiLCJrZXlzIiwicmVzdWx0IiwiY2JCZWZvcmVTZW5kRm9ybSIsInNldHRpbmdzIiwiZGF0YSIsImNiQWZ0ZXJTZW5kRm9ybSIsInN1Y2Nlc3MiLCJlbnRyaWVzIiwicmVzdWx0U2F2ZVRhYmxlcyIsImZvckVhY2giLCJ0YWJsZSIsIm1hcHBpbmciLCJvbGRJZCIsIiRzeW5jVHIiLCJSZWdFeHAiLCJ0ZW1wbGF0ZXMiLCJtYXAiLCJidWlsZEVudHJ5IiwicGFyZW50IiwiZ2V0IiwiZWFjaCIsInJlYnVpbGQiLCIkbWVudSIsImVtcHR5IiwidGV4dCIsImdsb2JhbFJvb3RVcmwiLCJtb2RhbCIsImNsb3NhYmxlIiwib25BcHByb3ZlIiwiY2hlY2tWYWx1ZXMiLCJkb2N1bWVudCIsInJlYWR5Il0sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7OztBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFFQSxJQUFNQSxtQkFBbUIsR0FBRztBQUMzQkMsRUFBQUEsT0FBTyxFQUFFLGdEQURrQjtBQUUzQkMsRUFBQUEsUUFBUSxFQUFFQyxDQUFDLENBQUMsNEJBQUQsQ0FGZ0I7QUFJM0JDLEVBQUFBLFVBSjJCLHdCQUlkO0FBQ1pKLElBQUFBLG1CQUFtQixDQUFDSyxjQUFwQjtBQUNBTCxJQUFBQSxtQkFBbUIsQ0FBQ00saUJBQXBCO0FBQ0FOLElBQUFBLG1CQUFtQixDQUFDTyxpQkFBcEI7QUFDQVAsSUFBQUEsbUJBQW1CLENBQUNRLGNBQXBCO0FBQ0EsR0FUMEI7O0FBVzNCO0FBQ0Q7QUFDQTtBQUNBO0FBQ0NELEVBQUFBLGlCQWYyQiwrQkFlUDtBQUNuQkosSUFBQUEsQ0FBQyxDQUFDLE1BQUQsQ0FBRCxDQUFVTSxFQUFWLENBQWEsT0FBYixFQUFzQixpQ0FBdEIsRUFBeUQsU0FBU0MsWUFBVCxHQUF3QjtBQUNoRixVQUFNQyxRQUFRLEdBQUdSLENBQUMsQ0FBQyxJQUFELENBQUQsQ0FBUVMsSUFBUixDQUFhLElBQWIsQ0FBakIsQ0FEZ0YsQ0FFaEY7O0FBQ0EsVUFBSUMsUUFBUSxHQUFHRixRQUFRLENBQUNHLEtBQVQsQ0FBZSxXQUFXQyxNQUExQixFQUFrQyxDQUFFLFVBQVVBLE1BQTlDLENBQWYsQ0FIZ0YsQ0FJaEY7O0FBQ0EsVUFBSUYsUUFBUSxLQUFLLFVBQWpCLEVBQTZCO0FBQzVCQSxRQUFBQSxRQUFRLEdBQUcsV0FB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Q0EsZUFBTyxJQUFQO0FBQ0E7QUFQK0QsS0FBakUsRUFRR0gsS0FSSCxDQVFTLE1BUlQ7QUFTQTtBQWhMMEIsQ0FBNUI7QUFtTEF2RixDQUFDLENBQUMyRixRQUFELENBQUQsQ0FBWUMsS0FBWixDQUFrQixZQUFNO0FBQ3ZCL0YsRUFBQUEsbUJBQW1CLENBQUNJLFVBQXBCO0FBQ0EsQ0FGRCIsInNvdXJjZXNDb250ZW50IjpbIi8qXG4gKiBDb3B5cmlnaHQgwqkgTUlLTyBMTEMgLSBBbGwgUmlnaHRzIFJlc2VydmVkXG4gKiBVbmF1dGhvcml6ZWQgY29weWluZyBvZiB0aGlzIGZpbGUsIHZpYSBhbnkgbWVkaXVtIGlzIHN0cmljdGx5IHByb2hpYml0ZWRcbiAqIFByb3ByaWV0YXJ5IGFuZCBjb25maWRlbnRpYWxcbiAqL1xuXG4vKiBnbG9iYWwgZ2xvYmFsUm9vdFVybCwgQ29uZmlnLCBGb3JtICovXG5cbmNvbnN0IG1vZHVsZUF1dG9wcm92aXNpb24gPSB7XG5cdHNhdmVVcmw6ICdtb2R1bGUtYXV0b3Byb3Zpc2lvbi9tb2R1bGUtYXV0b3Byb3Zpc2lvbi9zYXZlJyxcblx0JGZvcm1PYmo6ICQoJyNtb2R1bGUtYXV0b3Byb3Zpc2lvbi1mb3JtJyksXG5cblx0aW5pdGlhbGl6ZSgpIHtcblx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uLmluaXRpYWxpemVGb3JtKCk7XG5cdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbi5pbml0SW5wdXRFbGVtZW50cygpO1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb24uYmluZEFkZFJvd0J1dHRvbnMoKTtcblx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uLmJpbmRSb3dBY3Rpb25zKCk7XG5cdH0sXG5cblx0LyoqXG5cdCAqIFdpcmVzIHRoZSBcIisgQWRkXCIgYnV0dG9uIG9mIGVhY2ggZWRpdGFibGUgdGFibGUgdG8gYSBzaW5nbGUgcm93LWNsb25lci5cblx0ICogVGhlIGJ1dHRvbiBpZCBlbmNvZGVzIHRoZSB0YWJsZSBrZXk6IFwiYWRkLW5ldy17dGFibGVLZXl9LWJ1dHRvblwiLlxuXHQgKi9cblx0YmluZEFkZFJvd0J1dHRvbnMoKSB7XG5cdFx0JCgnYm9keScpLm9uKCdjbGljaycsICdbaWRePVwiYWRkLW5ldy1cIl1baWQkPVwiLWJ1dHRvblwiXScsIGZ1bmN0aW9uIGhhbmRsZUFkZFJvdygpIHtcblx0XHRcdGNvbnN0IGJ1dHRvbklkID0gJCh0aGlzKS5hdHRyKCdpZCcpO1xuXHRcdFx0Ly8gU3RyaXAgXCJhZGQtbmV3LVwiIHByZWZpeCBhbmQgXCItYnV0dG9uXCIgc3VmZml4IHRvIGdldCB0aGUgdGFibGUga2V5LlxuXHRcdFx0bGV0IHRhYmxlS2V5ID0gYnV0dG9uSWQuc2xpY2UoJ2FkZC1uZXctJy5sZW5ndGgsIC0oJy1idXR0b24nLmxlbmd0aCkpO1xuXHRcdFx0Ly8gU3BlY2lhbCBjYXNlOiB0aGUgXCJ0ZW1wbGF0ZXNcIiB0YWIgdXNlcyBzaW5ndWxhciBcInRlbXBsYXRlXCIgaW4gaXRzIGJ1dHRvbiBpZC5cblx0XHRcdGlmICh0YWJsZUtleSA9PT0gJ3RlbXBsYXRlJykge1xuXHRcdFx0XHR0YWJsZUtleSA9ICd0ZW1wbGF0ZXMnO1xuXHRcdFx0fVxuXHRcdFx0Y29uc3QgJHRhYmxlID0gJChgIyR7dGFibGVLZXl9YCk7XG5cdFx0XHRpZiAoJHRhYmxlLmxlbmd0aCA9PT0gMCkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cdFx0XHRjb25zdCBuZXdJZCA9IGBub25lXyR7RGF0ZS5ub3coKX1gO1xuXHRcdFx0Y29uc3QgJG5ld1JvdyA9ICQoJzx0cj4nKS5hdHRyKCdpZCcsIG5ld0lkKTtcblx0XHRcdGNvbnN0IHRlbXBsYXRlSHRtbCA9ICR0YWJsZS5maW5kKCcjZW1wdHlUZW1wbGF0ZVJvdycpLmh0bWwoKTtcblx0XHRcdCRuZXdSb3cuaHRtbCh0ZW1wbGF0ZUh0bWwucmVwbGFjZSgvZW1wdHlUZW1wbGF0ZVJvdy9nLCBuZXdJZCkpO1xuXHRcdFx0JHRhYmxlLmZpbmQoJ3Rib2R5JykuYXBwZW5kKCRuZXdSb3cpO1xuXHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbi5pbml0SW5wdXRFbGVtZW50cygpO1xuXHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbi4kZm9ybU9iai5mb3JtKCk7XG5cdFx0XHRGb3JtLnNldEV2ZW50cygpO1xuXHRcdH0pO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBTaW5nbGUgaGFuZGxlciBmb3Igcm93IGRlbGV0ZSBhbmQgdGVtcGxhdGUtZWRpdCBidXR0b25zLlxuXHQgKlxuXHQgKiBEZWxldGUgaXMgZGVmZXJyZWQ6IHVuc2F2ZWQgcm93cyAoaWQgXCJub25lXypcIikgYXJlIGRyb3BwZWQgZnJvbSB0aGUgRE9NLCBhbmQgcGVyc2lzdGVkXG5cdCAqIHJvd3MgZ2V0IGEgaGlkZGVuIFwiPHRhYmxlPls8aWQ+XVtfX2RlbGV0ZV09MVwiIGlucHV0IHBsdXMgYSBgbWFya2VkLWZvci1kZWxldGVgIGNsYXNzLlxuXHQgKiBBIHNlY29uZCBjbGljayBvbiBhIG1hcmtlZCByb3cgdW4tbWFya3MgaXQuIERlbGV0aW9uIHRha2VzIGVmZmVjdCBvbiBTYXZlLlxuXHQgKi9cblx0YmluZFJvd0FjdGlvbnMoKSB7XG5cdFx0Y29uc3QgJGJvZHkgPSAkKCdib2R5Jyk7XG5cdFx0JGJvZHkub24oJ2NsaWNrJywgJy5yZW1vdmUtcm93JywgZnVuY3Rpb24gaGFuZGxlUmVtb3ZlKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGlmICghJCh0aGlzKS5maW5kKCdpJykuaGFzQ2xhc3MoJ2Nsb3NlJykpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXHRcdFx0Y29uc3QgJHJvdyA9ICQodGhpcykuY2xvc2VzdCgndHInKTtcblx0XHRcdGNvbnN0ICR0YWJsZSA9ICRyb3cuY2xvc2VzdCgndGFibGUnKTtcblx0XHRcdGNvbnN0IHRhYmxlS2V5ID0gJHRhYmxlLmF0dHIoJ2RhdGEtdGFibGUta2V5Jyk7XG5cdFx0XHRjb25zdCByb3dJZCA9ICRyb3cuYXR0cignaWQnKTtcblx0XHRcdGlmICghdGFibGVLZXkgfHwgIXJvd0lkKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Ly8gVW5zYXZlZCByb3cg4oCUIGp1c3QgZHJvcCBpdDsgbm90aGluZyBmb3IgdGhlIHNlcnZlciB0byBkZWxldGUuXG5cdFx0XHRpZiAocm93SWQuc3RhcnRzV2l0aCgnbm9uZV8nKSkge1xuXHRcdFx0XHQkcm93LnJlbW92ZSgpO1xuXHRcdFx0XHRGb3JtLmRhdGFDaGFuZ2VkKCk7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Ly8gVG9nZ2xlIGRlbGV0aW9uIG1hcmsgZm9yIHBlcnNpc3RlZCByb3dzLlxuXHRcdFx0Y29uc3QgZGVsZXRlSW5wdXROYW1lID0gYCR7dGFibGVLZXl9WyR7cm93SWR9XVtfX2RlbGV0ZV1gO1xuXHRcdFx0Y29uc3QgJGV4aXN0aW5nID0gJHJvdy5maW5kKGBpbnB1dFtuYW1lPVwiJHtkZWxldGVJbnB1dE5hbWV9XCJdYCk7XG5cdFx0XHRpZiAoJGV4aXN0aW5nLmxlbmd0aCkge1xuXHRcdFx0XHQkZXhpc3RpbmcucmVtb3ZlKCk7XG5cdFx0XHRcdCRyb3cucmVtb3ZlQ2xhc3MoJ21hcmtlZC1mb3ItZGVsZXRlJyk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHQkKCc8aW5wdXQ+JywgeyB0eXBlOiAnaGlkZGVuJywgbmFtZTogZGVsZXRlSW5wdXROYW1lLCB2YWx1ZTogJzEnIH0pLmFwcGVuZFRvKCRyb3cpO1xuXHRcdFx0XHQkcm93LmFkZENsYXNzKCdtYXJrZWQtZm9yLWRlbGV0ZScpO1xuXHRcdFx0fVxuXHRcdFx0Ly8gUmUtaW5pdCBmb3JtIHNvIFNlbWFudGljIFVJJ3MgYGZvcm0oJ2dldCB2YWx1ZXMnKWAgcGlja3MgdXAgdGhlIGR5bmFtaWMgaW5wdXQuXG5cdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uLiRmb3JtT2JqLmZvcm0oKTtcblx0XHRcdEZvcm0uZGF0YUNoYW5nZWQoKTtcblx0XHR9KTtcblxuXHRcdCRib2R5Lm9uKCdjbGljaycsICcuc2hvdy10ZW1wbGF0ZS1vcHRpb25zJywgZnVuY3Rpb24gaGFuZGxlRWRpdFRlbXBsYXRlKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGlkID0gJCh0aGlzKS5jbG9zZXN0KCd0cicpLmF0dHIoJ2lkJyk7XG5cdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uLnNob3dUZW1wbGF0ZU9wdGlvbnMoaWQpO1xuXHRcdH0pO1xuXHR9LFxuXG5cdGluaXRJbnB1dEVsZW1lbnRzKCkge1xuXHRcdCQoJy5tZW51IC5pdGVtJykudGFiKCk7XG5cdFx0JCgnZGl2LmRyb3Bkb3duJykuZHJvcGRvd24oKTtcblx0XHQkKCdpbnB1dCwgdGV4dGFyZWEnKS5jaGFuZ2UoZnVuY3Rpb24gc3luY1ZhbHVlQXR0cmlidXRlKCkge1xuXHRcdFx0JCh0aGlzKS5hdHRyKCd2YWx1ZScsICQodGhpcykudmFsKCkpO1xuXHRcdH0pO1xuXHR9LFxuXG5cdC8qKlxuXHQgKiBOb3RpZmllcyB0aGUgYmFja2VuZCB0aGF0IG1vZHVsZS1sZXZlbCBzZXR0aW5ncyBjaGFuZ2VkIHNvIHRoZSB3b3JrZXIgY2FuIHJlbG9hZC5cblx0ICovXG5cdGFwcGx5Q29uZmlndXJhdGlvbkNoYW5nZXMoKSB7XG5cdFx0JC5hcGkoe1xuXHRcdFx0dXJsOiBgJHtDb25maWcucGJ4VXJsfS9wYnhjb3JlL2FwaS9tb2R1bGVzL01vZHVsZUF1dG9wcm92aXNpb24vcmVsb2FkYCxcblx0XHRcdG9uOiAnbm93Jyxcblx0XHRcdHN1Y2Nlc3NUZXN0KHJlc3BvbnNlKSB7XG5cdFx0XHRcdHJldHVybiBPYmplY3Qua2V5cyhyZXNwb25zZSkubGVuZ3RoID4gMCAmJiByZXNwb25zZS5yZXN1bHQgPT09IHRydWU7XG5cdFx0XHR9LFxuXHRcdH0pO1xuXHR9LFxuXG5cdGNiQmVmb3JlU2VuZEZvcm0oc2V0dGluZ3MpIHtcblx0XHRjb25zdCByZXN1bHQgPSBzZXR0aW5ncztcblx0XHRyZXN1bHQuZGF0YSA9IG1vZHVsZUF1dG9wcm92aXNpb24uJGZvcm1PYmouZm9ybSgnZ2V0IHZhbHVlcycpO1xuXHRcdHJldHVybiByZXN1bHQ7XG5cdH0sXG5cblx0Y2JBZnRlclNlbmRGb3JtKHJlc3BvbnNlKSB7XG5cdFx0Ly8gRm9ybS5qcyBjYWxscyB0aGlzIG9uIGJvdGggc3VjY2VzcyBhbmQgZmFpbHVyZSBwYXRocy4gT24gZmFpbHVyZSB0aGUgY29udHJvbGxlclxuXHRcdC8vIHJvbGxlZCB0aGUgdHJhbnNhY3Rpb24gYmFjaywgc28gbGVhdmUgdGhlIG1hcmtlZC1mb3ItZGVsZXRlIHJvd3MgdmlzaWJsZSDigJQgcmVtb3Zpbmdcblx0XHQvLyB0aGVtIHdvdWxkIGxpZSBhYm91dCB0aGUgREIgc3RhdGUuXG5cdFx0aWYgKHJlc3BvbnNlLnN1Y2Nlc3MgPT09IHRydWUpIHtcblx0XHRcdCQoJ3RyLm1hcmtlZC1mb3ItZGVsZXRlJykucmVtb3ZlKCk7XG5cdFx0fVxuXG5cdFx0Ly8gUmUtYmluZCBmcmVzaGx5IGluc2VydGVkIHJvd3MgZnJvbSBtb2NrIGlkcyB0byByZWFsIGRhdGFiYXNlIGlkcy5cblx0XHRPYmplY3QuZW50cmllcyhyZXNwb25zZS5yZXN1bHRTYXZlVGFibGVzIHx8IHt9KS5mb3JFYWNoKChbdGFibGUsIG1hcHBpbmddKSA9PiB7XG5cdFx0XHRPYmplY3QuZW50cmllcyhtYXBwaW5nKS5mb3JFYWNoKChbb2xkSWQsIG5ld0lkXSkgPT4ge1xuXHRcdFx0XHRjb25zdCAkc3luY1RyID0gJChgIyR7dGFibGV9IHRyIyR7b2xkSWR9YCk7XG5cdFx0XHRcdGNvbnN0IGh0bWwgPSAkc3luY1RyLmh0bWwoKTtcblx0XHRcdFx0aWYgKGh0bWwpIHtcblx0XHRcdFx0XHQkc3luY1RyLmh0bWwoaHRtbC5yZXBsYWNlKG5ldyBSZWdFeHAob2xkSWQsICdnJyksIG5ld0lkKSk7XG5cdFx0XHRcdH1cblx0XHRcdFx0JHN5bmNUci5hdHRyKCdpZCcsIG5ld0lkKTtcblx0XHRcdFx0JChgLnVpLm1vZGFsW2RhdGEtaWQ9XCIke29sZElkfVwiXVtkYXRhLWlkLXRhYmxlPVwiJHt0YWJsZX1cIl1gKS5hdHRyKCdkYXRhLWlkJywgbmV3SWQpO1xuXHRcdFx0fSk7XG5cdFx0fSk7XG5cblx0XHQvLyBSZWJ1aWxkIGV2ZXJ5IHRlbXBsYXRlLWRyb3Bkb3duJ3Mgb3B0aW9uIGxpc3QgZnJvbSB0aGUgY3VycmVudCBzdGF0ZSBvZiAjdGVtcGxhdGVzLlxuXHRcdGNvbnN0IHRlbXBsYXRlcyA9ICQoJyN0ZW1wbGF0ZXMgdGRbZGF0YS1sYWJlbD1cIm5hbWVcIl0nKS5tYXAoZnVuY3Rpb24gYnVpbGRFbnRyeSgpIHtcblx0XHRcdHJldHVybiB7XG5cdFx0XHRcdGlkOiAkKHRoaXMpLnBhcmVudCgpLmF0dHIoJ2lkJyksXG5cdFx0XHRcdG5hbWU6ICQodGhpcykuZmluZCgnaW5wdXQnKS52YWwoKSxcblx0XHRcdH07XG5cdFx0fSkuZ2V0KCk7XG5cdFx0JCgndGRbZGF0YS1sYWJlbD1cInRlbXBsYXRlXCJdIGRpdi5zY3JvbGxpbmcubWVudScpLmVhY2goZnVuY3Rpb24gcmVidWlsZCgpIHtcblx0XHRcdGNvbnN0ICRtZW51ID0gJCh0aGlzKS5lbXB0eSgpO1xuXHRcdFx0dGVtcGxhdGVzLmZvckVhY2goKHsgaWQsIG5hbWUgfSkgPT4ge1xuXHRcdFx0XHQkbWVudS5hcHBlbmQoJCgnPGRpdj4nKS5hdHRyKCdkYXRhLXZhbHVlJywgaWQpLmF0dHIoJ2NsYXNzJywgJ2l0ZW0nKS50ZXh0KG5hbWUpKTtcblx0XHRcdH0pO1xuXHRcdH0pO1xuXG5cdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbi5pbml0SW5wdXRFbGVtZW50cygpO1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb24uYXBwbHlDb25maWd1cmF0aW9uQ2hhbmdlcygpO1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb24uJGZvcm1PYmouZm9ybSgpO1xuXHRcdEZvcm0uc2V0RXZlbnRzKCk7XG5cdH0sXG5cblx0aW5pdGlhbGl6ZUZvcm0oKSB7XG5cdFx0Rm9ybS4kZm9ybU9iaiA9IG1vZHVsZUF1dG9wcm92aXNpb24uJGZvcm1PYmo7XG5cdFx0Rm9ybS51cmwgPSBgJHtnbG9iYWxSb290VXJsfSR7bW9kdWxlQXV0b3Byb3Zpc2lvbi5zYXZlVXJsfWA7XG5cdFx0Rm9ybS5jYkJlZm9yZVNlbmRGb3JtID0gbW9kdWxlQXV0b3Byb3Zpc2lvbi5jYkJlZm9yZVNlbmRGb3JtO1xuXHRcdEZvcm0uY2JBZnRlclNlbmRGb3JtID0gbW9kdWxlQXV0b3Byb3Zpc2lvbi5jYkFmdGVyU2VuZEZvcm07XG5cdFx0Rm9ybS5pbml0aWFsaXplKCk7XG5cdH0sXG5cblx0c2hvd1RlbXBsYXRlT3B0aW9ucyhpZCkge1xuXHRcdCQoYC51aS5tb2RhbFtkYXRhLWlkPVwiJHtpZH1cIl1bZGF0YS1pZC10YWJsZT1cInRlbXBsYXRlc1wiXWApLm1vZGFsKHtcblx0XHRcdGNsb3NhYmxlOiB0cnVlLFxuXHRcdFx0b25BcHByb3ZlKCkge1xuXHRcdFx0XHRjb25zdCB2YWx1ZSA9ICQodGhpcykuZmluZCgndGV4dGFyZWEnKS52YWwoKTtcblx0XHRcdFx0JChgdGV4dGFyZWFbbmFtZT1cInRlbXBsYXRlc1skeyQodGhpcykuYXR0cignZGF0YS1pZCcpfV1bdGVtcGxhdGVdXCJdYCkudmFsKHZhbHVlKTtcblx0XHRcdFx0Rm9ybS5jaGVja1ZhbHVlcygpO1xuXHRcdFx0XHRyZXR1cm4gdHJ1ZTtcblx0XHRcdH0sXG5cdFx0fSkubW9kYWwoJ3Nob3cnKTtcblx0fSxcbn07XG5cbiQoZG9jdW1lbnQpLnJlYWR5KCgpID0+IHtcblx0bW9kdWxlQXV0b3Byb3Zpc2lvbi5pbml0aWFsaXplKCk7XG59KTtcbiJdfQ==