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

/* global globalRootUrl, Config, Form, UserMessage */
var moduleAutoprovision = {
  saveUrl: 'module-autoprovision/module-autoprovision/save',
  loadExamplesUrl: 'module-autoprovision/module-autoprovision/load-example-templates',
  $formObj: $('#module-autoprovision-form'),
  initialize: function initialize() {
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
  bindLoadExamplesButton: function bindLoadExamplesButton() {
    $('body').on('click', '#load-example-templates-button', function handleLoadExamples(e) {
      e.preventDefault();
      var $button = $(this);

      if ($button.hasClass('loading') || $button.hasClass('disabled')) {
        return;
      } // Form.$submitButton has class `disabled` while the form matches its initial
      // values; once any field changes, checkValues() removes it. Treat that as
      // dirty and warn before the post-install reload throws those edits away.


      var formIsDirty = Form.$submitButton && !Form.$submitButton.hasClass('disabled'); // eslint-disable-next-line no-alert

      if (formIsDirty && !window.confirm($button.data('unsaved-msg'))) {
        return;
      }

      var failedHeader = $button.data('failed-msg');
      var alreadyMsg = $button.data('already-msg');
      var partialHeader = $button.data('partial-msg');
      $button.addClass('loading disabled');
      $.ajax({
        url: "".concat(globalRootUrl).concat(moduleAutoprovision.loadExamplesUrl),
        type: 'POST',
        dataType: 'json'
      }).done(function (response) {
        var _ref = response !== null && response !== void 0 ? response : {},
            _ref$installed = _ref.installed,
            installed = _ref$installed === void 0 ? [] : _ref$installed,
            _ref$failed = _ref.failed,
            failed = _ref$failed === void 0 ? [] : _ref$failed;

        if (failed.length > 0) {
          $button.removeClass('loading disabled');
          var lines = ["Failed: ".concat(failed.join(', '))];

          if (installed.length > 0) {
            lines.unshift("Installed: ".concat(installed.join(', ')));
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
      }).fail(function (jqXHR) {
        $button.removeClass('loading disabled');
        var detail = "HTTP ".concat(jqXHR.status).concat(jqXHR.statusText ? ": ".concat(jqXHR.statusText) : '');
        UserMessage.showError(detail, failedHeader);
      });
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


    Object.entries(response.resultSaveTables || {}).forEach(function (_ref2) {
      var _ref3 = _slicedToArray(_ref2, 2),
          table = _ref3[0],
          mapping = _ref3[1];

      Object.entries(mapping).forEach(function (_ref4) {
        var _ref5 = _slicedToArray(_ref4, 2),
            oldId = _ref5[0],
            newId = _ref5[1];

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
      templates.forEach(function (_ref6) {
        var id = _ref6.id,
            name = _ref6.name;
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbInNyYy9tb2R1bGUtYXV0b3Byb3Zpc2lvbi5qcyJdLCJuYW1lcyI6WyJtb2R1bGVBdXRvcHJvdmlzaW9uIiwic2F2ZVVybCIsImxvYWRFeGFtcGxlc1VybCIsIiRmb3JtT2JqIiwiJCIsImluaXRpYWxpemUiLCJpbml0aWFsaXplRm9ybSIsImluaXRJbnB1dEVsZW1lbnRzIiwiYmluZEFkZFJvd0J1dHRvbnMiLCJiaW5kUm93QWN0aW9ucyIsImJpbmRMb2FkRXhhbXBsZXNCdXR0b24iLCJvbiIsImhhbmRsZUFkZFJvdyIsImJ1dHRvbklkIiwiYXR0ciIsInRhYmxlS2V5Iiwic2xpY2UiLCJsZW5ndGgiLCIkdGFibGUiLCJuZXdJZCIsIkRhdGUiLCJub3ciLCIkbmV3Um93IiwidGVtcGxhdGVIdG1sIiwiZmluZCIsImh0bWwiLCJyZXBsYWNlIiwiYXBwZW5kIiwiZm9ybSIsIkZvcm0iLCJzZXRFdmVudHMiLCIkYm9keSIsImhhbmRsZVJlbW92ZSIsImUiLCJwcmV2ZW50RGVmYXVsdCIsImhhc0NsYXNzIiwiJHJvdyIsImNsb3Nlc3QiLCJyb3dJZCIsInN0YXJ0c1dpdGgiLCJyZW1vdmUiLCJkYXRhQ2hhbmdlZCIsImRlbGV0ZUlucHV0TmFtZSIsIiRleGlzdGluZyIsInJlbW92ZUNsYXNzIiwidHlwZSIsIm5hbWUiLCJ2YWx1ZSIsImFwcGVuZFRvIiwiYWRkQ2xhc3MiLCJoYW5kbGVFZGl0VGVtcGxhdGUiLCJpZCIsInNob3dUZW1wbGF0ZU9wdGlvbnMiLCJoYW5kbGVMb2FkRXhhbXBsZXMiLCIkYnV0dG9uIiwiZm9ybUlzRGlydHkiLCIkc3VibWl0QnV0dG9uIiwid2luZG93IiwiY29uZmlybSIsImRhdGEiLCJmYWlsZWRIZWFkZXIiLCJhbHJlYWR5TXNnIiwicGFydGlhbEhlYWRlciIsImFqYXgiLCJ1cmwiLCJnbG9iYWxSb290VXJsIiwiZGF0YVR5cGUiLCJkb25lIiwicmVzcG9uc2UiLCJpbnN0YWxsZWQiLCJmYWlsZWQiLCJsaW5lcyIsImpvaW4iLCJ1bnNoaWZ0IiwiVXNlck1lc3NhZ2UiLCJzaG93RXJyb3IiLCJsb2NhdGlvbiIsInJlbG9hZCIsInNob3dJbmZvcm1hdGlvbiIsImZhaWwiLCJqcVhIUiIsImRldGFpbCIsInN0YXR1cyIsInN0YXR1c1RleHQiLCJ0YWIiLCJkcm9wZG93biIsImNoYW5nZSIsInN5bmNWYWx1ZUF0dHJpYnV0ZSIsInZhbCIsImFwcGx5Q29uZmlndXJhdGlvbkNoYW5nZXMiLCJhcGkiLCJDb25maWciLCJwYnhVcmwiLCJzdWNjZXNzVGVzdCIsIk9iamVjdCIsImtleXMiLCJyZXN1bHQiLCJjYkJlZm9yZVNlbmRGb3JtIiwic2V0dGluZ3MiLCJjYkFmdGVyU2VuZEZvcm0iLCJzdWNjZXNzIiwiZW50cmllcyIsInJlc3VsdFNhdmVUYWJsZXMiLCJmb3JFYWNoIiwidGFibGUiLCJtYXBwaW5nIiwib2xkSWQiLCIkc3luY1RyIiwiUmVnRXhwIiwidGVtcGxhdGVzIiwibWFwIiwiYnVpbGRFbnRyeSIsInBhcmVudCIsImdldCIsImVhY2giLCJyZWJ1aWxkIiwiJG1lbnUiLCJlbXB0eSIsInRleHQiLCJtb2RhbCIsImNsb3NhYmxlIiwib25BcHByb3ZlIiwiY2hlY2tWYWx1ZXMiLCJkb2N1bWVudCIsInJlYWR5Il0sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7OztBQUFBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFFQSxJQUFNQSxtQkFBbUIsR0FBRztBQUMzQkMsRUFBQUEsT0FBTyxFQUFFLGdEQURrQjtBQUUzQkMsRUFBQUEsZUFBZSxFQUFFLGtFQUZVO0FBRzNCQyxFQUFBQSxRQUFRLEVBQUVDLENBQUMsQ0FBQyw0QkFBRCxDQUhnQjtBQUszQkMsRUFBQUEsVUFMMkIsd0JBS2Q7QUFDWkwsSUFBQUEsbUJBQW1CLENBQUNNLGNBQXBCO0FBQ0FOLElBQUFBLG1CQUFtQixDQUFDTyxpQkFBcEI7QUFDQVAsSUFBQUEsbUJBQW1CLENBQUNRLGlCQUFwQjtBQUNBUixJQUFBQSxtQkFBbUIsQ0FBQ1MsY0FBcEI7QUFDQVQsSUFBQUEsbUJBQW1CLENBQUNVLHNCQUFwQjtBQUNBLEdBWDBCOztBQWEzQjtBQUNEO0FBQ0E7QUFDQTtBQUNDRixFQUFBQSxpQkFqQjJCLCtCQWlCUDtBQUNuQkosSUFBQUEsQ0FBQyxDQUFDLE1BQUQsQ0FBRCxDQUFVTyxFQUFWLENBQWEsT0FBYixFQUFzQixpQ0FBdEIsRUFBeUQsU0FBU0MsWUFBVCxHQUF3QjtBQUNoRixVQUFNQyxRQUFRLEdBQUdULENBQUMsQ0FBQyxJQUFELENBQUQsQ0FBUVUsSUFBUixDQUFhLElBQWIsQ0FBakIsQ0FEZ0YsQ0FFaEY7O0FBQ0EsVUFBSUMsUUFBUSxHQUFHRixRQUFRLENBQUNHLEtBQVQsQ0FBZSxXQUFXQyxNQUExQixFQUFrQyxDQUFFLFVBQVVBLE1BQTlDLENBQWYsQ0FIZ0YsQ0FJaEY7O0FBQ0EsVUFBSUYsUUFBUSxLQUFLLFVBQWpCLEVBQTZCO0FBQzVCQSxRQUFBQSxRQUFRLEdBQUcsV0FBWDtBQUNBOztBQUNELFVBQU1HLE1BQU0sR0FBR2QsQ0FBQyxZQUFLVyxRQUFMLEVBQWhCOztBQUNBLFVBQUlHLE1BQU0sQ0FBQ0QsTUFBUCxLQUFrQixDQUF0QixFQUF5QjtBQUN4QjtBQUNBOztBQUNELFVBQU1FLEtBQUssa0JBQVdDLElBQUksQ0FBQ0MsR0FBTCxFQUFYLENBQVg7QUFDQSxVQUFNQyxPQUFPLEdBQUdsQixDQUFDLENBQUMsTUFBRCxDQUFELENBQVVVLElBQVYsQ0FBZSxJQUFmLEVBQXFCSyxLQUFyQixDQUFoQjtBQUNBLFVBQU1JLFlBQVksR0FBR0wsTUFBTSxDQUFDTSxJQUFQLENBQVksbUJBQVosRUFBaUNDLElBQWpDLEVBQXJCO0FBQ0FILE1BQUFBLE9BQU8sQ0FBQ0csSUFBUixDQUFhRixZQUFZLENBQUNHLE9BQWIsQ0FBcUIsbUJBQXJCLEVBQTBDUCxLQUExQyxDQUFiO0FBQ0FELE1BQUFBLE1BQU0sQ0FBQ00sSUFBUCxDQUFZLE9BQVosRUFBcUJHLE1BQXJCLENBQTRCTCxPQUE1QjtBQUNBdEIsTUFBQUEsbUJBQW1CLENBQUNPLGlCQUFwQjtBQUNBUCxNQUFBQSxtQkFBbUIsQ0FBQ0csUUFBcEIsQ0FBNkJ5QixJQUE3QjtBQUNBQyxNQUFBQSxJQUFJLENBQUNDLFNBQUw7QUFDQSxLQXBCRDtBQXFCQSxHQXZDMEI7O0FBeUMzQjtBQUNEO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNDckIsRUFBQUEsY0FoRDJCLDRCQWdEVjtBQUNoQixRQUFNc0IsS0FBSyxHQUFHM0IsQ0FBQyxDQUFDLE1BQUQsQ0FBZjtBQUNBMkIsSUFBQUEsS0FBSyxDQUFDcEIsRUFBTixDQUFTLE9BQVQsRUFBa0IsYUFBbEIsRUFBaUMsU0FBU3FCLFlBQVQsQ0FBc0JDLENBQXRCLEVBQXlCO0FBQ3pEQSxNQUFBQSxDQUFDLENBQUNDLGNBQUY7O0FBQ0EsVUFBSSxDQUFDOUIsQ0FBQyxDQUFDLElBQUQsQ0FBRCxDQUFRb0IsSUFBUixDQUFhLEdBQWIsRUFBa0JXLFFBQWxCLENBQTJCLE9BQTNCLENBQUwsRUFBMEM7QUFDekM7QUFDQTs7QUFDRCxVQUFNQyxJQUFJLEdBQUdoQyxDQUFDLENBQUMsSUFBRCxDQUFELENBQVFpQyxPQUFSLENBQWdCLElBQWhCLENBQWI7QUFDQSxVQUFNbkIsTUFBTSxHQUFHa0IsSUFBSSxDQUFDQyxPQUFMLENBQWEsT0FBYixDQUFmO0FBQ0EsVUFBTXRCLFFBQVEsR0FBR0csTUFBTSxDQUFDSixJQUFQLENBQVksZ0JBQVosQ0FBakI7QUFDQSxVQUFNd0IsS0FBSyxHQUFHRixJQUFJLENBQUN0QixJQUFMLENBQVUsSUFBVixDQUFkOztBQUNBLFVBQUksQ0FBQ0MsUUFBRCxJQUFhLENBQUN1QixLQUFsQixFQUF5QjtBQUN4QjtBQUNBLE9BWHdELENBYXpEOzs7QUFDQSxVQUFJQSxLQUFLLENBQUNDLFVBQU4sQ0FBaUIsT0FBakIsQ0FBSixFQUErQjtBQUM5QkgsUUFBQUEsSUFBSSxDQUFDSSxNQUFMO0FBQ0FYLFFBQUFBLElBQUksQ0FBQ1ksV0FBTDtBQUNBO0FBQ0EsT0FsQndELENBb0J6RDs7O0FBQ0EsVUFBTUMsZUFBZSxhQUFNM0IsUUFBTixjQUFrQnVCLEtBQWxCLGdCQUFyQjtBQUNBLFVBQU1LLFNBQVMsR0FBR1AsSUFBSSxDQUFDWixJQUFMLHdCQUF5QmtCLGVBQXpCLFNBQWxCOztBQUNBLFVBQUlDLFNBQVMsQ0FBQzFCLE1BQWQsRUFBc0I7QUFDckIwQixRQUFBQSxTQUFTLENBQUNILE1BQVY7QUFDQUosUUFBQUEsSUFBSSxDQUFDUSxXQUFMLENBQWlCLG1CQUFqQjtBQUNBLE9BSEQsTUFHTztBQUNOeEMsUUFBQUEsQ0FBQyxDQUFDLFNBQUQsRUFBWTtBQUFFeUMsVUFBQUEsSUFBSSxFQUFFLFFBQVI7QUFBa0JDLFVBQUFBLElBQUksRUFBRUosZUFBeEI7QUFBeUNLLFVBQUFBLEtBQUssRUFBRTtBQUFoRCxTQUFaLENBQUQsQ0FBb0VDLFFBQXBFLENBQTZFWixJQUE3RTtBQUNBQSxRQUFBQSxJQUFJLENBQUNhLFFBQUwsQ0FBYyxtQkFBZDtBQUNBLE9BN0J3RCxDQThCekQ7OztBQUNBakQsTUFBQUEsbUJBQW1CLENBQUNHLFFBQXBCLENBQTZCeUIsSUFBN0I7QUFDQUMsTUFBQUEsSUFBSSxDQUFDWSxXQUFMO0FBQ0EsS0FqQ0Q7QUFtQ0FWLElBQUFBLEtBQUssQ0FBQ3BCLEVBQU4sQ0FBUyxPQUFULEVBQWtCLHdCQUFsQixFQUE0QyxTQUFTdUMsa0JBQVQsQ0FBNEJqQixDQUE1QixFQUErQjtBQUMxRUEsTUFBQUEsQ0FBQyxDQUFDQyxjQUFGO0FBQ0EsVUFBTWlCLEVBQUUsR0FBRy9DLENBQUMsQ0FBQyxJQUFELENBQUQsQ0FBUWlDLE9BQVIsQ0FBZ0IsSUFBaEIsRUFBc0J2QixJQUF0QixDQUEyQixJQUEzQixDQUFYO0FBQ0FkLE1BQUFBLG1CQUFtQixDQUFDb0QsbUJBQXBCLENBQXdDRCxFQUF4QztBQUNBLEtBSkQ7QUFLQSxHQTFGMEI7O0FBNEYzQjtBQUNEO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNDekMsRUFBQUEsc0JBdEcyQixvQ0FzR0Y7QUFDeEJOLElBQUFBLENBQUMsQ0FBQyxNQUFELENBQUQsQ0FBVU8sRUFBVixDQUFhLE9BQWIsRUFBc0IsZ0NBQXRCLEVBQXdELFNBQVMwQyxrQkFBVCxDQUE0QnBCLENBQTVCLEVBQStCO0FBQ3RGQSxNQUFBQSxDQUFDLENBQUNDLGNBQUY7QUFDQSxVQUFNb0IsT0FBTyxHQUFHbEQsQ0FBQyxDQUFDLElBQUQsQ0FBakI7O0FBQ0EsVUFBSWtELE9BQU8sQ0FBQ25CLFFBQVIsQ0FBaUIsU0FBakIsS0FBK0JtQixPQUFPLENBQUNuQixRQUFSLENBQWlCLFVBQWpCLENBQW5DLEVBQWlFO0FBQ2hFO0FBQ0EsT0FMcUYsQ0FPdEY7QUFDQTtBQUNBOzs7QUFDQSxVQUFNb0IsV0FBVyxHQUFHMUIsSUFBSSxDQUFDMkIsYUFBTCxJQUFzQixDQUFDM0IsSUFBSSxDQUFDMkIsYUFBTCxDQUFtQnJCLFFBQW5CLENBQTRCLFVBQTVCLENBQTNDLENBVnNGLENBV3RGOztBQUNBLFVBQUlvQixXQUFXLElBQUksQ0FBQ0UsTUFBTSxDQUFDQyxPQUFQLENBQWVKLE9BQU8sQ0FBQ0ssSUFBUixDQUFhLGFBQWIsQ0FBZixDQUFwQixFQUFpRTtBQUNoRTtBQUNBOztBQUVELFVBQU1DLFlBQVksR0FBR04sT0FBTyxDQUFDSyxJQUFSLENBQWEsWUFBYixDQUFyQjtBQUNBLFVBQU1FLFVBQVUsR0FBR1AsT0FBTyxDQUFDSyxJQUFSLENBQWEsYUFBYixDQUFuQjtBQUNBLFVBQU1HLGFBQWEsR0FBR1IsT0FBTyxDQUFDSyxJQUFSLENBQWEsYUFBYixDQUF0QjtBQUNBTCxNQUFBQSxPQUFPLENBQUNMLFFBQVIsQ0FBaUIsa0JBQWpCO0FBQ0E3QyxNQUFBQSxDQUFDLENBQUMyRCxJQUFGLENBQU87QUFDTkMsUUFBQUEsR0FBRyxZQUFLQyxhQUFMLFNBQXFCakUsbUJBQW1CLENBQUNFLGVBQXpDLENBREc7QUFFTjJDLFFBQUFBLElBQUksRUFBRSxNQUZBO0FBR05xQixRQUFBQSxRQUFRLEVBQUU7QUFISixPQUFQLEVBSUdDLElBSkgsQ0FJUSxVQUFDQyxRQUFELEVBQWM7QUFDckIsbUJBQXdDQSxRQUF4QyxhQUF3Q0EsUUFBeEMsY0FBd0NBLFFBQXhDLEdBQW9ELEVBQXBEO0FBQUEsa0NBQVFDLFNBQVI7QUFBQSxZQUFRQSxTQUFSLCtCQUFvQixFQUFwQjtBQUFBLCtCQUF3QkMsTUFBeEI7QUFBQSxZQUF3QkEsTUFBeEIsNEJBQWlDLEVBQWpDOztBQUNBLFlBQUlBLE1BQU0sQ0FBQ3JELE1BQVAsR0FBZ0IsQ0FBcEIsRUFBdUI7QUFDdEJxQyxVQUFBQSxPQUFPLENBQUNWLFdBQVIsQ0FBb0Isa0JBQXBCO0FBQ0EsY0FBTTJCLEtBQUssR0FBRyxtQkFBWUQsTUFBTSxDQUFDRSxJQUFQLENBQVksSUFBWixDQUFaLEVBQWQ7O0FBQ0EsY0FBSUgsU0FBUyxDQUFDcEQsTUFBVixHQUFtQixDQUF2QixFQUEwQjtBQUN6QnNELFlBQUFBLEtBQUssQ0FBQ0UsT0FBTixzQkFBNEJKLFNBQVMsQ0FBQ0csSUFBVixDQUFlLElBQWYsQ0FBNUI7QUFDQTs7QUFDREUsVUFBQUEsV0FBVyxDQUFDQyxTQUFaLENBQXNCSixLQUFLLENBQUNDLElBQU4sQ0FBVyxNQUFYLENBQXRCLEVBQTBDVixhQUFhLElBQUlGLFlBQTNEO0FBQ0E7QUFDQTs7QUFDRCxZQUFJUyxTQUFTLENBQUNwRCxNQUFWLEdBQW1CLENBQXZCLEVBQTBCO0FBQ3pCd0MsVUFBQUEsTUFBTSxDQUFDbUIsUUFBUCxDQUFnQkMsTUFBaEI7QUFDQTtBQUNBOztBQUNEdkIsUUFBQUEsT0FBTyxDQUFDVixXQUFSLENBQW9CLGtCQUFwQjtBQUNBOEIsUUFBQUEsV0FBVyxDQUFDSSxlQUFaLENBQTRCakIsVUFBNUI7QUFDQSxPQXJCRCxFQXFCR2tCLElBckJILENBcUJRLFVBQUNDLEtBQUQsRUFBVztBQUNsQjFCLFFBQUFBLE9BQU8sQ0FBQ1YsV0FBUixDQUFvQixrQkFBcEI7QUFDQSxZQUFNcUMsTUFBTSxrQkFBV0QsS0FBSyxDQUFDRSxNQUFqQixTQUEwQkYsS0FBSyxDQUFDRyxVQUFOLGVBQXdCSCxLQUFLLENBQUNHLFVBQTlCLElBQTZDLEVBQXZFLENBQVo7QUFDQVQsUUFBQUEsV0FBVyxDQUFDQyxTQUFaLENBQXNCTSxNQUF0QixFQUE4QnJCLFlBQTlCO0FBQ0EsT0F6QkQ7QUEwQkEsS0E5Q0Q7QUErQ0EsR0F0SjBCO0FBd0ozQnJELEVBQUFBLGlCQXhKMkIsK0JBd0pQO0FBQ25CSCxJQUFBQSxDQUFDLENBQUMsYUFBRCxDQUFELENBQWlCZ0YsR0FBakI7QUFDQWhGLElBQUFBLENBQUMsQ0FBQyxjQUFELENBQUQsQ0FBa0JpRixRQUFsQjtBQUNBakYsSUFBQUEsQ0FBQyxDQUFDLGlCQUFELENBQUQsQ0FBcUJrRixNQUFyQixDQUE0QixTQUFTQyxrQkFBVCxHQUE4QjtBQUN6RG5GLE1BQUFBLENBQUMsQ0FBQyxJQUFELENBQUQsQ0FBUVUsSUFBUixDQUFhLE9BQWIsRUFBc0JWLENBQUMsQ0FBQyxJQUFELENBQUQsQ0FBUW9GLEdBQVIsRUFBdEI7QUFDQSxLQUZEO0FBR0EsR0E5SjBCOztBQWdLM0I7QUFDRDtBQUNBO0FBQ0NDLEVBQUFBLHlCQW5LMkIsdUNBbUtDO0FBQzNCckYsSUFBQUEsQ0FBQyxDQUFDc0YsR0FBRixDQUFNO0FBQ0wxQixNQUFBQSxHQUFHLFlBQUsyQixNQUFNLENBQUNDLE1BQVosb0RBREU7QUFFTGpGLE1BQUFBLEVBQUUsRUFBRSxLQUZDO0FBR0xrRixNQUFBQSxXQUhLLHVCQUdPekIsUUFIUCxFQUdpQjtBQUNyQixlQUFPMEIsTUFBTSxDQUFDQyxJQUFQLENBQVkzQixRQUFaLEVBQXNCbkQsTUFBdEIsR0FBK0IsQ0FBL0IsSUFBb0NtRCxRQUFRLENBQUM0QixNQUFULEtBQW9CLElBQS9EO0FBQ0E7QUFMSSxLQUFOO0FBT0EsR0EzSzBCO0FBNkszQkMsRUFBQUEsZ0JBN0syQiw0QkE2S1ZDLFFBN0tVLEVBNktBO0FBQzFCLFFBQU1GLE1BQU0sR0FBR0UsUUFBZjtBQUNBRixJQUFBQSxNQUFNLENBQUNyQyxJQUFQLEdBQWMzRCxtQkFBbUIsQ0FBQ0csUUFBcEIsQ0FBNkJ5QixJQUE3QixDQUFrQyxZQUFsQyxDQUFkO0FBQ0EsV0FBT29FLE1BQVA7QUFDQSxHQWpMMEI7QUFtTDNCRyxFQUFBQSxlQW5MMkIsMkJBbUxYL0IsUUFuTFcsRUFtTEQ7QUFDekI7QUFDQTtBQUNBO0FBQ0EsUUFBSUEsUUFBUSxDQUFDZ0MsT0FBVCxLQUFxQixJQUF6QixFQUErQjtBQUM5QmhHLE1BQUFBLENBQUMsQ0FBQyxzQkFBRCxDQUFELENBQTBCb0MsTUFBMUI7QUFDQSxLQU53QixDQVF6Qjs7O0FBQ0FzRCxJQUFBQSxNQUFNLENBQUNPLE9BQVAsQ0FBZWpDLFFBQVEsQ0FBQ2tDLGdCQUFULElBQTZCLEVBQTVDLEVBQWdEQyxPQUFoRCxDQUF3RCxpQkFBc0I7QUFBQTtBQUFBLFVBQXBCQyxLQUFvQjtBQUFBLFVBQWJDLE9BQWE7O0FBQzdFWCxNQUFBQSxNQUFNLENBQUNPLE9BQVAsQ0FBZUksT0FBZixFQUF3QkYsT0FBeEIsQ0FBZ0MsaUJBQW9CO0FBQUE7QUFBQSxZQUFsQkcsS0FBa0I7QUFBQSxZQUFYdkYsS0FBVzs7QUFDbkQsWUFBTXdGLE9BQU8sR0FBR3ZHLENBQUMsWUFBS29HLEtBQUwsaUJBQWlCRSxLQUFqQixFQUFqQjtBQUNBLFlBQU1qRixJQUFJLEdBQUdrRixPQUFPLENBQUNsRixJQUFSLEVBQWI7O0FBQ0EsWUFBSUEsSUFBSixFQUFVO0FBQ1RrRixVQUFBQSxPQUFPLENBQUNsRixJQUFSLENBQWFBLElBQUksQ0FBQ0MsT0FBTCxDQUFhLElBQUlrRixNQUFKLENBQVdGLEtBQVgsRUFBa0IsR0FBbEIsQ0FBYixFQUFxQ3ZGLEtBQXJDLENBQWI7QUFDQTs7QUFDRHdGLFFBQUFBLE9BQU8sQ0FBQzdGLElBQVIsQ0FBYSxJQUFiLEVBQW1CSyxLQUFuQjtBQUNBZixRQUFBQSxDQUFDLCtCQUF1QnNHLEtBQXZCLGlDQUFpREYsS0FBakQsU0FBRCxDQUE2RDFGLElBQTdELENBQWtFLFNBQWxFLEVBQTZFSyxLQUE3RTtBQUNBLE9BUkQ7QUFTQSxLQVZELEVBVHlCLENBcUJ6Qjs7QUFDQSxRQUFNMEYsU0FBUyxHQUFHekcsQ0FBQyxDQUFDLGtDQUFELENBQUQsQ0FBc0MwRyxHQUF0QyxDQUEwQyxTQUFTQyxVQUFULEdBQXNCO0FBQ2pGLGFBQU87QUFDTjVELFFBQUFBLEVBQUUsRUFBRS9DLENBQUMsQ0FBQyxJQUFELENBQUQsQ0FBUTRHLE1BQVIsR0FBaUJsRyxJQUFqQixDQUFzQixJQUF0QixDQURFO0FBRU5nQyxRQUFBQSxJQUFJLEVBQUUxQyxDQUFDLENBQUMsSUFBRCxDQUFELENBQVFvQixJQUFSLENBQWEsT0FBYixFQUFzQmdFLEdBQXRCO0FBRkEsT0FBUDtBQUlBLEtBTGlCLEVBS2Z5QixHQUxlLEVBQWxCO0FBTUE3RyxJQUFBQSxDQUFDLENBQUMsOENBQUQsQ0FBRCxDQUFrRDhHLElBQWxELENBQXVELFNBQVNDLE9BQVQsR0FBbUI7QUFDekUsVUFBTUMsS0FBSyxHQUFHaEgsQ0FBQyxDQUFDLElBQUQsQ0FBRCxDQUFRaUgsS0FBUixFQUFkO0FBQ0FSLE1BQUFBLFNBQVMsQ0FBQ04sT0FBVixDQUFrQixpQkFBa0I7QUFBQSxZQUFmcEQsRUFBZSxTQUFmQSxFQUFlO0FBQUEsWUFBWEwsSUFBVyxTQUFYQSxJQUFXO0FBQ25Dc0UsUUFBQUEsS0FBSyxDQUFDekYsTUFBTixDQUFhdkIsQ0FBQyxDQUFDLE9BQUQsQ0FBRCxDQUFXVSxJQUFYLENBQWdCLFlBQWhCLEVBQThCcUMsRUFBOUIsRUFBa0NyQyxJQUFsQyxDQUF1QyxPQUF2QyxFQUFnRCxNQUFoRCxFQUF3RHdHLElBQXhELENBQTZEeEUsSUFBN0QsQ0FBYjtBQUNBLE9BRkQ7QUFHQSxLQUxEO0FBT0E5QyxJQUFBQSxtQkFBbUIsQ0FBQ08saUJBQXBCO0FBQ0FQLElBQUFBLG1CQUFtQixDQUFDeUYseUJBQXBCO0FBQ0F6RixJQUFBQSxtQkFBbUIsQ0FBQ0csUUFBcEIsQ0FBNkJ5QixJQUE3QjtBQUNBQyxJQUFBQSxJQUFJLENBQUNDLFNBQUw7QUFDQSxHQTFOMEI7QUE0TjNCeEIsRUFBQUEsY0E1TjJCLDRCQTROVjtBQUNoQnVCLElBQUFBLElBQUksQ0FBQzFCLFFBQUwsR0FBZ0JILG1CQUFtQixDQUFDRyxRQUFwQztBQUNBMEIsSUFBQUEsSUFBSSxDQUFDbUMsR0FBTCxhQUFjQyxhQUFkLFNBQThCakUsbUJBQW1CLENBQUNDLE9BQWxEO0FBQ0E0QixJQUFBQSxJQUFJLENBQUNvRSxnQkFBTCxHQUF3QmpHLG1CQUFtQixDQUFDaUcsZ0JBQTVDO0FBQ0FwRSxJQUFBQSxJQUFJLENBQUNzRSxlQUFMLEdBQXVCbkcsbUJBQW1CLENBQUNtRyxlQUEzQztBQUNBdEUsSUFBQUEsSUFBSSxDQUFDeEIsVUFBTDtBQUNBLEdBbE8wQjtBQW9PM0IrQyxFQUFBQSxtQkFwTzJCLCtCQW9PUEQsRUFwT08sRUFvT0g7QUFDdkIvQyxJQUFBQSxDQUFDLCtCQUF1QitDLEVBQXZCLHNDQUFELENBQTJEb0UsS0FBM0QsQ0FBaUU7QUFDaEVDLE1BQUFBLFFBQVEsRUFBRSxJQURzRDtBQUVoRUMsTUFBQUEsU0FGZ0UsdUJBRXBEO0FBQ1gsWUFBTTFFLEtBQUssR0FBRzNDLENBQUMsQ0FBQyxJQUFELENBQUQsQ0FBUW9CLElBQVIsQ0FBYSxVQUFiLEVBQXlCZ0UsR0FBekIsRUFBZDtBQUNBcEYsUUFBQUEsQ0FBQyxxQ0FBNkJBLENBQUMsQ0FBQyxJQUFELENBQUQsQ0FBUVUsSUFBUixDQUFhLFNBQWIsQ0FBN0Isb0JBQUQsQ0FBc0UwRSxHQUF0RSxDQUEwRXpDLEtBQTFFO0FBQ0FsQixRQUFBQSxJQUFJLENBQUM2RixXQUFMO0FBQ0EsZUFBTyxJQUFQO0FBQ0E7QUFQK0QsS0FBakUsRUFRR0gsS0FSSCxDQVFTLE1BUlQ7QUFTQTtBQTlPMEIsQ0FBNUI7QUFpUEFuSCxDQUFDLENBQUN1SCxRQUFELENBQUQsQ0FBWUMsS0FBWixDQUFrQixZQUFNO0FBQ3ZCNUgsRUFBQUEsbUJBQW1CLENBQUNLLFVBQXBCO0FBQ0EsQ0FGRCIsInNvdXJjZXNDb250ZW50IjpbIi8qXG4gKiBDb3B5cmlnaHQgwqkgTUlLTyBMTEMgLSBBbGwgUmlnaHRzIFJlc2VydmVkXG4gKiBVbmF1dGhvcml6ZWQgY29weWluZyBvZiB0aGlzIGZpbGUsIHZpYSBhbnkgbWVkaXVtIGlzIHN0cmljdGx5IHByb2hpYml0ZWRcbiAqIFByb3ByaWV0YXJ5IGFuZCBjb25maWRlbnRpYWxcbiAqL1xuXG4vKiBnbG9iYWwgZ2xvYmFsUm9vdFVybCwgQ29uZmlnLCBGb3JtLCBVc2VyTWVzc2FnZSAqL1xuXG5jb25zdCBtb2R1bGVBdXRvcHJvdmlzaW9uID0ge1xuXHRzYXZlVXJsOiAnbW9kdWxlLWF1dG9wcm92aXNpb24vbW9kdWxlLWF1dG9wcm92aXNpb24vc2F2ZScsXG5cdGxvYWRFeGFtcGxlc1VybDogJ21vZHVsZS1hdXRvcHJvdmlzaW9uL21vZHVsZS1hdXRvcHJvdmlzaW9uL2xvYWQtZXhhbXBsZS10ZW1wbGF0ZXMnLFxuXHQkZm9ybU9iajogJCgnI21vZHVsZS1hdXRvcHJvdmlzaW9uLWZvcm0nKSxcblxuXHRpbml0aWFsaXplKCkge1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb24uaW5pdGlhbGl6ZUZvcm0oKTtcblx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uLmluaXRJbnB1dEVsZW1lbnRzKCk7XG5cdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbi5iaW5kQWRkUm93QnV0dG9ucygpO1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb24uYmluZFJvd0FjdGlvbnMoKTtcblx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uLmJpbmRMb2FkRXhhbXBsZXNCdXR0b24oKTtcblx0fSxcblxuXHQvKipcblx0ICogV2lyZXMgdGhlIFwiKyBBZGRcIiBidXR0b24gb2YgZWFjaCBlZGl0YWJsZSB0YWJsZSB0byBhIHNpbmdsZSByb3ctY2xvbmVyLlxuXHQgKiBUaGUgYnV0dG9uIGlkIGVuY29kZXMgdGhlIHRhYmxlIGtleTogXCJhZGQtbmV3LXt0YWJsZUtleX0tYnV0dG9uXCIuXG5cdCAqL1xuXHRiaW5kQWRkUm93QnV0dG9ucygpIHtcblx0XHQkKCdib2R5Jykub24oJ2NsaWNrJywgJ1tpZF49XCJhZGQtbmV3LVwiXVtpZCQ9XCItYnV0dG9uXCJdJywgZnVuY3Rpb24gaGFuZGxlQWRkUm93KCkge1xuXHRcdFx0Y29uc3QgYnV0dG9uSWQgPSAkKHRoaXMpLmF0dHIoJ2lkJyk7XG5cdFx0XHQvLyBTdHJpcCBcImFkZC1uZXctXCIgcHJlZml4IGFuZCBcIi1idXR0b25cIiBzdWZmaXggdG8gZ2V0IHRoZSB0YWJsZSBrZXkuXG5cdFx0XHRsZXQgdGFibGVLZXkgPSBidXR0b25JZC5zbGljZSgnYWRkLW5ldy0nLmxlbmd0aCwgLSgnLWJ1dHRvbicubGVuZ3RoKSk7XG5cdFx0XHQvLyBTcGVjaWFsIGNhc2U6IHRoZSBcInRlbXBsYXRlc1wiIHRhYiB1c2VzIHNpbmd1bGFyIFwidGVtcGxhdGVcIiBpbiBpdHMgYnV0dG9uIGlkLlxuXHRcdFx0aWYgKHRhYmxlS2V5ID09PSAndGVtcGxhdGUnKSB7XG5cdFx0XHRcdHRhYmxlS2V5ID0gJ3RlbXBsYXRlcyc7XG5cdFx0XHR9XG5cdFx0XHRjb25zdCAkdGFibGUgPSAkKGAjJHt0YWJsZUtleX1gKTtcblx0XHRcdGlmICgkdGFibGUubGVuZ3RoID09PSAwKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblx0XHRcdGNvbnN0IG5ld0lkID0gYG5vbmVfJHtEYXRlLm5vdygpfWA7XG5cdFx0XHRjb25zdCAkbmV3Um93ID0gJCgnPHRyPicpLmF0dHIoJ2lkJywgbmV3SWQpO1xuXHRcdFx0Y29uc3QgdGVtcGxhdGVIdG1sID0gJHRhYmxlLmZpbmQoJyNlbXB0eVRlbXBsYXRlUm93JykuaHRtbCgpO1xuXHRcdFx0JG5ld1Jvdy5odG1sKHRlbXBsYXRlSHRtbC5yZXBsYWNlKC9lbXB0eVRlbXBsYXRlUm93L2csIG5ld0lkKSk7XG5cdFx0XHQkdGFibGUuZmluZCgndGJvZHknKS5hcHBlbmQoJG5ld1Jvdyk7XG5cdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uLmluaXRJbnB1dEVsZW1lbnRzKCk7XG5cdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uLiRmb3JtT2JqLmZvcm0oKTtcblx0XHRcdEZvcm0uc2V0RXZlbnRzKCk7XG5cdFx0fSk7XG5cdH0sXG5cblx0LyoqXG5cdCAqIFNpbmdsZSBoYW5kbGVyIGZvciByb3cgZGVsZXRlIGFuZCB0ZW1wbGF0ZS1lZGl0IGJ1dHRvbnMuXG5cdCAqXG5cdCAqIERlbGV0ZSBpcyBkZWZlcnJlZDogdW5zYXZlZCByb3dzIChpZCBcIm5vbmVfKlwiKSBhcmUgZHJvcHBlZCBmcm9tIHRoZSBET00sIGFuZCBwZXJzaXN0ZWRcblx0ICogcm93cyBnZXQgYSBoaWRkZW4gXCI8dGFibGU+WzxpZD5dW19fZGVsZXRlXT0xXCIgaW5wdXQgcGx1cyBhIGBtYXJrZWQtZm9yLWRlbGV0ZWAgY2xhc3MuXG5cdCAqIEEgc2Vjb25kIGNsaWNrIG9uIGEgbWFya2VkIHJvdyB1bi1tYXJrcyBpdC4gRGVsZXRpb24gdGFrZXMgZWZmZWN0IG9uIFNhdmUuXG5cdCAqL1xuXHRiaW5kUm93QWN0aW9ucygpIHtcblx0XHRjb25zdCAkYm9keSA9ICQoJ2JvZHknKTtcblx0XHQkYm9keS5vbignY2xpY2snLCAnLnJlbW92ZS1yb3cnLCBmdW5jdGlvbiBoYW5kbGVSZW1vdmUoZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0aWYgKCEkKHRoaXMpLmZpbmQoJ2knKS5oYXNDbGFzcygnY2xvc2UnKSkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cdFx0XHRjb25zdCAkcm93ID0gJCh0aGlzKS5jbG9zZXN0KCd0cicpO1xuXHRcdFx0Y29uc3QgJHRhYmxlID0gJHJvdy5jbG9zZXN0KCd0YWJsZScpO1xuXHRcdFx0Y29uc3QgdGFibGVLZXkgPSAkdGFibGUuYXR0cignZGF0YS10YWJsZS1rZXknKTtcblx0XHRcdGNvbnN0IHJvd0lkID0gJHJvdy5hdHRyKCdpZCcpO1xuXHRcdFx0aWYgKCF0YWJsZUtleSB8fCAhcm93SWQpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBVbnNhdmVkIHJvdyDigJQganVzdCBkcm9wIGl0OyBub3RoaW5nIGZvciB0aGUgc2VydmVyIHRvIGRlbGV0ZS5cblx0XHRcdGlmIChyb3dJZC5zdGFydHNXaXRoKCdub25lXycpKSB7XG5cdFx0XHRcdCRyb3cucmVtb3ZlKCk7XG5cdFx0XHRcdEZvcm0uZGF0YUNoYW5nZWQoKTtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBUb2dnbGUgZGVsZXRpb24gbWFyayBmb3IgcGVyc2lzdGVkIHJvd3MuXG5cdFx0XHRjb25zdCBkZWxldGVJbnB1dE5hbWUgPSBgJHt0YWJsZUtleX1bJHtyb3dJZH1dW19fZGVsZXRlXWA7XG5cdFx0XHRjb25zdCAkZXhpc3RpbmcgPSAkcm93LmZpbmQoYGlucHV0W25hbWU9XCIke2RlbGV0ZUlucHV0TmFtZX1cIl1gKTtcblx0XHRcdGlmICgkZXhpc3RpbmcubGVuZ3RoKSB7XG5cdFx0XHRcdCRleGlzdGluZy5yZW1vdmUoKTtcblx0XHRcdFx0JHJvdy5yZW1vdmVDbGFzcygnbWFya2VkLWZvci1kZWxldGUnKTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdCQoJzxpbnB1dD4nLCB7IHR5cGU6ICdoaWRkZW4nLCBuYW1lOiBkZWxldGVJbnB1dE5hbWUsIHZhbHVlOiAnMScgfSkuYXBwZW5kVG8oJHJvdyk7XG5cdFx0XHRcdCRyb3cuYWRkQ2xhc3MoJ21hcmtlZC1mb3ItZGVsZXRlJyk7XG5cdFx0XHR9XG5cdFx0XHQvLyBSZS1pbml0IGZvcm0gc28gU2VtYW50aWMgVUkncyBgZm9ybSgnZ2V0IHZhbHVlcycpYCBwaWNrcyB1cCB0aGUgZHluYW1pYyBpbnB1dC5cblx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb24uJGZvcm1PYmouZm9ybSgpO1xuXHRcdFx0Rm9ybS5kYXRhQ2hhbmdlZCgpO1xuXHRcdH0pO1xuXG5cdFx0JGJvZHkub24oJ2NsaWNrJywgJy5zaG93LXRlbXBsYXRlLW9wdGlvbnMnLCBmdW5jdGlvbiBoYW5kbGVFZGl0VGVtcGxhdGUoZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgaWQgPSAkKHRoaXMpLmNsb3Nlc3QoJ3RyJykuYXR0cignaWQnKTtcblx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb24uc2hvd1RlbXBsYXRlT3B0aW9ucyhpZCk7XG5cdFx0fSk7XG5cdH0sXG5cblx0LyoqXG5cdCAqIE9uZS1zaG90IGJvb3RzdHJhcCBvZiB0aGUgYnVuZGxlZCB2ZW5kb3IgZXhhbXBsZSB0ZW1wbGF0ZXMuXG5cdCAqXG5cdCAqIFRlbXBsYXRlU2VlZGVyIGlzIGlkZW1wb3RlbnQgKHNraXBzIGJ5IG5hbWUpIGFuZCBhdG9taWMgcGVyIHNlZWQgKHJvbGxzIGJhY2tcblx0ICogdGhlIHRlbXBsYXRlIHJvdyB3aGVuIGl0cyBVUkkgaW5zZXJ0IGZhaWxzKS4gVGhlIGhhbmRsZXI6XG5cdCAqICAgLSBjb25maXJtcyBhIGRpc2NhcmQgaWYgdGhlIHN1cnJvdW5kaW5nIGZvcm0gaXMgZGlydHksIHNpbmNlIGEgc3VjY2Vzc2Z1bFxuXHQgKiAgICAgaW5zdGFsbCByZWxvYWRzIHRoZSBwYWdlIGFuZCB3b3VsZCBvdGhlcndpc2UgZHJvcCB1bnNhdmVkIGFkbWluIGVkaXRzO1xuXHQgKiAgIC0gcmVsb2FkcyBvbmx5IHdoZW4gbm90aGluZyBmYWlsZWQsIHNvIHBhcnRpYWwgZmFpbHVyZXMgc3RheSBvbiBzY3JlZW5cblx0ICogICAgIGluc3RlYWQgb2YgYmVpbmcgaGlkZGVuIGJ5IHRoZSByZWxvYWQuXG5cdCAqL1xuXHRiaW5kTG9hZEV4YW1wbGVzQnV0dG9uKCkge1xuXHRcdCQoJ2JvZHknKS5vbignY2xpY2snLCAnI2xvYWQtZXhhbXBsZS10ZW1wbGF0ZXMtYnV0dG9uJywgZnVuY3Rpb24gaGFuZGxlTG9hZEV4YW1wbGVzKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0ICRidXR0b24gPSAkKHRoaXMpO1xuXHRcdFx0aWYgKCRidXR0b24uaGFzQ2xhc3MoJ2xvYWRpbmcnKSB8fCAkYnV0dG9uLmhhc0NsYXNzKCdkaXNhYmxlZCcpKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0Ly8gRm9ybS4kc3VibWl0QnV0dG9uIGhhcyBjbGFzcyBgZGlzYWJsZWRgIHdoaWxlIHRoZSBmb3JtIG1hdGNoZXMgaXRzIGluaXRpYWxcblx0XHRcdC8vIHZhbHVlczsgb25jZSBhbnkgZmllbGQgY2hhbmdlcywgY2hlY2tWYWx1ZXMoKSByZW1vdmVzIGl0LiBUcmVhdCB0aGF0IGFzXG5cdFx0XHQvLyBkaXJ0eSBhbmQgd2FybiBiZWZvcmUgdGhlIHBvc3QtaW5zdGFsbCByZWxvYWQgdGhyb3dzIHRob3NlIGVkaXRzIGF3YXkuXG5cdFx0XHRjb25zdCBmb3JtSXNEaXJ0eSA9IEZvcm0uJHN1Ym1pdEJ1dHRvbiAmJiAhRm9ybS4kc3VibWl0QnV0dG9uLmhhc0NsYXNzKCdkaXNhYmxlZCcpO1xuXHRcdFx0Ly8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIG5vLWFsZXJ0XG5cdFx0XHRpZiAoZm9ybUlzRGlydHkgJiYgIXdpbmRvdy5jb25maXJtKCRidXR0b24uZGF0YSgndW5zYXZlZC1tc2cnKSkpIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRjb25zdCBmYWlsZWRIZWFkZXIgPSAkYnV0dG9uLmRhdGEoJ2ZhaWxlZC1tc2cnKTtcblx0XHRcdGNvbnN0IGFscmVhZHlNc2cgPSAkYnV0dG9uLmRhdGEoJ2FscmVhZHktbXNnJyk7XG5cdFx0XHRjb25zdCBwYXJ0aWFsSGVhZGVyID0gJGJ1dHRvbi5kYXRhKCdwYXJ0aWFsLW1zZycpO1xuXHRcdFx0JGJ1dHRvbi5hZGRDbGFzcygnbG9hZGluZyBkaXNhYmxlZCcpO1xuXHRcdFx0JC5hamF4KHtcblx0XHRcdFx0dXJsOiBgJHtnbG9iYWxSb290VXJsfSR7bW9kdWxlQXV0b3Byb3Zpc2lvbi5sb2FkRXhhbXBsZXNVcmx9YCxcblx0XHRcdFx0dHlwZTogJ1BPU1QnLFxuXHRcdFx0XHRkYXRhVHlwZTogJ2pzb24nLFxuXHRcdFx0fSkuZG9uZSgocmVzcG9uc2UpID0+IHtcblx0XHRcdFx0Y29uc3QgeyBpbnN0YWxsZWQgPSBbXSwgZmFpbGVkID0gW10gfSA9IHJlc3BvbnNlID8/IHt9O1xuXHRcdFx0XHRpZiAoZmFpbGVkLmxlbmd0aCA+IDApIHtcblx0XHRcdFx0XHQkYnV0dG9uLnJlbW92ZUNsYXNzKCdsb2FkaW5nIGRpc2FibGVkJyk7XG5cdFx0XHRcdFx0Y29uc3QgbGluZXMgPSBbYEZhaWxlZDogJHtmYWlsZWQuam9pbignLCAnKX1gXTtcblx0XHRcdFx0XHRpZiAoaW5zdGFsbGVkLmxlbmd0aCA+IDApIHtcblx0XHRcdFx0XHRcdGxpbmVzLnVuc2hpZnQoYEluc3RhbGxlZDogJHtpbnN0YWxsZWQuam9pbignLCAnKX1gKTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0VXNlck1lc3NhZ2Uuc2hvd0Vycm9yKGxpbmVzLmpvaW4oJzxicj4nKSwgcGFydGlhbEhlYWRlciB8fCBmYWlsZWRIZWFkZXIpO1xuXHRcdFx0XHRcdHJldHVybjtcblx0XHRcdFx0fVxuXHRcdFx0XHRpZiAoaW5zdGFsbGVkLmxlbmd0aCA+IDApIHtcblx0XHRcdFx0XHR3aW5kb3cubG9jYXRpb24ucmVsb2FkKCk7XG5cdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHR9XG5cdFx0XHRcdCRidXR0b24ucmVtb3ZlQ2xhc3MoJ2xvYWRpbmcgZGlzYWJsZWQnKTtcblx0XHRcdFx0VXNlck1lc3NhZ2Uuc2hvd0luZm9ybWF0aW9uKGFscmVhZHlNc2cpO1xuXHRcdFx0fSkuZmFpbCgoanFYSFIpID0+IHtcblx0XHRcdFx0JGJ1dHRvbi5yZW1vdmVDbGFzcygnbG9hZGluZyBkaXNhYmxlZCcpO1xuXHRcdFx0XHRjb25zdCBkZXRhaWwgPSBgSFRUUCAke2pxWEhSLnN0YXR1c30ke2pxWEhSLnN0YXR1c1RleHQgPyBgOiAke2pxWEhSLnN0YXR1c1RleHR9YCA6ICcnfWA7XG5cdFx0XHRcdFVzZXJNZXNzYWdlLnNob3dFcnJvcihkZXRhaWwsIGZhaWxlZEhlYWRlcik7XG5cdFx0XHR9KTtcblx0XHR9KTtcblx0fSxcblxuXHRpbml0SW5wdXRFbGVtZW50cygpIHtcblx0XHQkKCcubWVudSAuaXRlbScpLnRhYigpO1xuXHRcdCQoJ2Rpdi5kcm9wZG93bicpLmRyb3Bkb3duKCk7XG5cdFx0JCgnaW5wdXQsIHRleHRhcmVhJykuY2hhbmdlKGZ1bmN0aW9uIHN5bmNWYWx1ZUF0dHJpYnV0ZSgpIHtcblx0XHRcdCQodGhpcykuYXR0cigndmFsdWUnLCAkKHRoaXMpLnZhbCgpKTtcblx0XHR9KTtcblx0fSxcblxuXHQvKipcblx0ICogTm90aWZpZXMgdGhlIGJhY2tlbmQgdGhhdCBtb2R1bGUtbGV2ZWwgc2V0dGluZ3MgY2hhbmdlZCBzbyB0aGUgd29ya2VyIGNhbiByZWxvYWQuXG5cdCAqL1xuXHRhcHBseUNvbmZpZ3VyYXRpb25DaGFuZ2VzKCkge1xuXHRcdCQuYXBpKHtcblx0XHRcdHVybDogYCR7Q29uZmlnLnBieFVybH0vcGJ4Y29yZS9hcGkvbW9kdWxlcy9Nb2R1bGVBdXRvcHJvdmlzaW9uL3JlbG9hZGAsXG5cdFx0XHRvbjogJ25vdycsXG5cdFx0XHRzdWNjZXNzVGVzdChyZXNwb25zZSkge1xuXHRcdFx0XHRyZXR1cm4gT2JqZWN0LmtleXMocmVzcG9uc2UpLmxlbmd0aCA+IDAgJiYgcmVzcG9uc2UucmVzdWx0ID09PSB0cnVlO1xuXHRcdFx0fSxcblx0XHR9KTtcblx0fSxcblxuXHRjYkJlZm9yZVNlbmRGb3JtKHNldHRpbmdzKSB7XG5cdFx0Y29uc3QgcmVzdWx0ID0gc2V0dGluZ3M7XG5cdFx0cmVzdWx0LmRhdGEgPSBtb2R1bGVBdXRvcHJvdmlzaW9uLiRmb3JtT2JqLmZvcm0oJ2dldCB2YWx1ZXMnKTtcblx0XHRyZXR1cm4gcmVzdWx0O1xuXHR9LFxuXG5cdGNiQWZ0ZXJTZW5kRm9ybShyZXNwb25zZSkge1xuXHRcdC8vIEZvcm0uanMgY2FsbHMgdGhpcyBvbiBib3RoIHN1Y2Nlc3MgYW5kIGZhaWx1cmUgcGF0aHMuIE9uIGZhaWx1cmUgdGhlIGNvbnRyb2xsZXJcblx0XHQvLyByb2xsZWQgdGhlIHRyYW5zYWN0aW9uIGJhY2ssIHNvIGxlYXZlIHRoZSBtYXJrZWQtZm9yLWRlbGV0ZSByb3dzIHZpc2libGUg4oCUIHJlbW92aW5nXG5cdFx0Ly8gdGhlbSB3b3VsZCBsaWUgYWJvdXQgdGhlIERCIHN0YXRlLlxuXHRcdGlmIChyZXNwb25zZS5zdWNjZXNzID09PSB0cnVlKSB7XG5cdFx0XHQkKCd0ci5tYXJrZWQtZm9yLWRlbGV0ZScpLnJlbW92ZSgpO1xuXHRcdH1cblxuXHRcdC8vIFJlLWJpbmQgZnJlc2hseSBpbnNlcnRlZCByb3dzIGZyb20gbW9jayBpZHMgdG8gcmVhbCBkYXRhYmFzZSBpZHMuXG5cdFx0T2JqZWN0LmVudHJpZXMocmVzcG9uc2UucmVzdWx0U2F2ZVRhYmxlcyB8fCB7fSkuZm9yRWFjaCgoW3RhYmxlLCBtYXBwaW5nXSkgPT4ge1xuXHRcdFx0T2JqZWN0LmVudHJpZXMobWFwcGluZykuZm9yRWFjaCgoW29sZElkLCBuZXdJZF0pID0+IHtcblx0XHRcdFx0Y29uc3QgJHN5bmNUciA9ICQoYCMke3RhYmxlfSB0ciMke29sZElkfWApO1xuXHRcdFx0XHRjb25zdCBodG1sID0gJHN5bmNUci5odG1sKCk7XG5cdFx0XHRcdGlmIChodG1sKSB7XG5cdFx0XHRcdFx0JHN5bmNUci5odG1sKGh0bWwucmVwbGFjZShuZXcgUmVnRXhwKG9sZElkLCAnZycpLCBuZXdJZCkpO1xuXHRcdFx0XHR9XG5cdFx0XHRcdCRzeW5jVHIuYXR0cignaWQnLCBuZXdJZCk7XG5cdFx0XHRcdCQoYC51aS5tb2RhbFtkYXRhLWlkPVwiJHtvbGRJZH1cIl1bZGF0YS1pZC10YWJsZT1cIiR7dGFibGV9XCJdYCkuYXR0cignZGF0YS1pZCcsIG5ld0lkKTtcblx0XHRcdH0pO1xuXHRcdH0pO1xuXG5cdFx0Ly8gUmVidWlsZCBldmVyeSB0ZW1wbGF0ZS1kcm9wZG93bidzIG9wdGlvbiBsaXN0IGZyb20gdGhlIGN1cnJlbnQgc3RhdGUgb2YgI3RlbXBsYXRlcy5cblx0XHRjb25zdCB0ZW1wbGF0ZXMgPSAkKCcjdGVtcGxhdGVzIHRkW2RhdGEtbGFiZWw9XCJuYW1lXCJdJykubWFwKGZ1bmN0aW9uIGJ1aWxkRW50cnkoKSB7XG5cdFx0XHRyZXR1cm4ge1xuXHRcdFx0XHRpZDogJCh0aGlzKS5wYXJlbnQoKS5hdHRyKCdpZCcpLFxuXHRcdFx0XHRuYW1lOiAkKHRoaXMpLmZpbmQoJ2lucHV0JykudmFsKCksXG5cdFx0XHR9O1xuXHRcdH0pLmdldCgpO1xuXHRcdCQoJ3RkW2RhdGEtbGFiZWw9XCJ0ZW1wbGF0ZVwiXSBkaXYuc2Nyb2xsaW5nLm1lbnUnKS5lYWNoKGZ1bmN0aW9uIHJlYnVpbGQoKSB7XG5cdFx0XHRjb25zdCAkbWVudSA9ICQodGhpcykuZW1wdHkoKTtcblx0XHRcdHRlbXBsYXRlcy5mb3JFYWNoKCh7IGlkLCBuYW1lIH0pID0+IHtcblx0XHRcdFx0JG1lbnUuYXBwZW5kKCQoJzxkaXY+JykuYXR0cignZGF0YS12YWx1ZScsIGlkKS5hdHRyKCdjbGFzcycsICdpdGVtJykudGV4dChuYW1lKSk7XG5cdFx0XHR9KTtcblx0XHR9KTtcblxuXHRcdG1vZHVsZUF1dG9wcm92aXNpb24uaW5pdElucHV0RWxlbWVudHMoKTtcblx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uLmFwcGx5Q29uZmlndXJhdGlvbkNoYW5nZXMoKTtcblx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uLiRmb3JtT2JqLmZvcm0oKTtcblx0XHRGb3JtLnNldEV2ZW50cygpO1xuXHR9LFxuXG5cdGluaXRpYWxpemVGb3JtKCkge1xuXHRcdEZvcm0uJGZvcm1PYmogPSBtb2R1bGVBdXRvcHJvdmlzaW9uLiRmb3JtT2JqO1xuXHRcdEZvcm0udXJsID0gYCR7Z2xvYmFsUm9vdFVybH0ke21vZHVsZUF1dG9wcm92aXNpb24uc2F2ZVVybH1gO1xuXHRcdEZvcm0uY2JCZWZvcmVTZW5kRm9ybSA9IG1vZHVsZUF1dG9wcm92aXNpb24uY2JCZWZvcmVTZW5kRm9ybTtcblx0XHRGb3JtLmNiQWZ0ZXJTZW5kRm9ybSA9IG1vZHVsZUF1dG9wcm92aXNpb24uY2JBZnRlclNlbmRGb3JtO1xuXHRcdEZvcm0uaW5pdGlhbGl6ZSgpO1xuXHR9LFxuXG5cdHNob3dUZW1wbGF0ZU9wdGlvbnMoaWQpIHtcblx0XHQkKGAudWkubW9kYWxbZGF0YS1pZD1cIiR7aWR9XCJdW2RhdGEtaWQtdGFibGU9XCJ0ZW1wbGF0ZXNcIl1gKS5tb2RhbCh7XG5cdFx0XHRjbG9zYWJsZTogdHJ1ZSxcblx0XHRcdG9uQXBwcm92ZSgpIHtcblx0XHRcdFx0Y29uc3QgdmFsdWUgPSAkKHRoaXMpLmZpbmQoJ3RleHRhcmVhJykudmFsKCk7XG5cdFx0XHRcdCQoYHRleHRhcmVhW25hbWU9XCJ0ZW1wbGF0ZXNbJHskKHRoaXMpLmF0dHIoJ2RhdGEtaWQnKX1dW3RlbXBsYXRlXVwiXWApLnZhbCh2YWx1ZSk7XG5cdFx0XHRcdEZvcm0uY2hlY2tWYWx1ZXMoKTtcblx0XHRcdFx0cmV0dXJuIHRydWU7XG5cdFx0XHR9LFxuXHRcdH0pLm1vZGFsKCdzaG93Jyk7XG5cdH0sXG59O1xuXG4kKGRvY3VtZW50KS5yZWFkeSgoKSA9PiB7XG5cdG1vZHVsZUF1dG9wcm92aXNpb24uaW5pdGlhbGl6ZSgpO1xufSk7XG4iXX0=