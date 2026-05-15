"use strict";

/*
 * Copyright © MIKO LLC - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 */

/* global globalRootUrl, Config, FilesAPI, Resumable */

/**
 * Firmware tab on the ModuleAutoprovision settings page.
 *
 * Loads the firmware list on demand and wires the drag-and-drop upload zone to
 * Core's chunked /pbxcore/api/v3/files:upload endpoint. After the chunked upload
 * lands a file_id, this module POSTs the registration metadata (vendor, model,
 * version, notes) to /pbxcore/api/v3/module-autoprovision/firmware:upload —
 * the same two-step flow as ModuleExampleRestAPIv3.
 */
var moduleAutoprovisionFirmware = {
  basePath: '/pbxcore/api/v3/module-autoprovision/firmware',
  $tab: null,
  $tableBody: null,
  $totals: null,
  $dropZone: null,
  $progress: null,
  $progressBar: null,
  $vendor: null,
  $model: null,
  $version: null,
  $notes: null,
  loaded: false,
  resumable: null,
  // Tracks per-row "replace file" uploads. Keyed by firmware row id so
  // concurrent replaces on different rows don't clobber each other's metadata.
  replaceResumables: {},
  replaceTargetId: null,
  initialize: function initialize() {
    var $tab = $('a.item[data-tab="firmware"]');

    if ($tab.length === 0) {
      return;
    }

    moduleAutoprovisionFirmware.$tab = $('.ui.tab.segment[data-tab="firmware"]');
    moduleAutoprovisionFirmware.$tableBody = $('#firmware-table tbody');
    moduleAutoprovisionFirmware.$totals = $('#firmware-totals');
    moduleAutoprovisionFirmware.$dropZone = $('#firmware-dropzone');
    moduleAutoprovisionFirmware.$progress = $('#firmware-progress');
    moduleAutoprovisionFirmware.$progressBar = $('#firmware-progress .bar');
    moduleAutoprovisionFirmware.$vendor = $('#firmware-vendor');
    moduleAutoprovisionFirmware.$model = $('#firmware-model');
    moduleAutoprovisionFirmware.$version = $('#firmware-version');
    moduleAutoprovisionFirmware.$notes = $('#firmware-notes'); // Load list when the tab is first opened — the device/template tabs are
    // the common entry points, so paying the round-trip up front would slow
    // the page for admins who never touch firmware.

    $tab.on('click', function () {
      if (!moduleAutoprovisionFirmware.loaded) {
        moduleAutoprovisionFirmware.refresh();
        moduleAutoprovisionFirmware.loaded = true;
      }
    });
    moduleAutoprovisionFirmware.initializeUpload();
    moduleAutoprovisionFirmware.bindRowActions(); // Semantic UI styling for the vendor select. .val() reads the underlying
    // <select>, so this only changes how it looks, not how we read it.

    moduleAutoprovisionFirmware.$vendor.dropdown();
  },
  refresh: function refresh() {
    $.api({
      url: moduleAutoprovisionFirmware.basePath,
      method: 'GET',
      on: 'now',
      onSuccess: function onSuccess(response) {
        var _response$data, _response$data2;

        var items = (response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.items) || [];
        var totals = (response === null || response === void 0 ? void 0 : (_response$data2 = response.data) === null || _response$data2 === void 0 ? void 0 : _response$data2.totals) || {};
        moduleAutoprovisionFirmware.renderTable(items);
        moduleAutoprovisionFirmware.renderTotals(totals);
      },
      onFailure: function onFailure(response) {
        moduleAutoprovisionFirmware.renderTable([]);
        console.error('Failed to list firmware:', response);
      }
    });
  },
  renderTable: function renderTable(items) {
    var $tbody = moduleAutoprovisionFirmware.$tableBody;
    $tbody.empty();

    if (items.length === 0) {
      $tbody.append('<tr><td colspan="7" class="center aligned">' + '<i class="info circle icon"></i> No firmware uploaded yet.' + '</td></tr>');
      return;
    }

    items.forEach(function (item) {
      var sha = (item.sha256 || '').substring(0, 12);
      var sizeMb = (item.size / (1024 * 1024)).toFixed(2);
      var $row = $('<tr>').attr('data-firmware-id', item.id).attr('data-firmware-vendor', item.vendor || '').attr('data-firmware-model', item.model || '').attr('data-firmware-version', item.version || '').attr('data-firmware-notes', item.notes || '').append($('<td>').text(item.vendor || '')).append($('<td>').text(item.model || '*')).append($('<td>').text(item.filename || '')).append($('<td>').text(item.version || '')).append($('<td>').text("".concat(sizeMb, " MB"))).append($('<td>').attr('title', item.sha256 || '').text(sha)).append($('<td class="right aligned">').append($('<a class="ui mini blue button firmware-download">').attr('href', item.url || '#').attr('target', '_blank').attr('title', 'Download').html('<i class="download icon"></i>')).append($('<a class="ui mini teal button firmware-edit">').attr('href', '#').attr('data-firmware-id', item.id).attr('title', 'Edit metadata').html('<i class="edit icon"></i>')).append($('<a class="ui mini olive button firmware-replace">').attr('href', '#').attr('data-firmware-id', item.id).attr('title', 'Replace file').html('<i class="sync icon"></i>')).append($('<a class="ui mini red button firmware-delete">').attr('href', '#').attr('data-firmware-id', item.id).attr('title', 'Delete').html('<i class="trash icon"></i>')));
      $tbody.append($row);
    });
  },
  renderTotals: function renderTotals(totals) {
    var used = totals.used || 0;
    var cap = totals.used_cap || 0;
    var fileCap = totals.file_cap || 0;
    var usedMb = (used / (1024 * 1024)).toFixed(1);
    var capMb = (cap / (1024 * 1024)).toFixed(0);
    var fileCapMb = (fileCap / (1024 * 1024)).toFixed(0);
    moduleAutoprovisionFirmware.$totals.text("".concat(usedMb, " MB / ").concat(capMb, " MB used \u2014 single file cap ").concat(fileCapMb, " MB"));
  },
  bindRowActions: function bindRowActions() {
    var $body = $('body');
    $body.on('click', '.firmware-delete', function handleDelete(e) {
      e.preventDefault();
      var id = $(this).attr('data-firmware-id');

      if (!id) {
        return;
      }

      $.api({
        url: "".concat(moduleAutoprovisionFirmware.basePath, "/").concat(id),
        method: 'DELETE',
        on: 'now',
        onSuccess: function onSuccess() {
          moduleAutoprovisionFirmware.refresh();
        },
        onFailure: function onFailure(response) {
          console.error('Failed to delete firmware:', response);
        }
      });
    });
    $body.on('click', '.firmware-edit', function handleEdit(e) {
      e.preventDefault();
      var $row = $(this).closest('tr');
      moduleAutoprovisionFirmware.openEditModal({
        id: $row.attr('data-firmware-id'),
        vendor: $row.attr('data-firmware-vendor'),
        model: $row.attr('data-firmware-model'),
        version: $row.attr('data-firmware-version'),
        notes: $row.attr('data-firmware-notes')
      });
    });
    $body.on('click', '#firmware-edit-save', function () {
      moduleAutoprovisionFirmware.submitEditModal();
    }); // "Replace file" opens a hidden file picker pointed at this row id.

    $body.on('click', '.firmware-replace', function handleReplace(e) {
      e.preventDefault();
      moduleAutoprovisionFirmware.replaceTargetId = $(this).attr('data-firmware-id');
      moduleAutoprovisionFirmware.triggerReplacePicker();
    });
  },
  openEditModal: function openEditModal(item) {
    var $modal = $('#firmware-edit-modal');
    $modal.find('#firmware-edit-id').val(item.id || '');
    $modal.find('#firmware-edit-vendor').val(item.vendor || '');
    $modal.find('#firmware-edit-model').val(item.model || '');
    $modal.find('#firmware-edit-version').val(item.version || '');
    $modal.find('#firmware-edit-notes').val(item.notes || '');
    $modal.find('#firmware-edit-vendor').dropdown('refresh');
    $modal.modal('show');
  },
  submitEditModal: function submitEditModal() {
    var $modal = $('#firmware-edit-modal');
    var id = $modal.find('#firmware-edit-id').val();

    if (!id) {
      return;
    }

    var payload = {
      vendor: $modal.find('#firmware-edit-vendor').val(),
      model: $modal.find('#firmware-edit-model').val(),
      version: $modal.find('#firmware-edit-version').val(),
      notes: $modal.find('#firmware-edit-notes').val()
    };
    $.api({
      url: "".concat(moduleAutoprovisionFirmware.basePath, "/").concat(id),
      method: 'PATCH',
      data: payload,
      on: 'now',
      onSuccess: function onSuccess(response) {
        if ((response === null || response === void 0 ? void 0 : response.result) === true) {
          $modal.modal('hide');
          moduleAutoprovisionFirmware.refresh();
        } else {
          var _response$messages, _response$messages$er;

          var err = (response === null || response === void 0 ? void 0 : (_response$messages = response.messages) === null || _response$messages === void 0 ? void 0 : (_response$messages$er = _response$messages.error) === null || _response$messages$er === void 0 ? void 0 : _response$messages$er[0]) || 'Update failed';
          $modal.find('#firmware-edit-error').text(err).show();
        }
      },
      onFailure: function onFailure(response) {
        var _response$messages2, _response$messages2$e;

        var err = (response === null || response === void 0 ? void 0 : (_response$messages2 = response.messages) === null || _response$messages2 === void 0 ? void 0 : (_response$messages2$e = _response$messages2.error) === null || _response$messages2$e === void 0 ? void 0 : _response$messages2$e[0]) || 'Update failed';
        $modal.find('#firmware-edit-error').text(err).show();
      }
    });
  },
  triggerReplacePicker: function triggerReplacePicker() {
    // Lazy-create a single hidden <input type="file"> the first time around;
    // Resumable.js attaches its own change handler when we assignBrowse() it.
    var input = document.getElementById('firmware-replace-input');

    if (!input) {
      input = document.createElement('input');
      input.type = 'file';
      input.id = 'firmware-replace-input';
      input.style.display = 'none';
      document.body.appendChild(input);
      var config = FilesAPI.configureResumable({
        target: "".concat(Config.pbxUrl, "/pbxcore/api/v3/files:upload"),
        fileType: ['rom', 'bin', 'fw', 'z'],
        maxFileSize: 80 * 1024 * 1024,
        query: function query() {
          return {
            category: 'autoprovision-firmware'
          };
        }
      });
      var resumable = new Resumable(config);

      if (!resumable.support) {
        console.error('Resumable.js not supported');
        return;
      }

      resumable.assignBrowse(input);
      FilesAPI.setupResumableEvents(resumable, function (event, data) {
        return moduleAutoprovisionFirmware.handleReplaceUploadEvent(event, data);
      }, true);
      moduleAutoprovisionFirmware.replaceResumables["default"] = resumable;
    }

    input.click();
  },
  handleReplaceUploadEvent: function handleReplaceUploadEvent(event, data) {
    switch (event) {
      case 'uploadStart':
        moduleAutoprovisionFirmware.setProgress(0, 'Uploading replacement…');
        break;

      case 'fileProgress':
        moduleAutoprovisionFirmware.setProgress(Math.floor(data.file.progress() * 100));
        break;

      case 'progress':
        moduleAutoprovisionFirmware.setProgress(Math.floor(data.percent));
        break;

      case 'fileSuccess':
        {
          var _response, _response$data3, _response2, _response2$data;

          var response;

          try {
            response = JSON.parse(data.response);
          } catch (err) {
            console.error('Replace response parse error:', err);
            return;
          }

          var fileId = ((_response = response) === null || _response === void 0 ? void 0 : (_response$data3 = _response.data) === null || _response$data3 === void 0 ? void 0 : _response$data3.upload_id) || '';
          var status = ((_response2 = response) === null || _response2 === void 0 ? void 0 : (_response2$data = _response2.data) === null || _response2$data === void 0 ? void 0 : _response2$data.d_status) || '';

          if (status === 'MERGING') {
            moduleAutoprovisionFirmware.setProgress(95, 'Merging chunks…');
            moduleAutoprovisionFirmware.waitForMerge(fileId, function () {
              return moduleAutoprovisionFirmware.callReplace(fileId);
            });
          } else {
            moduleAutoprovisionFirmware.setProgress(100, 'Replacing…');
            moduleAutoprovisionFirmware.callReplace(fileId);
          }

          break;
        }

      case 'fileError':
        moduleAutoprovisionFirmware.setProgress(0, "Replace failed: ".concat(data.message || ''));
        break;

      default:
        break;
    }
  },
  callReplace: function callReplace(fileId) {
    var id = moduleAutoprovisionFirmware.replaceTargetId;

    if (!id || !fileId) {
      moduleAutoprovisionFirmware.setProgress(0, 'Replace cancelled — missing id or file_id');
      return;
    }

    $.api({
      url: "".concat(moduleAutoprovisionFirmware.basePath, "/").concat(id),
      method: 'PUT',
      data: {
        file_id: fileId
      },
      on: 'now',
      onSuccess: function onSuccess(response) {
        if ((response === null || response === void 0 ? void 0 : response.result) === true) {
          moduleAutoprovisionFirmware.setProgress(100, 'Replaced');
          moduleAutoprovisionFirmware.refresh();
        } else {
          var _response$messages3, _response$messages3$e;

          var err = (response === null || response === void 0 ? void 0 : (_response$messages3 = response.messages) === null || _response$messages3 === void 0 ? void 0 : (_response$messages3$e = _response$messages3.error) === null || _response$messages3$e === void 0 ? void 0 : _response$messages3$e[0]) || 'Replace failed';
          moduleAutoprovisionFirmware.setProgress(0, err);
        }
      },
      onFailure: function onFailure(response) {
        var _response$messages4, _response$messages4$e;

        var err = (response === null || response === void 0 ? void 0 : (_response$messages4 = response.messages) === null || _response$messages4 === void 0 ? void 0 : (_response$messages4$e = _response$messages4.error) === null || _response$messages4$e === void 0 ? void 0 : _response$messages4$e[0]) || 'Replace failed';
        moduleAutoprovisionFirmware.setProgress(0, err);
      }
    });
  },
  initializeUpload: function initializeUpload() {
    if (typeof FilesAPI === 'undefined' || typeof Resumable === 'undefined') {
      return;
    }

    var config = FilesAPI.configureResumable({
      target: "".concat(Config.pbxUrl, "/pbxcore/api/v3/files:upload"),
      fileType: ['rom', 'bin', 'fw', 'z'],
      // 80 MB per file — server enforces, but we short-circuit on the client too.
      maxFileSize: 80 * 1024 * 1024,
      query: function query() {
        return {
          category: 'autoprovision-firmware'
        };
      }
    });
    var resumable = new Resumable(config);

    if (!resumable.support) {
      console.error('Resumable.js is not supported');
      return;
    }

    var dropEl = document.getElementById('firmware-dropzone');
    var browseEl = document.getElementById('firmware-browse');

    if (dropEl) {
      resumable.assignDrop(dropEl);
    }

    if (browseEl) {
      resumable.assignBrowse(browseEl);
    }

    FilesAPI.setupResumableEvents(resumable, function (event, data) {
      return moduleAutoprovisionFirmware.handleUploadEvent(event, data);
    }, true);
    moduleAutoprovisionFirmware.resumable = resumable;
  },
  handleUploadEvent: function handleUploadEvent(event, data) {
    switch (event) {
      case 'uploadStart':
        moduleAutoprovisionFirmware.setProgress(0, 'Uploading…');
        break;

      case 'fileProgress':
        moduleAutoprovisionFirmware.setProgress(Math.floor(data.file.progress() * 100));
        break;

      case 'progress':
        moduleAutoprovisionFirmware.setProgress(Math.floor(data.percent));
        break;

      case 'fileSuccess':
        {
          var _response3, _response3$data, _response4, _response4$data;

          var response;

          try {
            response = JSON.parse(data.response);
          } catch (err) {
            console.error('Upload response parse error:', err);
            return;
          }

          var fileId = ((_response3 = response) === null || _response3 === void 0 ? void 0 : (_response3$data = _response3.data) === null || _response3$data === void 0 ? void 0 : _response3$data.upload_id) || '';
          var status = ((_response4 = response) === null || _response4 === void 0 ? void 0 : (_response4$data = _response4.data) === null || _response4$data === void 0 ? void 0 : _response4$data.d_status) || '';

          if (status === 'MERGING') {
            moduleAutoprovisionFirmware.setProgress(95, 'Merging chunks…');
            moduleAutoprovisionFirmware.waitForMerge(fileId, function () {
              return moduleAutoprovisionFirmware.registerFirmware(fileId);
            });
          } else {
            moduleAutoprovisionFirmware.setProgress(100, 'Registering…');
            moduleAutoprovisionFirmware.registerFirmware(fileId);
          }

          break;
        }

      case 'fileError':
        moduleAutoprovisionFirmware.setProgress(0, "Upload failed: ".concat(data.message || ''));
        break;

      default:
        break;
    }
  },

  /**
   * Polls Core's /files:uploadStatus until the chunk merge for the given file_id
   * reports UPLOAD_COMPLETE, then invokes `onReady`. A 60-iteration cap at ~1s
   * per poll covers the 80 MB ceiling (Core merges roughly 50 MB/sec on slow
   * USB-backed installs) while still giving up rather than spinning forever.
   *
   * On error or timeout we still invoke `onReady` so the user gets a clean
   * 425-style error from our endpoint rather than a silent stuck progress bar.
   */
  waitForMerge: function waitForMerge(fileId, onReady) {
    var attempt = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;

    if (!fileId || typeof FilesAPI === 'undefined' || typeof FilesAPI.getStatusUploadFile !== 'function') {
      onReady();
      return;
    }

    if (attempt > 60) {
      moduleAutoprovisionFirmware.setProgress(95, 'Merge taking too long — proceeding anyway');
      onReady();
      return;
    }

    FilesAPI.getStatusUploadFile(fileId, function (info) {
      if (info === false || !info) {
        setTimeout(function () {
          return moduleAutoprovisionFirmware.waitForMerge(fileId, onReady, attempt + 1);
        }, 1000);
        return;
      }

      var status = info.d_status || '';

      if (status === 'UPLOAD_COMPLETE' || status === 'MERGED') {
        onReady();
        return;
      }

      if (info.d_status_progress) {
        moduleAutoprovisionFirmware.setProgress(95, "Merging\u2026 ".concat(info.d_status_progress, "%"));
      }

      setTimeout(function () {
        return moduleAutoprovisionFirmware.waitForMerge(fileId, onReady, attempt + 1);
      }, 1000);
    });
  },
  registerFirmware: function registerFirmware(fileId) {
    if (!fileId) {
      moduleAutoprovisionFirmware.setProgress(0, 'Missing file_id from Core upload');
      return;
    }

    var payload = {
      file_id: fileId,
      vendor: moduleAutoprovisionFirmware.$vendor.val() || '',
      model: moduleAutoprovisionFirmware.$model.val() || '',
      version: moduleAutoprovisionFirmware.$version.val() || '',
      notes: moduleAutoprovisionFirmware.$notes.val() || ''
    };
    $.api({
      url: "".concat(moduleAutoprovisionFirmware.basePath, ":upload"),
      method: 'POST',
      data: payload,
      on: 'now',
      onSuccess: function onSuccess(response) {
        if ((response === null || response === void 0 ? void 0 : response.result) === true) {
          moduleAutoprovisionFirmware.setProgress(100, 'Registered');
          moduleAutoprovisionFirmware.refresh();
        } else {
          var _response$messages5, _response$messages5$e;

          var err = (response === null || response === void 0 ? void 0 : (_response$messages5 = response.messages) === null || _response$messages5 === void 0 ? void 0 : (_response$messages5$e = _response$messages5.error) === null || _response$messages5$e === void 0 ? void 0 : _response$messages5$e[0]) || 'Registration failed';
          moduleAutoprovisionFirmware.setProgress(0, err);
        }
      },
      onFailure: function onFailure(response) {
        var _response$messages6, _response$messages6$e;

        var err = (response === null || response === void 0 ? void 0 : (_response$messages6 = response.messages) === null || _response$messages6 === void 0 ? void 0 : (_response$messages6$e = _response$messages6.error) === null || _response$messages6$e === void 0 ? void 0 : _response$messages6$e[0]) || 'Registration failed';
        moduleAutoprovisionFirmware.setProgress(0, err);
      }
    });
  },
  setProgress: function setProgress(percent, label) {
    if (!moduleAutoprovisionFirmware.$progress || moduleAutoprovisionFirmware.$progress.length === 0) {
      return;
    }

    moduleAutoprovisionFirmware.$progressBar.css('width', "".concat(percent, "%"));

    if (label !== undefined) {
      moduleAutoprovisionFirmware.$progress.find('.label').text(label);
    }
  }
};
$(document).ready(function () {
  moduleAutoprovisionFirmware.initialize();
});
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbInNyYy9tb2R1bGUtYXV0b3Byb3Zpc2lvbi1maXJtd2FyZS5qcyJdLCJuYW1lcyI6WyJtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUiLCJiYXNlUGF0aCIsIiR0YWIiLCIkdGFibGVCb2R5IiwiJHRvdGFscyIsIiRkcm9wWm9uZSIsIiRwcm9ncmVzcyIsIiRwcm9ncmVzc0JhciIsIiR2ZW5kb3IiLCIkbW9kZWwiLCIkdmVyc2lvbiIsIiRub3RlcyIsImxvYWRlZCIsInJlc3VtYWJsZSIsInJlcGxhY2VSZXN1bWFibGVzIiwicmVwbGFjZVRhcmdldElkIiwiaW5pdGlhbGl6ZSIsIiQiLCJsZW5ndGgiLCJvbiIsInJlZnJlc2giLCJpbml0aWFsaXplVXBsb2FkIiwiYmluZFJvd0FjdGlvbnMiLCJkcm9wZG93biIsImFwaSIsInVybCIsIm1ldGhvZCIsIm9uU3VjY2VzcyIsInJlc3BvbnNlIiwiaXRlbXMiLCJkYXRhIiwidG90YWxzIiwicmVuZGVyVGFibGUiLCJyZW5kZXJUb3RhbHMiLCJvbkZhaWx1cmUiLCJjb25zb2xlIiwiZXJyb3IiLCIkdGJvZHkiLCJlbXB0eSIsImFwcGVuZCIsImZvckVhY2giLCJpdGVtIiwic2hhIiwic2hhMjU2Iiwic3Vic3RyaW5nIiwic2l6ZU1iIiwic2l6ZSIsInRvRml4ZWQiLCIkcm93IiwiYXR0ciIsImlkIiwidmVuZG9yIiwibW9kZWwiLCJ2ZXJzaW9uIiwibm90ZXMiLCJ0ZXh0IiwiZmlsZW5hbWUiLCJodG1sIiwidXNlZCIsImNhcCIsInVzZWRfY2FwIiwiZmlsZUNhcCIsImZpbGVfY2FwIiwidXNlZE1iIiwiY2FwTWIiLCJmaWxlQ2FwTWIiLCIkYm9keSIsImhhbmRsZURlbGV0ZSIsImUiLCJwcmV2ZW50RGVmYXVsdCIsImhhbmRsZUVkaXQiLCJjbG9zZXN0Iiwib3BlbkVkaXRNb2RhbCIsInN1Ym1pdEVkaXRNb2RhbCIsImhhbmRsZVJlcGxhY2UiLCJ0cmlnZ2VyUmVwbGFjZVBpY2tlciIsIiRtb2RhbCIsImZpbmQiLCJ2YWwiLCJtb2RhbCIsInBheWxvYWQiLCJyZXN1bHQiLCJlcnIiLCJtZXNzYWdlcyIsInNob3ciLCJpbnB1dCIsImRvY3VtZW50IiwiZ2V0RWxlbWVudEJ5SWQiLCJjcmVhdGVFbGVtZW50IiwidHlwZSIsInN0eWxlIiwiZGlzcGxheSIsImJvZHkiLCJhcHBlbmRDaGlsZCIsImNvbmZpZyIsIkZpbGVzQVBJIiwiY29uZmlndXJlUmVzdW1hYmxlIiwidGFyZ2V0IiwiQ29uZmlnIiwicGJ4VXJsIiwiZmlsZVR5cGUiLCJtYXhGaWxlU2l6ZSIsInF1ZXJ5IiwiY2F0ZWdvcnkiLCJSZXN1bWFibGUiLCJzdXBwb3J0IiwiYXNzaWduQnJvd3NlIiwic2V0dXBSZXN1bWFibGVFdmVudHMiLCJldmVudCIsImhhbmRsZVJlcGxhY2VVcGxvYWRFdmVudCIsImNsaWNrIiwic2V0UHJvZ3Jlc3MiLCJNYXRoIiwiZmxvb3IiLCJmaWxlIiwicHJvZ3Jlc3MiLCJwZXJjZW50IiwiSlNPTiIsInBhcnNlIiwiZmlsZUlkIiwidXBsb2FkX2lkIiwic3RhdHVzIiwiZF9zdGF0dXMiLCJ3YWl0Rm9yTWVyZ2UiLCJjYWxsUmVwbGFjZSIsIm1lc3NhZ2UiLCJmaWxlX2lkIiwiZHJvcEVsIiwiYnJvd3NlRWwiLCJhc3NpZ25Ecm9wIiwiaGFuZGxlVXBsb2FkRXZlbnQiLCJyZWdpc3RlckZpcm13YXJlIiwib25SZWFkeSIsImF0dGVtcHQiLCJnZXRTdGF0dXNVcGxvYWRGaWxlIiwiaW5mbyIsInNldFRpbWVvdXQiLCJkX3N0YXR1c19wcm9ncmVzcyIsImxhYmVsIiwiY3NzIiwidW5kZWZpbmVkIiwicmVhZHkiXSwibWFwcGluZ3MiOiI7O0FBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxJQUFNQSwyQkFBMkIsR0FBRztBQUNuQ0MsRUFBQUEsUUFBUSxFQUFFLCtDQUR5QjtBQUduQ0MsRUFBQUEsSUFBSSxFQUFFLElBSDZCO0FBSW5D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QUMrRyxXQUE1QixDQUF3QyxDQUF4QyxFQUEyQzdCLEdBQTNDO0FBQ0E7QUFDRCxPQWJJO0FBY0xoRCxNQUFBQSxTQWRLLHFCQWNLTixRQWRMLEVBY2U7QUFBQTs7QUFDbkIsWUFBTXNELEdBQUcsR0FBRyxDQUFBdEQsUUFBUSxTQUFSLElBQUFBLFFBQVEsV0FBUixtQ0FBQUEsUUFBUSxDQUFFdUQsUUFBVixxR0FBb0IvQyxLQUFwQixnRkFBNEIsQ0FBNUIsTUFBa0MscUJBQTlDO0FBQ0FwQyxRQUFBQSwyQkFBMkIsQ0FBQytHLFdBQTVCLENBQXdDLENBQXhDLEVBQTJDN0IsR0FBM0M7QUFDQTtBQWpCSSxLQUFOO0FBbUJBLEdBemVrQztBQTJlbkM2QixFQUFBQSxXQTNlbUMsdUJBMmV2QkssT0EzZXVCLEVBMmVkc0IsS0EzZWMsRUEyZVA7QUFDM0IsUUFBSSxDQUFDMUksMkJBQTJCLENBQUNNLFNBQTdCLElBQTBDTiwyQkFBMkIsQ0FBQ00sU0FBNUIsQ0FBc0NZLE1BQXRDLEtBQWlELENBQS9GLEVBQWtHO0FBQ2pHO0FBQ0E7O0FBQ0RsQixJQUFBQSwyQkFBMkIsQ0FBQ08sWUFBNUIsQ0FBeUNvSSxHQUF6QyxDQUE2QyxPQUE3QyxZQUF5RHZCLE9BQXpEOztBQUNBLFFBQUlzQixLQUFLLEtBQUtFLFNBQWQsRUFBeUI7QUFDeEI1SSxNQUFBQSwyQkFBMkIsQ0FBQ00sU0FBNUIsQ0FBc0N1RSxJQUF0QyxDQUEyQyxRQUEzQyxFQUFxRHRCLElBQXJELENBQTBEbUYsS0FBMUQ7QUFDQTtBQUNEO0FBbmZrQyxDQUFwQztBQXNmQXpILENBQUMsQ0FBQ3FFLFFBQUQsQ0FBRCxDQUFZdUQsS0FBWixDQUFrQixZQUFNO0FBQ3ZCN0ksRUFBQUEsMkJBQTJCLENBQUNnQixVQUE1QjtBQUNBLENBRkQiLCJzb3VyY2VzQ29udGVudCI6WyIvKlxuICogQ29weXJpZ2h0IMKpIE1JS08gTExDIC0gQWxsIFJpZ2h0cyBSZXNlcnZlZFxuICogVW5hdXRob3JpemVkIGNvcHlpbmcgb2YgdGhpcyBmaWxlLCB2aWEgYW55IG1lZGl1bSBpcyBzdHJpY3RseSBwcm9oaWJpdGVkXG4gKiBQcm9wcmlldGFyeSBhbmQgY29uZmlkZW50aWFsXG4gKi9cblxuLyogZ2xvYmFsIGdsb2JhbFJvb3RVcmwsIENvbmZpZywgRmlsZXNBUEksIFJlc3VtYWJsZSAqL1xuXG4vKipcbiAqIEZpcm13YXJlIHRhYiBvbiB0aGUgTW9kdWxlQXV0b3Byb3Zpc2lvbiBzZXR0aW5ncyBwYWdlLlxuICpcbiAqIExvYWRzIHRoZSBmaXJtd2FyZSBsaXN0IG9uIGRlbWFuZCBhbmQgd2lyZXMgdGhlIGRyYWctYW5kLWRyb3AgdXBsb2FkIHpvbmUgdG9cbiAqIENvcmUncyBjaHVua2VkIC9wYnhjb3JlL2FwaS92My9maWxlczp1cGxvYWQgZW5kcG9pbnQuIEFmdGVyIHRoZSBjaHVua2VkIHVwbG9hZFxuICogbGFuZHMgYSBmaWxlX2lkLCB0aGlzIG1vZHVsZSBQT1NUcyB0aGUgcmVnaXN0cmF0aW9uIG1ldGFkYXRhICh2ZW5kb3IsIG1vZGVsLFxuICogdmVyc2lvbiwgbm90ZXMpIHRvIC9wYnhjb3JlL2FwaS92My9tb2R1bGUtYXV0b3Byb3Zpc2lvbi9maXJtd2FyZTp1cGxvYWQg4oCUXG4gKiB0aGUgc2FtZSB0d28tc3RlcCBmbG93IGFzIE1vZHVsZUV4YW1wbGVSZXN0QVBJdjMuXG4gKi9cbmNvbnN0IG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZSA9IHtcblx0YmFzZVBhdGg6ICcvcGJ4Y29yZS9hcGkvdjMvbW9kdWxlLWF1dG9wcm92aXNpb24vZmlybXdhcmUnLFxuXG5cdCR0YWI6IG51bGwsXG5cdCR0YWJsZUJvZHk6IG51bGwsXG5cdCR0b3RhbHM6IG51bGwsXG5cdCRkcm9wWm9uZTogbnVsbCxcblx0JHByb2dyZXNzOiBudWxsLFxuXHQkcHJvZ3Jlc3NCYXI6IG51bGwsXG5cdCR2ZW5kb3I6IG51bGwsXG5cdCRtb2RlbDogbnVsbCxcblx0JHZlcnNpb246IG51bGwsXG5cdCRub3RlczogbnVsbCxcblxuXHRsb2FkZWQ6IGZhbHNlLFxuXHRyZXN1bWFibGU6IG51bGwsXG5cdC8vIFRyYWNrcyBwZXItcm93IFwicmVwbGFjZSBmaWxlXCIgdXBsb2Fkcy4gS2V5ZWQgYnkgZmlybXdhcmUgcm93IGlkIHNvXG5cdC8vIGNvbmN1cnJlbnQgcmVwbGFjZXMgb24gZGlmZmVyZW50IHJvd3MgZG9uJ3QgY2xvYmJlciBlYWNoIG90aGVyJ3MgbWV0YWRhdGEuXG5cdHJlcGxhY2VSZXN1bWFibGVzOiB7fSxcblx0cmVwbGFjZVRhcmdldElkOiBudWxsLFxuXG5cdGluaXRpYWxpemUoKSB7XG5cdFx0Y29uc3QgJHRhYiA9ICQoJ2EuaXRlbVtkYXRhLXRhYj1cImZpcm13YXJlXCJdJyk7XG5cdFx0aWYgKCR0YWIubGVuZ3RoID09PSAwKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS4kdGFiID0gJCgnLnVpLnRhYi5zZWdtZW50W2RhdGEtdGFiPVwiZmlybXdhcmVcIl0nKTtcblx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuJHRhYmxlQm9keSA9ICQoJyNmaXJtd2FyZS10YWJsZSB0Ym9keScpO1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS4kdG90YWxzID0gJCgnI2Zpcm13YXJlLXRvdGFscycpO1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS4kZHJvcFpvbmUgPSAkKCcjZmlybXdhcmUtZHJvcHpvbmUnKTtcblx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuJHByb2dyZXNzID0gJCgnI2Zpcm13YXJlLXByb2dyZXNzJyk7XG5cdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLiRwcm9ncmVzc0JhciA9ICQoJyNmaXJtd2FyZS1wcm9ncmVzcyAuYmFyJyk7XG5cdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLiR2ZW5kb3IgPSAkKCcjZmlybXdhcmUtdmVuZG9yJyk7XG5cdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLiRtb2RlbCA9ICQoJyNmaXJtd2FyZS1tb2RlbCcpO1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS4kdmVyc2lvbiA9ICQoJyNmaXJtd2FyZS12ZXJzaW9uJyk7XG5cdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLiRub3RlcyA9ICQoJyNmaXJtd2FyZS1ub3RlcycpO1xuXG5cdFx0Ly8gTG9hZCBsaXN0IHdoZW4gdGhlIHRhYiBpcyBmaXJzdCBvcGVuZWQg4oCUIHRoZSBkZXZpY2UvdGVtcGxhdGUgdGFicyBhcmVcblx0XHQvLyB0aGUgY29tbW9uIGVudHJ5IHBvaW50cywgc28gcGF5aW5nIHRoZSByb3VuZC10cmlwIHVwIGZyb250IHdvdWxkIHNsb3dcblx0XHQvLyB0aGUgcGFnZSBmb3IgYWRtaW5zIHdobyBuZXZlciB0b3VjaCBmaXJtd2FyZS5cblx0XHQkdGFiLm9uKCdjbGljaycsICgpID0+IHtcblx0XHRcdGlmICghbW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLmxvYWRlZCkge1xuXHRcdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUucmVmcmVzaCgpO1xuXHRcdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUubG9hZGVkID0gdHJ1ZTtcblx0XHRcdH1cblx0XHR9KTtcblxuXHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5pbml0aWFsaXplVXBsb2FkKCk7XG5cdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLmJpbmRSb3dBY3Rpb25zKCk7XG5cblx0XHQvLyBTZW1hbnRpYyBVSSBzdHlsaW5nIGZvciB0aGUgdmVuZG9yIHNlbGVjdC4gLnZhbCgpIHJlYWRzIHRoZSB1bmRlcmx5aW5nXG5cdFx0Ly8gPHNlbGVjdD4sIHNvIHRoaXMgb25seSBjaGFuZ2VzIGhvdyBpdCBsb29rcywgbm90IGhvdyB3ZSByZWFkIGl0LlxuXHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS4kdmVuZG9yLmRyb3Bkb3duKCk7XG5cdH0sXG5cblx0cmVmcmVzaCgpIHtcblx0XHQkLmFwaSh7XG5cdFx0XHR1cmw6IG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5iYXNlUGF0aCxcblx0XHRcdG1ldGhvZDogJ0dFVCcsXG5cdFx0XHRvbjogJ25vdycsXG5cdFx0XHRvblN1Y2Nlc3MocmVzcG9uc2UpIHtcblx0XHRcdFx0Y29uc3QgaXRlbXMgPSByZXNwb25zZT8uZGF0YT8uaXRlbXMgfHwgW107XG5cdFx0XHRcdGNvbnN0IHRvdGFscyA9IHJlc3BvbnNlPy5kYXRhPy50b3RhbHMgfHwge307XG5cdFx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5yZW5kZXJUYWJsZShpdGVtcyk7XG5cdFx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5yZW5kZXJUb3RhbHModG90YWxzKTtcblx0XHRcdH0sXG5cdFx0XHRvbkZhaWx1cmUocmVzcG9uc2UpIHtcblx0XHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnJlbmRlclRhYmxlKFtdKTtcblx0XHRcdFx0Y29uc29sZS5lcnJvcignRmFpbGVkIHRvIGxpc3QgZmlybXdhcmU6JywgcmVzcG9uc2UpO1xuXHRcdFx0fSxcblx0XHR9KTtcblx0fSxcblxuXHRyZW5kZXJUYWJsZShpdGVtcykge1xuXHRcdGNvbnN0ICR0Ym9keSA9IG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS4kdGFibGVCb2R5O1xuXHRcdCR0Ym9keS5lbXB0eSgpO1xuXHRcdGlmIChpdGVtcy5sZW5ndGggPT09IDApIHtcblx0XHRcdCR0Ym9keS5hcHBlbmQoXG5cdFx0XHRcdCc8dHI+PHRkIGNvbHNwYW49XCI3XCIgY2xhc3M9XCJjZW50ZXIgYWxpZ25lZFwiPidcblx0XHRcdFx0XHQrICc8aSBjbGFzcz1cImluZm8gY2lyY2xlIGljb25cIj48L2k+IE5vIGZpcm13YXJlIHVwbG9hZGVkIHlldC4nXG5cdFx0XHRcdFx0KyAnPC90ZD48L3RyPicsXG5cdFx0XHQpO1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblx0XHRpdGVtcy5mb3JFYWNoKChpdGVtKSA9PiB7XG5cdFx0XHRjb25zdCBzaGEgPSAoaXRlbS5zaGEyNTYgfHwgJycpLnN1YnN0cmluZygwLCAxMik7XG5cdFx0XHRjb25zdCBzaXplTWIgPSAoaXRlbS5zaXplIC8gKDEwMjQgKiAxMDI0KSkudG9GaXhlZCgyKTtcblx0XHRcdGNvbnN0ICRyb3cgPSAkKCc8dHI+Jylcblx0XHRcdFx0LmF0dHIoJ2RhdGEtZmlybXdhcmUtaWQnLCBpdGVtLmlkKVxuXHRcdFx0XHQuYXR0cignZGF0YS1maXJtd2FyZS12ZW5kb3InLCBpdGVtLnZlbmRvciB8fCAnJylcblx0XHRcdFx0LmF0dHIoJ2RhdGEtZmlybXdhcmUtbW9kZWwnLCBpdGVtLm1vZGVsIHx8ICcnKVxuXHRcdFx0XHQuYXR0cignZGF0YS1maXJtd2FyZS12ZXJzaW9uJywgaXRlbS52ZXJzaW9uIHx8ICcnKVxuXHRcdFx0XHQuYXR0cignZGF0YS1maXJtd2FyZS1ub3RlcycsIGl0ZW0ubm90ZXMgfHwgJycpXG5cdFx0XHRcdC5hcHBlbmQoJCgnPHRkPicpLnRleHQoaXRlbS52ZW5kb3IgfHwgJycpKVxuXHRcdFx0XHQuYXBwZW5kKCQoJzx0ZD4nKS50ZXh0KGl0ZW0ubW9kZWwgfHwgJyonKSlcblx0XHRcdFx0LmFwcGVuZCgkKCc8dGQ+JykudGV4dChpdGVtLmZpbGVuYW1lIHx8ICcnKSlcblx0XHRcdFx0LmFwcGVuZCgkKCc8dGQ+JykudGV4dChpdGVtLnZlcnNpb24gfHwgJycpKVxuXHRcdFx0XHQuYXBwZW5kKCQoJzx0ZD4nKS50ZXh0KGAke3NpemVNYn0gTUJgKSlcblx0XHRcdFx0LmFwcGVuZCgkKCc8dGQ+JykuYXR0cigndGl0bGUnLCBpdGVtLnNoYTI1NiB8fCAnJykudGV4dChzaGEpKVxuXHRcdFx0XHQuYXBwZW5kKFxuXHRcdFx0XHRcdCQoJzx0ZCBjbGFzcz1cInJpZ2h0IGFsaWduZWRcIj4nKVxuXHRcdFx0XHRcdFx0LmFwcGVuZChcblx0XHRcdFx0XHRcdFx0JCgnPGEgY2xhc3M9XCJ1aSBtaW5pIGJsdWUgYnV0dG9uIGZpcm13YXJlLWRvd25sb2FkXCI+Jylcblx0XHRcdFx0XHRcdFx0XHQuYXR0cignaHJlZicsIGl0ZW0udXJsIHx8ICcjJylcblx0XHRcdFx0XHRcdFx0XHQuYXR0cigndGFyZ2V0JywgJ19ibGFuaycpXG5cdFx0XHRcdFx0XHRcdFx0LmF0dHIoJ3RpdGxlJywgJ0Rvd25sb2FkJylcblx0XHRcdFx0XHRcdFx0XHQuaHRtbCgnPGkgY2xhc3M9XCJkb3dubG9hZCBpY29uXCI+PC9pPicpLFxuXHRcdFx0XHRcdFx0KVxuXHRcdFx0XHRcdFx0LmFwcGVuZChcblx0XHRcdFx0XHRcdFx0JCgnPGEgY2xhc3M9XCJ1aSBtaW5pIHRlYWwgYnV0dG9uIGZpcm13YXJlLWVkaXRcIj4nKVxuXHRcdFx0XHRcdFx0XHRcdC5hdHRyKCdocmVmJywgJyMnKVxuXHRcdFx0XHRcdFx0XHRcdC5hdHRyKCdkYXRhLWZpcm13YXJlLWlkJywgaXRlbS5pZClcblx0XHRcdFx0XHRcdFx0XHQuYXR0cigndGl0bGUnLCAnRWRpdCBtZXRhZGF0YScpXG5cdFx0XHRcdFx0XHRcdFx0Lmh0bWwoJzxpIGNsYXNzPVwiZWRpdCBpY29uXCI+PC9pPicpLFxuXHRcdFx0XHRcdFx0KVxuXHRcdFx0XHRcdFx0LmFwcGVuZChcblx0XHRcdFx0XHRcdFx0JCgnPGEgY2xhc3M9XCJ1aSBtaW5pIG9saXZlIGJ1dHRvbiBmaXJtd2FyZS1yZXBsYWNlXCI+Jylcblx0XHRcdFx0XHRcdFx0XHQuYXR0cignaHJlZicsICcjJylcblx0XHRcdFx0XHRcdFx0XHQuYXR0cignZGF0YS1maXJtd2FyZS1pZCcsIGl0ZW0uaWQpXG5cdFx0XHRcdFx0XHRcdFx0LmF0dHIoJ3RpdGxlJywgJ1JlcGxhY2UgZmlsZScpXG5cdFx0XHRcdFx0XHRcdFx0Lmh0bWwoJzxpIGNsYXNzPVwic3luYyBpY29uXCI+PC9pPicpLFxuXHRcdFx0XHRcdFx0KVxuXHRcdFx0XHRcdFx0LmFwcGVuZChcblx0XHRcdFx0XHRcdFx0JCgnPGEgY2xhc3M9XCJ1aSBtaW5pIHJlZCBidXR0b24gZmlybXdhcmUtZGVsZXRlXCI+Jylcblx0XHRcdFx0XHRcdFx0XHQuYXR0cignaHJlZicsICcjJylcblx0XHRcdFx0XHRcdFx0XHQuYXR0cignZGF0YS1maXJtd2FyZS1pZCcsIGl0ZW0uaWQpXG5cdFx0XHRcdFx0XHRcdFx0LmF0dHIoJ3RpdGxlJywgJ0RlbGV0ZScpXG5cdFx0XHRcdFx0XHRcdFx0Lmh0bWwoJzxpIGNsYXNzPVwidHJhc2ggaWNvblwiPjwvaT4nKSxcblx0XHRcdFx0XHRcdCksXG5cdFx0XHRcdCk7XG5cdFx0XHQkdGJvZHkuYXBwZW5kKCRyb3cpO1xuXHRcdH0pO1xuXHR9LFxuXG5cdHJlbmRlclRvdGFscyh0b3RhbHMpIHtcblx0XHRjb25zdCB1c2VkID0gdG90YWxzLnVzZWQgfHwgMDtcblx0XHRjb25zdCBjYXAgPSB0b3RhbHMudXNlZF9jYXAgfHwgMDtcblx0XHRjb25zdCBmaWxlQ2FwID0gdG90YWxzLmZpbGVfY2FwIHx8IDA7XG5cdFx0Y29uc3QgdXNlZE1iID0gKHVzZWQgLyAoMTAyNCAqIDEwMjQpKS50b0ZpeGVkKDEpO1xuXHRcdGNvbnN0IGNhcE1iID0gKGNhcCAvICgxMDI0ICogMTAyNCkpLnRvRml4ZWQoMCk7XG5cdFx0Y29uc3QgZmlsZUNhcE1iID0gKGZpbGVDYXAgLyAoMTAyNCAqIDEwMjQpKS50b0ZpeGVkKDApO1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS4kdG90YWxzLnRleHQoXG5cdFx0XHRgJHt1c2VkTWJ9IE1CIC8gJHtjYXBNYn0gTUIgdXNlZCDigJQgc2luZ2xlIGZpbGUgY2FwICR7ZmlsZUNhcE1ifSBNQmAsXG5cdFx0KTtcblx0fSxcblxuXHRiaW5kUm93QWN0aW9ucygpIHtcblx0XHRjb25zdCAkYm9keSA9ICQoJ2JvZHknKTtcblxuXHRcdCRib2R5Lm9uKCdjbGljaycsICcuZmlybXdhcmUtZGVsZXRlJywgZnVuY3Rpb24gaGFuZGxlRGVsZXRlKGUpIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdGNvbnN0IGlkID0gJCh0aGlzKS5hdHRyKCdkYXRhLWZpcm13YXJlLWlkJyk7XG5cdFx0XHRpZiAoIWlkKSB7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblx0XHRcdCQuYXBpKHtcblx0XHRcdFx0dXJsOiBgJHttb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuYmFzZVBhdGh9LyR7aWR9YCxcblx0XHRcdFx0bWV0aG9kOiAnREVMRVRFJyxcblx0XHRcdFx0b246ICdub3cnLFxuXHRcdFx0XHRvblN1Y2Nlc3MoKSB7XG5cdFx0XHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnJlZnJlc2goKTtcblx0XHRcdFx0fSxcblx0XHRcdFx0b25GYWlsdXJlKHJlc3BvbnNlKSB7XG5cdFx0XHRcdFx0Y29uc29sZS5lcnJvcignRmFpbGVkIHRvIGRlbGV0ZSBmaXJtd2FyZTonLCByZXNwb25zZSk7XG5cdFx0XHRcdH0sXG5cdFx0XHR9KTtcblx0XHR9KTtcblxuXHRcdCRib2R5Lm9uKCdjbGljaycsICcuZmlybXdhcmUtZWRpdCcsIGZ1bmN0aW9uIGhhbmRsZUVkaXQoZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0Y29uc3QgJHJvdyA9ICQodGhpcykuY2xvc2VzdCgndHInKTtcblx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5vcGVuRWRpdE1vZGFsKHtcblx0XHRcdFx0aWQ6ICRyb3cuYXR0cignZGF0YS1maXJtd2FyZS1pZCcpLFxuXHRcdFx0XHR2ZW5kb3I6ICRyb3cuYXR0cignZGF0YS1maXJtd2FyZS12ZW5kb3InKSxcblx0XHRcdFx0bW9kZWw6ICRyb3cuYXR0cignZGF0YS1maXJtd2FyZS1tb2RlbCcpLFxuXHRcdFx0XHR2ZXJzaW9uOiAkcm93LmF0dHIoJ2RhdGEtZmlybXdhcmUtdmVyc2lvbicpLFxuXHRcdFx0XHRub3RlczogJHJvdy5hdHRyKCdkYXRhLWZpcm13YXJlLW5vdGVzJyksXG5cdFx0XHR9KTtcblx0XHR9KTtcblxuXHRcdCRib2R5Lm9uKCdjbGljaycsICcjZmlybXdhcmUtZWRpdC1zYXZlJywgKCkgPT4ge1xuXHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnN1Ym1pdEVkaXRNb2RhbCgpO1xuXHRcdH0pO1xuXG5cdFx0Ly8gXCJSZXBsYWNlIGZpbGVcIiBvcGVucyBhIGhpZGRlbiBmaWxlIHBpY2tlciBwb2ludGVkIGF0IHRoaXMgcm93IGlkLlxuXHRcdCRib2R5Lm9uKCdjbGljaycsICcuZmlybXdhcmUtcmVwbGFjZScsIGZ1bmN0aW9uIGhhbmRsZVJlcGxhY2UoZSkge1xuXHRcdFx0ZS5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnJlcGxhY2VUYXJnZXRJZCA9ICQodGhpcykuYXR0cignZGF0YS1maXJtd2FyZS1pZCcpO1xuXHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnRyaWdnZXJSZXBsYWNlUGlja2VyKCk7XG5cdFx0fSk7XG5cdH0sXG5cblx0b3BlbkVkaXRNb2RhbChpdGVtKSB7XG5cdFx0Y29uc3QgJG1vZGFsID0gJCgnI2Zpcm13YXJlLWVkaXQtbW9kYWwnKTtcblx0XHQkbW9kYWwuZmluZCgnI2Zpcm13YXJlLWVkaXQtaWQnKS52YWwoaXRlbS5pZCB8fCAnJyk7XG5cdFx0JG1vZGFsLmZpbmQoJyNmaXJtd2FyZS1lZGl0LXZlbmRvcicpLnZhbChpdGVtLnZlbmRvciB8fCAnJyk7XG5cdFx0JG1vZGFsLmZpbmQoJyNmaXJtd2FyZS1lZGl0LW1vZGVsJykudmFsKGl0ZW0ubW9kZWwgfHwgJycpO1xuXHRcdCRtb2RhbC5maW5kKCcjZmlybXdhcmUtZWRpdC12ZXJzaW9uJykudmFsKGl0ZW0udmVyc2lvbiB8fCAnJyk7XG5cdFx0JG1vZGFsLmZpbmQoJyNmaXJtd2FyZS1lZGl0LW5vdGVzJykudmFsKGl0ZW0ubm90ZXMgfHwgJycpO1xuXHRcdCRtb2RhbC5maW5kKCcjZmlybXdhcmUtZWRpdC12ZW5kb3InKS5kcm9wZG93bigncmVmcmVzaCcpO1xuXHRcdCRtb2RhbC5tb2RhbCgnc2hvdycpO1xuXHR9LFxuXG5cdHN1Ym1pdEVkaXRNb2RhbCgpIHtcblx0XHRjb25zdCAkbW9kYWwgPSAkKCcjZmlybXdhcmUtZWRpdC1tb2RhbCcpO1xuXHRcdGNvbnN0IGlkID0gJG1vZGFsLmZpbmQoJyNmaXJtd2FyZS1lZGl0LWlkJykudmFsKCk7XG5cdFx0aWYgKCFpZCkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblx0XHRjb25zdCBwYXlsb2FkID0ge1xuXHRcdFx0dmVuZG9yOiAkbW9kYWwuZmluZCgnI2Zpcm13YXJlLWVkaXQtdmVuZG9yJykudmFsKCksXG5cdFx0XHRtb2RlbDogJG1vZGFsLmZpbmQoJyNmaXJtd2FyZS1lZGl0LW1vZGVsJykudmFsKCksXG5cdFx0XHR2ZXJzaW9uOiAkbW9kYWwuZmluZCgnI2Zpcm13YXJlLWVkaXQtdmVyc2lvbicpLnZhbCgpLFxuXHRcdFx0bm90ZXM6ICRtb2RhbC5maW5kKCcjZmlybXdhcmUtZWRpdC1ub3RlcycpLnZhbCgpLFxuXHRcdH07XG5cdFx0JC5hcGkoe1xuXHRcdFx0dXJsOiBgJHttb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuYmFzZVBhdGh9LyR7aWR9YCxcblx0XHRcdG1ldGhvZDogJ1BBVENIJyxcblx0XHRcdGRhdGE6IHBheWxvYWQsXG5cdFx0XHRvbjogJ25vdycsXG5cdFx0XHRvblN1Y2Nlc3MocmVzcG9uc2UpIHtcblx0XHRcdFx0aWYgKHJlc3BvbnNlPy5yZXN1bHQgPT09IHRydWUpIHtcblx0XHRcdFx0XHQkbW9kYWwubW9kYWwoJ2hpZGUnKTtcblx0XHRcdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUucmVmcmVzaCgpO1xuXHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdGNvbnN0IGVyciA9IHJlc3BvbnNlPy5tZXNzYWdlcz8uZXJyb3I/LlswXSB8fCAnVXBkYXRlIGZhaWxlZCc7XG5cdFx0XHRcdFx0JG1vZGFsLmZpbmQoJyNmaXJtd2FyZS1lZGl0LWVycm9yJykudGV4dChlcnIpLnNob3coKTtcblx0XHRcdFx0fVxuXHRcdFx0fSxcblx0XHRcdG9uRmFpbHVyZShyZXNwb25zZSkge1xuXHRcdFx0XHRjb25zdCBlcnIgPSByZXNwb25zZT8ubWVzc2FnZXM/LmVycm9yPy5bMF0gfHwgJ1VwZGF0ZSBmYWlsZWQnO1xuXHRcdFx0XHQkbW9kYWwuZmluZCgnI2Zpcm13YXJlLWVkaXQtZXJyb3InKS50ZXh0KGVycikuc2hvdygpO1xuXHRcdFx0fSxcblx0XHR9KTtcblx0fSxcblxuXHR0cmlnZ2VyUmVwbGFjZVBpY2tlcigpIHtcblx0XHQvLyBMYXp5LWNyZWF0ZSBhIHNpbmdsZSBoaWRkZW4gPGlucHV0IHR5cGU9XCJmaWxlXCI+IHRoZSBmaXJzdCB0aW1lIGFyb3VuZDtcblx0XHQvLyBSZXN1bWFibGUuanMgYXR0YWNoZXMgaXRzIG93biBjaGFuZ2UgaGFuZGxlciB3aGVuIHdlIGFzc2lnbkJyb3dzZSgpIGl0LlxuXHRcdGxldCBpbnB1dCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdmaXJtd2FyZS1yZXBsYWNlLWlucHV0Jyk7XG5cdFx0aWYgKCFpbnB1dCkge1xuXHRcdFx0aW5wdXQgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdpbnB1dCcpO1xuXHRcdFx0aW5wdXQudHlwZSA9ICdmaWxlJztcblx0XHRcdGlucHV0LmlkID0gJ2Zpcm13YXJlLXJlcGxhY2UtaW5wdXQnO1xuXHRcdFx0aW5wdXQuc3R5bGUuZGlzcGxheSA9ICdub25lJztcblx0XHRcdGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoaW5wdXQpO1xuXG5cdFx0XHRjb25zdCBjb25maWcgPSBGaWxlc0FQSS5jb25maWd1cmVSZXN1bWFibGUoe1xuXHRcdFx0XHR0YXJnZXQ6IGAke0NvbmZpZy5wYnhVcmx9L3BieGNvcmUvYXBpL3YzL2ZpbGVzOnVwbG9hZGAsXG5cdFx0XHRcdGZpbGVUeXBlOiBbJ3JvbScsICdiaW4nLCAnZncnLCAneiddLFxuXHRcdFx0XHRtYXhGaWxlU2l6ZTogODAgKiAxMDI0ICogMTAyNCxcblx0XHRcdFx0cXVlcnkoKSB7XG5cdFx0XHRcdFx0cmV0dXJuIHsgY2F0ZWdvcnk6ICdhdXRvcHJvdmlzaW9uLWZpcm13YXJlJyB9O1xuXHRcdFx0XHR9LFxuXHRcdFx0fSk7XG5cdFx0XHRjb25zdCByZXN1bWFibGUgPSBuZXcgUmVzdW1hYmxlKGNvbmZpZyk7XG5cdFx0XHRpZiAoIXJlc3VtYWJsZS5zdXBwb3J0KSB7XG5cdFx0XHRcdGNvbnNvbGUuZXJyb3IoJ1Jlc3VtYWJsZS5qcyBub3Qgc3VwcG9ydGVkJyk7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblx0XHRcdHJlc3VtYWJsZS5hc3NpZ25Ccm93c2UoaW5wdXQpO1xuXHRcdFx0RmlsZXNBUEkuc2V0dXBSZXN1bWFibGVFdmVudHMoXG5cdFx0XHRcdHJlc3VtYWJsZSxcblx0XHRcdFx0KGV2ZW50LCBkYXRhKSA9PiBtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuaGFuZGxlUmVwbGFjZVVwbG9hZEV2ZW50KGV2ZW50LCBkYXRhKSxcblx0XHRcdFx0dHJ1ZSxcblx0XHRcdCk7XG5cdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUucmVwbGFjZVJlc3VtYWJsZXMuZGVmYXVsdCA9IHJlc3VtYWJsZTtcblx0XHR9XG5cdFx0aW5wdXQuY2xpY2soKTtcblx0fSxcblxuXHRoYW5kbGVSZXBsYWNlVXBsb2FkRXZlbnQoZXZlbnQsIGRhdGEpIHtcblx0XHRzd2l0Y2ggKGV2ZW50KSB7XG5cdFx0Y2FzZSAndXBsb2FkU3RhcnQnOlxuXHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnNldFByb2dyZXNzKDAsICdVcGxvYWRpbmcgcmVwbGFjZW1lbnTigKYnKTtcblx0XHRcdGJyZWFrO1xuXHRcdGNhc2UgJ2ZpbGVQcm9ncmVzcyc6XG5cdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuc2V0UHJvZ3Jlc3MoTWF0aC5mbG9vcihkYXRhLmZpbGUucHJvZ3Jlc3MoKSAqIDEwMCkpO1xuXHRcdFx0YnJlYWs7XG5cdFx0Y2FzZSAncHJvZ3Jlc3MnOlxuXHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnNldFByb2dyZXNzKE1hdGguZmxvb3IoZGF0YS5wZXJjZW50KSk7XG5cdFx0XHRicmVhaztcblx0XHRjYXNlICdmaWxlU3VjY2Vzcyc6IHtcblx0XHRcdGxldCByZXNwb25zZTtcblx0XHRcdHRyeSB7XG5cdFx0XHRcdHJlc3BvbnNlID0gSlNPTi5wYXJzZShkYXRhLnJlc3BvbnNlKTtcblx0XHRcdH0gY2F0Y2ggKGVycikge1xuXHRcdFx0XHRjb25zb2xlLmVycm9yKCdSZXBsYWNlIHJlc3BvbnNlIHBhcnNlIGVycm9yOicsIGVycik7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblx0XHRcdGNvbnN0IGZpbGVJZCA9IHJlc3BvbnNlPy5kYXRhPy51cGxvYWRfaWQgfHwgJyc7XG5cdFx0XHRjb25zdCBzdGF0dXMgPSByZXNwb25zZT8uZGF0YT8uZF9zdGF0dXMgfHwgJyc7XG5cdFx0XHRpZiAoc3RhdHVzID09PSAnTUVSR0lORycpIHtcblx0XHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnNldFByb2dyZXNzKDk1LCAnTWVyZ2luZyBjaHVua3PigKYnKTtcblx0XHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLndhaXRGb3JNZXJnZShcblx0XHRcdFx0XHRmaWxlSWQsXG5cdFx0XHRcdFx0KCkgPT4gbW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLmNhbGxSZXBsYWNlKGZpbGVJZCksXG5cdFx0XHRcdCk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuc2V0UHJvZ3Jlc3MoMTAwLCAnUmVwbGFjaW5n4oCmJyk7XG5cdFx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5jYWxsUmVwbGFjZShmaWxlSWQpO1xuXHRcdFx0fVxuXHRcdFx0YnJlYWs7XG5cdFx0fVxuXHRcdGNhc2UgJ2ZpbGVFcnJvcic6XG5cdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuc2V0UHJvZ3Jlc3MoMCwgYFJlcGxhY2UgZmFpbGVkOiAke2RhdGEubWVzc2FnZSB8fCAnJ31gKTtcblx0XHRcdGJyZWFrO1xuXHRcdGRlZmF1bHQ6XG5cdFx0XHRicmVhaztcblx0XHR9XG5cdH0sXG5cblx0Y2FsbFJlcGxhY2UoZmlsZUlkKSB7XG5cdFx0Y29uc3QgaWQgPSBtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUucmVwbGFjZVRhcmdldElkO1xuXHRcdGlmICghaWQgfHwgIWZpbGVJZCkge1xuXHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnNldFByb2dyZXNzKDAsICdSZXBsYWNlIGNhbmNlbGxlZCDigJQgbWlzc2luZyBpZCBvciBmaWxlX2lkJyk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdCQuYXBpKHtcblx0XHRcdHVybDogYCR7bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLmJhc2VQYXRofS8ke2lkfWAsXG5cdFx0XHRtZXRob2Q6ICdQVVQnLFxuXHRcdFx0ZGF0YTogeyBmaWxlX2lkOiBmaWxlSWQgfSxcblx0XHRcdG9uOiAnbm93Jyxcblx0XHRcdG9uU3VjY2VzcyhyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAocmVzcG9uc2U/LnJlc3VsdCA9PT0gdHJ1ZSkge1xuXHRcdFx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5zZXRQcm9ncmVzcygxMDAsICdSZXBsYWNlZCcpO1xuXHRcdFx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5yZWZyZXNoKCk7XG5cdFx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdFx0Y29uc3QgZXJyID0gcmVzcG9uc2U/Lm1lc3NhZ2VzPy5lcnJvcj8uWzBdIHx8ICdSZXBsYWNlIGZhaWxlZCc7XG5cdFx0XHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnNldFByb2dyZXNzKDAsIGVycik7XG5cdFx0XHRcdH1cblx0XHRcdH0sXG5cdFx0XHRvbkZhaWx1cmUocmVzcG9uc2UpIHtcblx0XHRcdFx0Y29uc3QgZXJyID0gcmVzcG9uc2U/Lm1lc3NhZ2VzPy5lcnJvcj8uWzBdIHx8ICdSZXBsYWNlIGZhaWxlZCc7XG5cdFx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5zZXRQcm9ncmVzcygwLCBlcnIpO1xuXHRcdFx0fSxcblx0XHR9KTtcblx0fSxcblxuXHRpbml0aWFsaXplVXBsb2FkKCkge1xuXHRcdGlmICh0eXBlb2YgRmlsZXNBUEkgPT09ICd1bmRlZmluZWQnIHx8IHR5cGVvZiBSZXN1bWFibGUgPT09ICd1bmRlZmluZWQnKSB7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdGNvbnN0IGNvbmZpZyA9IEZpbGVzQVBJLmNvbmZpZ3VyZVJlc3VtYWJsZSh7XG5cdFx0XHR0YXJnZXQ6IGAke0NvbmZpZy5wYnhVcmx9L3BieGNvcmUvYXBpL3YzL2ZpbGVzOnVwbG9hZGAsXG5cdFx0XHRmaWxlVHlwZTogWydyb20nLCAnYmluJywgJ2Z3JywgJ3onXSxcblx0XHRcdC8vIDgwIE1CIHBlciBmaWxlIOKAlCBzZXJ2ZXIgZW5mb3JjZXMsIGJ1dCB3ZSBzaG9ydC1jaXJjdWl0IG9uIHRoZSBjbGllbnQgdG9vLlxuXHRcdFx0bWF4RmlsZVNpemU6IDgwICogMTAyNCAqIDEwMjQsXG5cdFx0XHRxdWVyeSgpIHtcblx0XHRcdFx0cmV0dXJuIHsgY2F0ZWdvcnk6ICdhdXRvcHJvdmlzaW9uLWZpcm13YXJlJyB9O1xuXHRcdFx0fSxcblx0XHR9KTtcblxuXHRcdGNvbnN0IHJlc3VtYWJsZSA9IG5ldyBSZXN1bWFibGUoY29uZmlnKTtcblx0XHRpZiAoIXJlc3VtYWJsZS5zdXBwb3J0KSB7XG5cdFx0XHRjb25zb2xlLmVycm9yKCdSZXN1bWFibGUuanMgaXMgbm90IHN1cHBvcnRlZCcpO1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblx0XHRjb25zdCBkcm9wRWwgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnZmlybXdhcmUtZHJvcHpvbmUnKTtcblx0XHRjb25zdCBicm93c2VFbCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdmaXJtd2FyZS1icm93c2UnKTtcblx0XHRpZiAoZHJvcEVsKSB7XG5cdFx0XHRyZXN1bWFibGUuYXNzaWduRHJvcChkcm9wRWwpO1xuXHRcdH1cblx0XHRpZiAoYnJvd3NlRWwpIHtcblx0XHRcdHJlc3VtYWJsZS5hc3NpZ25Ccm93c2UoYnJvd3NlRWwpO1xuXHRcdH1cblxuXHRcdEZpbGVzQVBJLnNldHVwUmVzdW1hYmxlRXZlbnRzKFxuXHRcdFx0cmVzdW1hYmxlLFxuXHRcdFx0KGV2ZW50LCBkYXRhKSA9PiBtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuaGFuZGxlVXBsb2FkRXZlbnQoZXZlbnQsIGRhdGEpLFxuXHRcdFx0dHJ1ZSxcblx0XHQpO1xuXHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5yZXN1bWFibGUgPSByZXN1bWFibGU7XG5cdH0sXG5cblx0aGFuZGxlVXBsb2FkRXZlbnQoZXZlbnQsIGRhdGEpIHtcblx0XHRzd2l0Y2ggKGV2ZW50KSB7XG5cdFx0Y2FzZSAndXBsb2FkU3RhcnQnOlxuXHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnNldFByb2dyZXNzKDAsICdVcGxvYWRpbmfigKYnKTtcblx0XHRcdGJyZWFrO1xuXHRcdGNhc2UgJ2ZpbGVQcm9ncmVzcyc6XG5cdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuc2V0UHJvZ3Jlc3MoTWF0aC5mbG9vcihkYXRhLmZpbGUucHJvZ3Jlc3MoKSAqIDEwMCkpO1xuXHRcdFx0YnJlYWs7XG5cdFx0Y2FzZSAncHJvZ3Jlc3MnOlxuXHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnNldFByb2dyZXNzKE1hdGguZmxvb3IoZGF0YS5wZXJjZW50KSk7XG5cdFx0XHRicmVhaztcblx0XHRjYXNlICdmaWxlU3VjY2Vzcyc6IHtcblx0XHRcdGxldCByZXNwb25zZTtcblx0XHRcdHRyeSB7XG5cdFx0XHRcdHJlc3BvbnNlID0gSlNPTi5wYXJzZShkYXRhLnJlc3BvbnNlKTtcblx0XHRcdH0gY2F0Y2ggKGVycikge1xuXHRcdFx0XHRjb25zb2xlLmVycm9yKCdVcGxvYWQgcmVzcG9uc2UgcGFyc2UgZXJyb3I6JywgZXJyKTtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXHRcdFx0Y29uc3QgZmlsZUlkID0gcmVzcG9uc2U/LmRhdGE/LnVwbG9hZF9pZCB8fCAnJztcblx0XHRcdGNvbnN0IHN0YXR1cyA9IHJlc3BvbnNlPy5kYXRhPy5kX3N0YXR1cyB8fCAnJztcblx0XHRcdGlmIChzdGF0dXMgPT09ICdNRVJHSU5HJykge1xuXHRcdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuc2V0UHJvZ3Jlc3MoOTUsICdNZXJnaW5nIGNodW5rc+KApicpO1xuXHRcdFx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUud2FpdEZvck1lcmdlKFxuXHRcdFx0XHRcdGZpbGVJZCxcblx0XHRcdFx0XHQoKSA9PiBtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUucmVnaXN0ZXJGaXJtd2FyZShmaWxlSWQpLFxuXHRcdFx0XHQpO1xuXHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnNldFByb2dyZXNzKDEwMCwgJ1JlZ2lzdGVyaW5n4oCmJyk7XG5cdFx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5yZWdpc3RlckZpcm13YXJlKGZpbGVJZCk7XG5cdFx0XHR9XG5cdFx0XHRicmVhaztcblx0XHR9XG5cdFx0Y2FzZSAnZmlsZUVycm9yJzpcblx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5zZXRQcm9ncmVzcygwLCBgVXBsb2FkIGZhaWxlZDogJHtkYXRhLm1lc3NhZ2UgfHwgJyd9YCk7XG5cdFx0XHRicmVhaztcblx0XHRkZWZhdWx0OlxuXHRcdFx0YnJlYWs7XG5cdFx0fVxuXHR9LFxuXG5cdC8qKlxuXHQgKiBQb2xscyBDb3JlJ3MgL2ZpbGVzOnVwbG9hZFN0YXR1cyB1bnRpbCB0aGUgY2h1bmsgbWVyZ2UgZm9yIHRoZSBnaXZlbiBmaWxlX2lkXG5cdCAqIHJlcG9ydHMgVVBMT0FEX0NPTVBMRVRFLCB0aGVuIGludm9rZXMgYG9uUmVhZHlgLiBBIDYwLWl0ZXJhdGlvbiBjYXAgYXQgfjFzXG5cdCAqIHBlciBwb2xsIGNvdmVycyB0aGUgODAgTUIgY2VpbGluZyAoQ29yZSBtZXJnZXMgcm91Z2hseSA1MCBNQi9zZWMgb24gc2xvd1xuXHQgKiBVU0ItYmFja2VkIGluc3RhbGxzKSB3aGlsZSBzdGlsbCBnaXZpbmcgdXAgcmF0aGVyIHRoYW4gc3Bpbm5pbmcgZm9yZXZlci5cblx0ICpcblx0ICogT24gZXJyb3Igb3IgdGltZW91dCB3ZSBzdGlsbCBpbnZva2UgYG9uUmVhZHlgIHNvIHRoZSB1c2VyIGdldHMgYSBjbGVhblxuXHQgKiA0MjUtc3R5bGUgZXJyb3IgZnJvbSBvdXIgZW5kcG9pbnQgcmF0aGVyIHRoYW4gYSBzaWxlbnQgc3R1Y2sgcHJvZ3Jlc3MgYmFyLlxuXHQgKi9cblx0d2FpdEZvck1lcmdlKGZpbGVJZCwgb25SZWFkeSwgYXR0ZW1wdCA9IDApIHtcblx0XHRpZiAoIWZpbGVJZCB8fCB0eXBlb2YgRmlsZXNBUEkgPT09ICd1bmRlZmluZWQnIHx8IHR5cGVvZiBGaWxlc0FQSS5nZXRTdGF0dXNVcGxvYWRGaWxlICE9PSAnZnVuY3Rpb24nKSB7XG5cdFx0XHRvblJlYWR5KCk7XG5cdFx0XHRyZXR1cm47XG5cdFx0fVxuXHRcdGlmIChhdHRlbXB0ID4gNjApIHtcblx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5zZXRQcm9ncmVzcyg5NSwgJ01lcmdlIHRha2luZyB0b28gbG9uZyDigJQgcHJvY2VlZGluZyBhbnl3YXknKTtcblx0XHRcdG9uUmVhZHkoKTtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cdFx0RmlsZXNBUEkuZ2V0U3RhdHVzVXBsb2FkRmlsZShmaWxlSWQsIChpbmZvKSA9PiB7XG5cdFx0XHRpZiAoaW5mbyA9PT0gZmFsc2UgfHwgIWluZm8pIHtcblx0XHRcdFx0c2V0VGltZW91dChcblx0XHRcdFx0XHQoKSA9PiBtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUud2FpdEZvck1lcmdlKGZpbGVJZCwgb25SZWFkeSwgYXR0ZW1wdCArIDEpLFxuXHRcdFx0XHRcdDEwMDAsXG5cdFx0XHRcdCk7XG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblx0XHRcdGNvbnN0IHN0YXR1cyA9IGluZm8uZF9zdGF0dXMgfHwgJyc7XG5cdFx0XHRpZiAoc3RhdHVzID09PSAnVVBMT0FEX0NPTVBMRVRFJyB8fCBzdGF0dXMgPT09ICdNRVJHRUQnKSB7XG5cdFx0XHRcdG9uUmVhZHkoKTtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXHRcdFx0aWYgKGluZm8uZF9zdGF0dXNfcHJvZ3Jlc3MpIHtcblx0XHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnNldFByb2dyZXNzKDk1LCBgTWVyZ2luZ+KApiAke2luZm8uZF9zdGF0dXNfcHJvZ3Jlc3N9JWApO1xuXHRcdFx0fVxuXHRcdFx0c2V0VGltZW91dChcblx0XHRcdFx0KCkgPT4gbW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLndhaXRGb3JNZXJnZShmaWxlSWQsIG9uUmVhZHksIGF0dGVtcHQgKyAxKSxcblx0XHRcdFx0MTAwMCxcblx0XHRcdCk7XG5cdFx0fSk7XG5cdH0sXG5cblx0cmVnaXN0ZXJGaXJtd2FyZShmaWxlSWQpIHtcblx0XHRpZiAoIWZpbGVJZCkge1xuXHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnNldFByb2dyZXNzKDAsICdNaXNzaW5nIGZpbGVfaWQgZnJvbSBDb3JlIHVwbG9hZCcpO1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblx0XHRjb25zdCBwYXlsb2FkID0ge1xuXHRcdFx0ZmlsZV9pZDogZmlsZUlkLFxuXHRcdFx0dmVuZG9yOiBtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuJHZlbmRvci52YWwoKSB8fCAnJyxcblx0XHRcdG1vZGVsOiBtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuJG1vZGVsLnZhbCgpIHx8ICcnLFxuXHRcdFx0dmVyc2lvbjogbW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLiR2ZXJzaW9uLnZhbCgpIHx8ICcnLFxuXHRcdFx0bm90ZXM6IG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS4kbm90ZXMudmFsKCkgfHwgJycsXG5cdFx0fTtcblx0XHQkLmFwaSh7XG5cdFx0XHR1cmw6IGAke21vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5iYXNlUGF0aH06dXBsb2FkYCxcblx0XHRcdG1ldGhvZDogJ1BPU1QnLFxuXHRcdFx0ZGF0YTogcGF5bG9hZCxcblx0XHRcdG9uOiAnbm93Jyxcblx0XHRcdG9uU3VjY2VzcyhyZXNwb25zZSkge1xuXHRcdFx0XHRpZiAocmVzcG9uc2U/LnJlc3VsdCA9PT0gdHJ1ZSkge1xuXHRcdFx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5zZXRQcm9ncmVzcygxMDAsICdSZWdpc3RlcmVkJyk7XG5cdFx0XHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLnJlZnJlc2goKTtcblx0XHRcdFx0fSBlbHNlIHtcblx0XHRcdFx0XHRjb25zdCBlcnIgPSByZXNwb25zZT8ubWVzc2FnZXM/LmVycm9yPy5bMF0gfHwgJ1JlZ2lzdHJhdGlvbiBmYWlsZWQnO1xuXHRcdFx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5zZXRQcm9ncmVzcygwLCBlcnIpO1xuXHRcdFx0XHR9XG5cdFx0XHR9LFxuXHRcdFx0b25GYWlsdXJlKHJlc3BvbnNlKSB7XG5cdFx0XHRcdGNvbnN0IGVyciA9IHJlc3BvbnNlPy5tZXNzYWdlcz8uZXJyb3I/LlswXSB8fCAnUmVnaXN0cmF0aW9uIGZhaWxlZCc7XG5cdFx0XHRcdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5zZXRQcm9ncmVzcygwLCBlcnIpO1xuXHRcdFx0fSxcblx0XHR9KTtcblx0fSxcblxuXHRzZXRQcm9ncmVzcyhwZXJjZW50LCBsYWJlbCkge1xuXHRcdGlmICghbW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLiRwcm9ncmVzcyB8fCBtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuJHByb2dyZXNzLmxlbmd0aCA9PT0gMCkge1xuXHRcdFx0cmV0dXJuO1xuXHRcdH1cblx0XHRtb2R1bGVBdXRvcHJvdmlzaW9uRmlybXdhcmUuJHByb2dyZXNzQmFyLmNzcygnd2lkdGgnLCBgJHtwZXJjZW50fSVgKTtcblx0XHRpZiAobGFiZWwgIT09IHVuZGVmaW5lZCkge1xuXHRcdFx0bW9kdWxlQXV0b3Byb3Zpc2lvbkZpcm13YXJlLiRwcm9ncmVzcy5maW5kKCcubGFiZWwnKS50ZXh0KGxhYmVsKTtcblx0XHR9XG5cdH0sXG59O1xuXG4kKGRvY3VtZW50KS5yZWFkeSgoKSA9PiB7XG5cdG1vZHVsZUF1dG9wcm92aXNpb25GaXJtd2FyZS5pbml0aWFsaXplKCk7XG59KTtcbiJdfQ==