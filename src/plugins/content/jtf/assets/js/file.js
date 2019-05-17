/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 **/

jQuery(document).ready(function ($) {
	$.fn.jtfUploadFile = function (optionlist) {
		var $dragZone = this.find('.dragarea'),
			$allowedExt = this.find('.allowedExt'),
			$fileInput = $('#' + optionlist.id),
			$uploadList = this.find('.upload-list'),
			$label = $('label[for="' + optionlist.id + '"'),
			accept = $fileInput.attr('accept').split(','),
			maxsize = optionlist.uploadMaxSize,
			msgAllowedExt = Joomla.JText._('JTF_UPLOAD_ALLOWED_FILES_EXT', ''),
			errorFileSize = Joomla.JText._('JTF_UPLOAD_ERROR_MESSAGE_SIZE', ''),
			errorFileType = Joomla.JText._('JTF_UPLOAD_ERROR_FILE_NOT_ALLOWED', '');

		// Set list of mimetype and file extension
		for (var i = 0, acceptedType = '', acceptedExt = '', sep = ''; i < accept.length; ++i) {
			var mimeType = mimelite.getType(accept[i].replace(".", ""));
			var fileExt = mimelite.getExtension(accept[i].replace(".", ""));

			if (mimeType === null) {
				mimeType = mimelite.getTypes(accept[i].replace(".", ""));
			}

			if (fileExt === null) {
				fileExt = accept[i].replace(".", "");
			}

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

		acceptedExt = '.' + acceptedExt.replace(/,/g, " .");
		acceptedType = acceptedType.replace(/,/g, " ");

		$allowedExt.html(msgAllowedExt + acceptedExt);

		function allowedFile(fileType) {
			if (fileType) {
				var patt = new RegExp(fileType);

				if (patt.test(acceptedType)) {
					return true;
				}
			}

			return false;
		}

		function getTotalFilesSize(files) {
			var size = 0;

			for (var i = 0, f; f = files[i]; i++) {
				size += f.size;
			}

			return size;
		}

		function setInvalid() {
			$label.addClass('invalid').attr('aria-invalid', true);
			$dragZone.addClass('invalid').attr('aria-invalid', true);
			$fileInput.addClass('invalid').attr('aria-invalid', true);
		}

		function unsetInvalid() {
			$label.removeClass('invalid').attr('aria-invalid', false);
			$dragZone.removeClass('invalid').attr('aria-invalid', false);
			$fileInput.removeClass('invalid').attr('aria-invalid', false);
		}

		function humanReadableSize(bytes, si) {
			var thresh = si ? 1000 : 1024;

			if (Math.abs(bytes) < thresh) {
				return bytes + ' B';
			}

			var units = si ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'] : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'],
				u = -1;

			do {
				bytes /= thresh;
				++u;
			} while (Math.abs(bytes) >= thresh && u < units.length - 1);

			return bytes.toFixed(1) + ' ' + units[u];
		}

		function dateiauswahl(files) {
			var output = [],
				uploadError = '',
				uploadsize = getTotalFilesSize(files);

			for (var i = 0, f; f = files[i]; i++) {
				if (allowedFile(f.type)) {
					output.push('<li><strong>Upload: ', f.name, '</strong> (', humanReadableSize(f.size, true), ')</li>');
				} else {
					uploadError += '<p><strong>Error: ' + f.name + '</strong> - ' + errorFileType + '</p>';
				}
			}

			if (uploadsize > maxsize) {
				uploadError += '<p><strong>Error: ' + humanReadableSize(uploadsize, true) + '</strong> - ' + errorFileSize + '</p>';
			}

			if (uploadError) {
				setInvalid();
				document.formvalidator.setHandler('file', function () {
					return false;
				});
			} else {
				unsetInvalid();
				document.formvalidator.setHandler('file', function () {
					return true;
				});
			}

			$uploadList.html(uploadError + '<ul style="text-align: left;">' + output.join('') + '</ul>');
		}

		$fileInput.on('drag dragstart dragend dragover dragenter dragleave change', function (e) {
			e.preventDefault();
			e.stopPropagation();
		}).on('dragenter dragover', function (e) {
			$dragZone.addClass('hover');
		}).on('dragleave dragend drop', function (e) {
			$dragZone.removeClass('hover');
		}).on('change', function (e) {
			dateiauswahl(e.originalEvent.target.files);
		});
	};

	$('.uploader-wrapper').each(function () {
		$(this).jtfUploadFile({
			id: $(this).find('.legacy-uploader input[type="file"]').attr('id'),
			uploadMaxSize: $(this).find('.legacy-uploader input[type="hidden"]').attr('value')
		});
	});
});