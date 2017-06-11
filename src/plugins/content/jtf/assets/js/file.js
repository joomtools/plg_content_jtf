/**
 * @package      Joomla.Plugin
 * @subpackage   Content.jtf
 *
 * @author       Guido De Gobbis
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/
(function($) {

	$.fn.jtfUploadFile = function(optionlist) {
		var containerId  = this.selector,
			dragZone     = $('#' + containerId + ' .dragarea'),
			fileInput    = $('#' + optionlist.id),
			button       = $('#' + containerId + ' .select-file-button'),
			uploadList   = $('#' + containerId + ' .upload-list'),
			label        = $('label[for="' + optionlist.id + '"'),
			maxsize      = optionlist.uploadMaxSize,
			errorMessage = optionlist.errorMessage;

		$('#' + containerId + ' .legacy-uploader').hide();
		$('#' + containerId + ' .dragarea').show();

		if (typeof FormData == 'undefined') {
			$('#' + containerId + ' .legacy-uploader').show();
			$('#' + containerId + ' .dragarea').hide();
		}

		function getFilesize(files) {
			var size = 0;

			for (var i = 0, f; f = files[i]; i++) {
				size += f.size;
			}

			return size;
		}

		function setInvalid() {
			label.addClass('invalid').attr('aria-invalid', true);
			dragZone.addClass('invalid').attr('aria-invalid', true);
			fileInput.addClass('invalid').attr('aria-invalid', true);
		}

		function unsetInvalid() {
			label.removeClass('invalid').attr('aria-invalid', false);
			dragZone.removeClass('invalid').attr('aria-invalid', false);
			fileInput.removeClass('invalid').attr('aria-invalid', false);
		}

		function dateiauswahl(evt) {
			var files = evt.originalEvent.target.files || evt.originalEvent.dataTransfer.files,
				output      = [],
				uploadError = '',
				uploadsize  = getFilesize(files);

			console.log(files);
			for (var i = 0, f; f = files[i]; i++) {
				//uploadsize += f.size;
				output.push('<li><strong>', f.name, '</strong> (',
					Math.round((f.size / 1024 / 1024) * 100) / 100, ' MB)</li>');
			}

			if (uploadsize > maxsize) {
				uploadError = '<p><strong>Upload: ' + Math.round((uploadsize / 1024 / 1024) * 100) / 100
					+ ' MB</strong> - ' + errorMessage + '</p>';
				setInvalid();
				document.formvalidator.setHandler('file', function() {
					return false;
				});
			} else {
				unsetInvalid();
				document.formvalidator.setHandler('file', function() {
					return true;
				});
			}

			uploadList.html(uploadError + '<ul style="text-align: left;">' + output.join('') + '</ul>');
		}

		fileInput.on('change', dateiauswahl);

		button.on('click', function() {
			fileInput.click();
		});

		dragZone.on('dragenter dragover dragleave drop', function(e) {
			e.preventDefault();
			e.stopPropagation();
		}).on('dragenter dragover', function() {
			dragZone.addClass('hover');
		}).on('dragleave drop', function() {
			dragZone.removeClass('hover');
		}).on('drop', dateiauswahl);
	};
})(jQuery);
