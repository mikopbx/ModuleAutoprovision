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
const moduleAutoprovisionFirmware = {
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

	initialize() {
		const $tab = $('a.item[data-tab="firmware"]');
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
		moduleAutoprovisionFirmware.$notes = $('#firmware-notes');

		// Load list when the tab is first opened — the device/template tabs are
		// the common entry points, so paying the round-trip up front would slow
		// the page for admins who never touch firmware.
		$tab.on('click', () => {
			if (!moduleAutoprovisionFirmware.loaded) {
				moduleAutoprovisionFirmware.refresh();
				moduleAutoprovisionFirmware.loaded = true;
			}
		});

		moduleAutoprovisionFirmware.initializeUpload();
		moduleAutoprovisionFirmware.bindRowActions();

		// Semantic UI styling for the vendor select. .val() reads the underlying
		// <select>, so this only changes how it looks, not how we read it.
		moduleAutoprovisionFirmware.$vendor.dropdown();
	},

	refresh() {
		$.api({
			url: moduleAutoprovisionFirmware.basePath,
			method: 'GET',
			on: 'now',
			onSuccess(response) {
				const items = response?.data?.items || [];
				const totals = response?.data?.totals || {};
				moduleAutoprovisionFirmware.renderTable(items);
				moduleAutoprovisionFirmware.renderTotals(totals);
			},
			onFailure(response) {
				moduleAutoprovisionFirmware.renderTable([]);
				console.error('Failed to list firmware:', response);
			},
		});
	},

	renderTable(items) {
		const $tbody = moduleAutoprovisionFirmware.$tableBody;
		$tbody.empty();
		if (items.length === 0) {
			$tbody.append(
				'<tr><td colspan="7" class="center aligned">'
					+ '<i class="info circle icon"></i> No firmware uploaded yet.'
					+ '</td></tr>',
			);
			return;
		}
		items.forEach((item) => {
			const sha = (item.sha256 || '').substring(0, 12);
			const sizeMb = (item.size / (1024 * 1024)).toFixed(2);
			const $row = $('<tr>')
				.attr('data-firmware-id', item.id)
				.attr('data-firmware-vendor', item.vendor || '')
				.attr('data-firmware-model', item.model || '')
				.attr('data-firmware-version', item.version || '')
				.attr('data-firmware-notes', item.notes || '')
				.append($('<td>').text(item.vendor || ''))
				.append($('<td>').text(item.model || '*'))
				.append($('<td>').text(item.filename || ''))
				.append($('<td>').text(item.version || ''))
				.append($('<td>').text(`${sizeMb} MB`))
				.append($('<td>').attr('title', item.sha256 || '').text(sha))
				.append(
					$('<td class="right aligned collapsing">')
						.append(
							// Semantic UI "buttons" group renders the actions as a single
							// connected, monochrome bar — keeps the row visually tidy and
							// avoids the rainbow of per-action colours we had before.
							// Download stays an <a> so middle-click / "open in new tab"
							// still works; the rest are <button> because they trigger
							// JS-driven flows (modal, file picker, confirm).
							$('<div class="ui small basic icon buttons">')
								.append(
									$('<a class="ui button firmware-download">')
										.attr('href', item.url || '#')
										.attr('target', '_blank')
										.attr('title', 'Download')
										.html('<i class="download icon"></i>'),
								)
								.append(
									$('<button class="ui button firmware-edit" type="button">')
										.attr('data-firmware-id', item.id)
										.attr('title', 'Edit metadata')
										.html('<i class="edit icon"></i>'),
								)
								.append(
									$('<button class="ui button firmware-replace" type="button">')
										.attr('data-firmware-id', item.id)
										.attr('title', 'Replace file')
										.html('<i class="sync icon"></i>'),
								)
								.append(
									$('<button class="ui button firmware-delete" type="button">')
										.attr('data-firmware-id', item.id)
										.attr('title', 'Delete')
										.html('<i class="trash icon"></i>'),
								),
						),
				);
			$tbody.append($row);
		});
	},

	renderTotals(totals) {
		const used = totals.used || 0;
		const cap = totals.used_cap || 0;
		const fileCap = totals.file_cap || 0;
		const usedMb = (used / (1024 * 1024)).toFixed(1);
		const capMb = (cap / (1024 * 1024)).toFixed(0);
		const fileCapMb = (fileCap / (1024 * 1024)).toFixed(0);
		moduleAutoprovisionFirmware.$totals.text(
			`${usedMb} MB / ${capMb} MB used — single file cap ${fileCapMb} MB`,
		);
	},

	bindRowActions() {
		const $body = $('body');

		$body.on('click', '.firmware-delete', function handleDelete(e) {
			e.preventDefault();
			const id = $(this).attr('data-firmware-id');
			if (!id) {
				return;
			}
			$.api({
				url: `${moduleAutoprovisionFirmware.basePath}/${id}`,
				method: 'DELETE',
				on: 'now',
				onSuccess() {
					moduleAutoprovisionFirmware.refresh();
				},
				onFailure(response) {
					console.error('Failed to delete firmware:', response);
				},
			});
		});

		$body.on('click', '.firmware-edit', function handleEdit(e) {
			e.preventDefault();
			const $row = $(this).closest('tr');
			moduleAutoprovisionFirmware.openEditModal({
				id: $row.attr('data-firmware-id'),
				vendor: $row.attr('data-firmware-vendor'),
				model: $row.attr('data-firmware-model'),
				version: $row.attr('data-firmware-version'),
				notes: $row.attr('data-firmware-notes'),
			});
		});

		$body.on('click', '#firmware-edit-save', () => {
			moduleAutoprovisionFirmware.submitEditModal();
		});

		// "Replace file" opens a hidden file picker pointed at this row id.
		$body.on('click', '.firmware-replace', function handleReplace(e) {
			e.preventDefault();
			moduleAutoprovisionFirmware.replaceTargetId = $(this).attr('data-firmware-id');
			moduleAutoprovisionFirmware.triggerReplacePicker();
		});
	},

	openEditModal(item) {
		const $modal = $('#firmware-edit-modal');
		$modal.find('#firmware-edit-id').val(item.id || '');
		$modal.find('#firmware-edit-vendor').val(item.vendor || '');
		$modal.find('#firmware-edit-model').val(item.model || '');
		$modal.find('#firmware-edit-version').val(item.version || '');
		$modal.find('#firmware-edit-notes').val(item.notes || '');
		$modal.find('#firmware-edit-vendor').dropdown('refresh');
		$modal.modal('show');
	},

	submitEditModal() {
		const $modal = $('#firmware-edit-modal');
		const id = $modal.find('#firmware-edit-id').val();
		if (!id) {
			return;
		}
		const payload = {
			vendor: $modal.find('#firmware-edit-vendor').val(),
			model: $modal.find('#firmware-edit-model').val(),
			version: $modal.find('#firmware-edit-version').val(),
			notes: $modal.find('#firmware-edit-notes').val(),
		};
		$.api({
			url: `${moduleAutoprovisionFirmware.basePath}/${id}`,
			method: 'PATCH',
			data: payload,
			on: 'now',
			onSuccess(response) {
				if (response?.result === true) {
					$modal.modal('hide');
					moduleAutoprovisionFirmware.refresh();
				} else {
					const err = response?.messages?.error?.[0] || 'Update failed';
					$modal.find('#firmware-edit-error').text(err).show();
				}
			},
			onFailure(response) {
				const err = response?.messages?.error?.[0] || 'Update failed';
				$modal.find('#firmware-edit-error').text(err).show();
			},
		});
	},

	triggerReplacePicker() {
		// Lazy-create a single hidden <input type="file"> the first time around;
		// Resumable.js attaches its own change handler when we assignBrowse() it.
		let input = document.getElementById('firmware-replace-input');
		if (!input) {
			input = document.createElement('input');
			input.type = 'file';
			input.id = 'firmware-replace-input';
			input.style.display = 'none';
			document.body.appendChild(input);

			const config = FilesAPI.configureResumable({
				target: `${Config.pbxUrl}/pbxcore/api/v3/files:upload`,
				fileType: ['rom', 'bin', 'fw', 'z'],
				maxFileSize: 80 * 1024 * 1024,
				query() {
					return { category: 'autoprovision-firmware' };
				},
			});
			const resumable = new Resumable(config);
			if (!resumable.support) {
				console.error('Resumable.js not supported');
				return;
			}
			resumable.assignBrowse(input);
			FilesAPI.setupResumableEvents(
				resumable,
				(event, data) => moduleAutoprovisionFirmware.handleReplaceUploadEvent(event, data),
				true,
			);
			moduleAutoprovisionFirmware.replaceResumables.default = resumable;
		}
		input.click();
	},

	handleReplaceUploadEvent(event, data) {
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
		case 'fileSuccess': {
			let response;
			try {
				response = JSON.parse(data.response);
			} catch (err) {
				console.error('Replace response parse error:', err);
				return;
			}
			const fileId = response?.data?.upload_id || '';
			const status = response?.data?.d_status || '';
			if (status === 'MERGING') {
				moduleAutoprovisionFirmware.setProgress(95, 'Merging chunks…');
				moduleAutoprovisionFirmware.waitForMerge(
					fileId,
					() => moduleAutoprovisionFirmware.callReplace(fileId),
				);
			} else {
				moduleAutoprovisionFirmware.setProgress(100, 'Replacing…');
				moduleAutoprovisionFirmware.callReplace(fileId);
			}
			break;
		}
		case 'fileError':
			moduleAutoprovisionFirmware.setProgress(0, `Replace failed: ${data.message || ''}`);
			break;
		default:
			break;
		}
	},

	callReplace(fileId) {
		const id = moduleAutoprovisionFirmware.replaceTargetId;
		if (!id || !fileId) {
			moduleAutoprovisionFirmware.setProgress(0, 'Replace cancelled — missing id or file_id');
			return;
		}
		$.api({
			url: `${moduleAutoprovisionFirmware.basePath}/${id}`,
			method: 'PUT',
			data: { file_id: fileId },
			on: 'now',
			onSuccess(response) {
				if (response?.result === true) {
					moduleAutoprovisionFirmware.setProgress(100, 'Replaced');
					moduleAutoprovisionFirmware.refresh();
				} else {
					const err = response?.messages?.error?.[0] || 'Replace failed';
					moduleAutoprovisionFirmware.setProgress(0, err);
				}
			},
			onFailure(response) {
				const err = response?.messages?.error?.[0] || 'Replace failed';
				moduleAutoprovisionFirmware.setProgress(0, err);
			},
		});
	},

	initializeUpload() {
		if (typeof FilesAPI === 'undefined' || typeof Resumable === 'undefined') {
			return;
		}
		const config = FilesAPI.configureResumable({
			target: `${Config.pbxUrl}/pbxcore/api/v3/files:upload`,
			fileType: ['rom', 'bin', 'fw', 'z'],
			// 80 MB per file — server enforces, but we short-circuit on the client too.
			maxFileSize: 80 * 1024 * 1024,
			query() {
				return { category: 'autoprovision-firmware' };
			},
		});

		const resumable = new Resumable(config);
		if (!resumable.support) {
			console.error('Resumable.js is not supported');
			return;
		}
		const dropEl = document.getElementById('firmware-dropzone');
		const browseEl = document.getElementById('firmware-browse');
		if (dropEl) {
			resumable.assignDrop(dropEl);
		}
		if (browseEl) {
			resumable.assignBrowse(browseEl);
		}

		FilesAPI.setupResumableEvents(
			resumable,
			(event, data) => moduleAutoprovisionFirmware.handleUploadEvent(event, data),
			true,
		);
		moduleAutoprovisionFirmware.resumable = resumable;
	},

	handleUploadEvent(event, data) {
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
		case 'fileSuccess': {
			let response;
			try {
				response = JSON.parse(data.response);
			} catch (err) {
				console.error('Upload response parse error:', err);
				return;
			}
			const fileId = response?.data?.upload_id || '';
			const status = response?.data?.d_status || '';
			// Capture the browser-supplied filename before Core's merge step
			// drops dots from the canonical name on disk. This is what we send
			// to /firmware:upload as `original_filename` so the registered row
			// keeps "T48S-66.86.0.11.rom" instead of "T48S-668601".
			const originalName = (data?.file?.fileName)
				|| (data?.file?.file?.name)
				|| '';
			if (status === 'MERGING') {
				moduleAutoprovisionFirmware.setProgress(95, 'Merging chunks…');
				moduleAutoprovisionFirmware.waitForMerge(
					fileId,
					() => moduleAutoprovisionFirmware.registerFirmware(fileId, originalName),
				);
			} else {
				moduleAutoprovisionFirmware.setProgress(100, 'Registering…');
				moduleAutoprovisionFirmware.registerFirmware(fileId, originalName);
			}
			break;
		}
		case 'fileError':
			moduleAutoprovisionFirmware.setProgress(0, `Upload failed: ${data.message || ''}`);
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
	waitForMerge(fileId, onReady, attempt = 0) {
		if (!fileId || typeof FilesAPI === 'undefined' || typeof FilesAPI.getStatusUploadFile !== 'function') {
			onReady();
			return;
		}
		if (attempt > 60) {
			moduleAutoprovisionFirmware.setProgress(95, 'Merge taking too long — proceeding anyway');
			onReady();
			return;
		}
		FilesAPI.getStatusUploadFile(fileId, (info) => {
			if (info === false || !info) {
				setTimeout(
					() => moduleAutoprovisionFirmware.waitForMerge(fileId, onReady, attempt + 1),
					1000,
				);
				return;
			}
			const status = info.d_status || '';
			if (status === 'UPLOAD_COMPLETE' || status === 'MERGED') {
				onReady();
				return;
			}
			if (info.d_status_progress) {
				moduleAutoprovisionFirmware.setProgress(95, `Merging… ${info.d_status_progress}%`);
			}
			setTimeout(
				() => moduleAutoprovisionFirmware.waitForMerge(fileId, onReady, attempt + 1),
				1000,
			);
		});
	},

	registerFirmware(fileId, originalName = '') {
		if (!fileId) {
			moduleAutoprovisionFirmware.setProgress(0, 'Missing file_id from Core upload');
			return;
		}
		const payload = {
			file_id: fileId,
			vendor: moduleAutoprovisionFirmware.$vendor.val() || '',
			model: moduleAutoprovisionFirmware.$model.val() || '',
			version: moduleAutoprovisionFirmware.$version.val() || '',
			notes: moduleAutoprovisionFirmware.$notes.val() || '',
		};
		// Forward the browser-supplied filename so Core's dot-stripping merge
		// can't lose the firmware extension or version string. UploadAction
		// falls back to the merged basename when this is absent.
		if (originalName) {
			payload.original_filename = originalName;
		}
		$.api({
			url: `${moduleAutoprovisionFirmware.basePath}:upload`,
			method: 'POST',
			data: payload,
			on: 'now',
			onSuccess(response) {
				if (response?.result === true) {
					moduleAutoprovisionFirmware.setProgress(100, 'Registered');
					moduleAutoprovisionFirmware.refresh();
				} else {
					const err = response?.messages?.error?.[0] || 'Registration failed';
					moduleAutoprovisionFirmware.setProgress(0, err);
				}
			},
			onFailure(response) {
				const err = response?.messages?.error?.[0] || 'Registration failed';
				moduleAutoprovisionFirmware.setProgress(0, err);
			},
		});
	},

	setProgress(percent, label) {
		if (!moduleAutoprovisionFirmware.$progress || moduleAutoprovisionFirmware.$progress.length === 0) {
			return;
		}
		moduleAutoprovisionFirmware.$progressBar.css('width', `${percent}%`);
		if (label !== undefined) {
			moduleAutoprovisionFirmware.$progress.find('.label').text(label);
		}
	},
};

$(document).ready(() => {
	moduleAutoprovisionFirmware.initialize();
});
