/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/
(function($) {

	$.fn.jtfUploadFile = function(optionlist) {
		var containerId  = this.selector,
			dragZone     = $('#' + containerId + ' .dragarea'),
			fileInput    = $('#' + optionlist.id),
			uploadList   = $('#' + containerId + ' .upload-list'),
			label        = $('label[for="' + optionlist.id + '"'),
			maxsize      = optionlist.uploadMaxSize,
			errorMessage = optionlist.errorMessage;

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

		function returnFileSize(number) {
			if(number < 1024) {
				return number + ' bytes';
			} else if(number > 1024 && number < 1048576) {
				return (number/1024).toFixed(1) + ' KB';
			} else if(number > 1048576) {
				return (number/1048576).toFixed(1) + ' MB';
			}
		}
		function dateiauswahl(files) {
			var output      = [],
				uploadError = '',
				uploadsize  = getFilesize(files);

			console.log(files);
			console.log(uploadsize);

			for (var i = 0, f; f = files[i]; i++) {
				//uploadsize += f.size;
				output.push('<li><strong>', f.name, '</strong> (',
					returnFileSize(f.size), ')</li>');
			}

			if (uploadsize > maxsize) {
				uploadError = '<p><strong>Upload: ' + returnFileSize(uploadsize)
					+ '</strong> - ' + errorMessage + '</p>';
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

		fileInput.on('drag dragstart dragend dragover dragenter dragleave change', function(e) {
			e.preventDefault();
			e.stopPropagation();
		}).on('dragenter dragover', function(e) {
			console.log('dragenter / dragover');
			dragZone.addClass('hover');
		}).on('dragleave dragend drop', function(e) {
			console.log('dragleave / dragend / drop');
			dragZone.removeClass('hover');
		}).on('change', function(e) {
			console.log('change');
			dateiauswahl(e.originalEvent.target.files);
		}).on('click', function() {
			console.log('fileInputClick');
		});

	};
})(jQuery);
