/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

"use strict";

let jtfUploadFile = (elm, optionlist) => {
	console.log('elm', elm);
	console.log('optionlist', optionlist);

	let humanReadableSize = (bytes, si = true) => {
		let thresh = si ? 1000 : 1024;

		if (Math.abs(bytes) < thresh) {
			return bytes + ' B';
		}

		let units = si ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'] : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'],
			u = -1;

		do {
			bytes /= thresh;
			++u;
		} while (Math.abs(bytes) >= thresh && u < units.length - 1);

		return bytes.toFixed(1) + ' ' + units[u];
	};

	let Joomla = window.Joomla || {},
		mimelite = window.mimelite || {},
		acceptedType = '',
		acceptedExt = '';

	const allowedExt = elm.querySelector('.allowedExt'),
		dragarea = elm.querySelector('.dragarea'),
		fileInput = document.querySelector('#' + optionlist.id),
		uploadList = elm.querySelector('.upload-list'),
		label = document.querySelector('label[for="' + optionlist.id + '"'),
		accept = fileInput.getAttribute('accept').split(','),
		maxsize = optionlist.uploadMaxSize,
		msgAllowedExt = Joomla.Text._('JTF_JS_UPLOAD_ALLOWED_FILES_EXT', ''),
		errorFileSize = Joomla.Text._('JTF_JS_UPLOAD_ERROR_MESSAGE_SIZE', '').replace('%s', humanReadableSize(maxsize)),
		errorFileType = Joomla.Text._('JTF_JS_UPLOAD_ERROR_FILE_NOT_ALLOWED', '');

	console.log('JS TEXT: msgAllowedExt', msgAllowedExt);
	console.log('JS TEXT: errorFileSize', errorFileSize);
	console.log('JS TEXT: errorFileType', errorFileType);

	console.log('allowedExt', allowedExt);
	console.log('dragarea', dragarea);
	console.log('fileInput', fileInput);
	console.log('uploadList', uploadList);
	console.log('label', label);
	console.log('accept', accept);
	console.log('maxsize', maxsize);

	// Set list of mimetype and file extension
	for (let i = 0, sep = ''; i < accept.length; ++i) {
		let mimeType = mimelite.getType(accept[i]),
			fileExt = mimelite.getExtension(accept[i]);

		if (mimeType === null) {
			mimeType = mimelite.getTypes(accept[i]);
		}

		console.log('mimeType', mimeType);

		if (fileExt === null) {
			fileExt = accept[i].replace('.', '');
		}

		console.log('fileExt', fileExt);

		if (fileExt !== null) {
			sep = acceptedExt ? ',' : '';
			acceptedExt += sep + fileExt;

			if (fileExt === 'zip') {
				acceptedType += sep + 'application/x-zip-compressed';
			}
		}

		if (mimeType !== null) {
			sep = acceptedType ? ',' : '';
			acceptedType += sep + mimeType;
		}
	}

	acceptedExt = acceptedExt.replace(/,/g, ' .');
	acceptedType = acceptedType.replace(/,/g, ' ');

	allowedExt.innerHTML = msgAllowedExt + acceptedExt;

	let allowedFile = (fileType) => {
		if (fileType) {
			let patt = new RegExp(fileType);

			if (patt.test(acceptedType)) {
				return true;
			}
		}

		return false;
	};

	let getTotalFilesSize = (files) => {
		let size = 0;

		for (let i = 0, f; i < files.length; i++) {
			f = files[i];
			size += f.size;
		}

		return size;
	};

	let setInvalid = () => {
		label.classList.add('invalid');
		label.setAttribute('aria-invalid', true);

		if (dragarea !== null) {
			dragarea.classList.add('invalid');
		}

		fileInput.classList.add('invalid');
		fileInput.setAttribute('aria-invalid', true);
	};

	let unsetInvalid = () => {
		label.classList.remove('invalid');
		label.setAttribute('aria-invalid', false);

		if (dragarea !== null) {
			dragarea.classList.remove('invalid');
		}

		fileInput.classList.remove('invalid');
		fileInput.setAttribute('aria-invalid', false);
	};

	let dateiauswahl = (elm) => {
		let files = {},
			output = [],
			uploadError = [],
			uploadListWrapper = '',
			errorWrapper = '';

		if (typeof elm.originalEvent !== 'undefined') {
			files = elm.originalEvent.target.files;
		} else {
			files = elm.target.files;
		}

		console.log('dateiauswahl -> files', files);

		let uploadsize = getTotalFilesSize(files);

		console.log('dateiauswahl -> uploadsize', uploadsize);

		for (let i = 0, c = 1, f; i < files.length; i++, c++) {
			f = files[i];
			let filename = '<strong>' + f.name + '</strong> ';
			output.push('<li>' + filename + '(' + humanReadableSize(f.size) + ')</li>');

			if (!allowedFile(f.type)) {
				uploadError.push(filename + '- ' + errorFileType);
			}
		}

		if (uploadsize > maxsize) {
			uploadError.push('<li><strong>' + humanReadableSize(uploadsize) + '</strong> - ' + errorFileSize + '</li>');
		}

		console.log('output', output);
		console.log('uploadError', uploadError);

		if (output.length > 0) {
			uploadListWrapper = Joomla.Text._('JTF_JS_UPLOAD_LIST_WRAPPER_' + jtfFrwk, '').replace('%s', '<ol>' + output.join('') + '</ol>');
			unsetInvalid();
			document.formvalidator.setHandler('file', () => {
				return true;
			});
		}

		if (uploadError.length > 0) {
			errorWrapper = Joomla.Text._('JTF_JS_ERROR_WRAPPER_' + jtfFrwk, '').replace('%s', '<ul>' + uploadError.join('') + '</ul>');
			setInvalid();
			document.formvalidator.setHandler('file', () => {
				return false;
			});
		}

		console.log('errorWrapper', errorWrapper);
		console.log('uploadListWrapper', uploadListWrapper);

		uploadList.innerHTML = errorWrapper + uploadListWrapper;
	};

	if (label && dragarea) {
		console.info('label Eventhandler aufgerufen.');
		let prevClassState = label.classList.contains('invalid'),
			labelClassObserver = new MutationObserver((mutations) => {
				mutations.forEach((mutation) => {
					if (mutation.attributeName === "class") {
						let currentClassState = mutation.target.classList.contains('invalid');
						if (prevClassState !== currentClassState) {
							prevClassState = currentClassState;
							if (currentClassState) {
								console.log("class added!");
								setInvalid();
							} else {
								console.log("class removed!");
								unsetInvalid();
							}
						}
					}
				});
			});

		labelClassObserver.observe(label, {attributes: true});
	}

	if (typeof fileInput !== 'undefined') {
		console.info('fileInput Eventhandler aufgerufen.');
		fileInput.addEventListener('change', (e) => {
			e.preventDefault();
			e.stopPropagation();
			dateiauswahl(e);
		}, false);
	}
};

let jtfFrwk = window.jtfFrwk || 'BS2';

document.addEventListener('DOMContentLoaded', () => {
	let uploaderWrapper = document.querySelectorAll('.uploader-wrapper');

	console.log('uploaderWrapper', uploaderWrapper);

	Array.prototype.forEach.call(uploaderWrapper, (elm) => {
		jtfUploadFile(elm, {
			id: elm.querySelector('input[type="file"].file-uplaoder').getAttribute('id'),
			uploadMaxSize: elm.querySelector('input[type="hidden"].file-uplaoder').getAttribute('value')
		});
	});
});
