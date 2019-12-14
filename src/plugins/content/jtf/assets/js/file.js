"use strict";

/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2019 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 **/
domIsReady(function () {
  var jtfUploadFile = function jtfUploadFile(elm, optionlist) {
    var acceptedType = '',
        acceptedExt = '';

    var dragZone = elm.querySelector('.dragarea'),
        allowedExt = elm.querySelector('.allowedExt'),
        fileInput = document.querySelector('#' + optionlist.id),
        uploadList = elm.querySelector('.upload-list'),
        label = document.querySelector('label[for="' + optionlist.id + '"'),
        accept = fileInput.getAttribute('accept').split(','),
        maxsize = optionlist.uploadMaxSize,
        msgAllowedExt = Joomla.JText._('JTF_UPLOAD_ALLOWED_FILES_EXT', ''),
        errorFileSize = Joomla.JText._('JTF_UPLOAD_ERROR_MESSAGE_SIZE', ''),
        errorFileType = Joomla.JText._('JTF_UPLOAD_ERROR_FILE_NOT_ALLOWED', ''); // Set list of mimetype and file extension


    for (var i = 0, sep = ''; i < accept.length; ++i) {
      var mimeType = mimelite.getType(accept[i].replace(".", "")),
          fileExt = mimelite.getExtension(accept[i].replace(".", ""));

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
    allowedExt.innerHTML = msgAllowedExt + acceptedExt;

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

      for (var _i = 0, f = files[_i]; _i < files.length; _i++) {
        size += f.size;
      }

      return size;
    }

    function setInvalid() {
      label.classList.add('invalid');
      label.setAttribute('aria-invalid', true);
      dragZone.classList.add('invalid');
      dragZone.setAttribute('aria-invalid', true);
      fileInput.classList.add('invalid');
      fileInput.setAttribute('aria-invalid', true);
    }

    function unsetInvalid() {
      label.classList.remove('invalid');
      label.setAttribute('aria-invalid', false);
      dragZone.classList.remove('invalid');
      dragZone.setAttribute('aria-invalid', false);
      fileInput.classList.remove('invalid');
      fileInput.setAttribute('aria-invalid', false);
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

    function dateiauswahl(elm) {
      var files = {},
          output = [],
          uploadError = '';

      if (typeof elm.originalEvent !== 'undefined') {
        files = elm.originalEvent.target.files;
      } else {
        files = elm.target.files;
      }

      var uploadsize = getTotalFilesSize(files);

      for (var _i2 = 0, f = files[_i2]; _i2 < files.length; _i2++) {
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

      uploadList.innerHTML = uploadError + '<ul style="text-align: left;">' + output.join('') + '</ul>';
    }

    if (typeof fileInput !== 'undefined') {
      fileInput.addEventListener('drag dragstart dragend dragover dragenter dragleave change', function (e) {
        e.preventDefault();
        e.stopPropagation();
      });
      fileInput.addEventListener('dragenter dragover', function (e) {
        dragZone.classList.add('hover');
      });
      fileInput.addEventListener('dragleave dragend drop', function (e) {
        dragZone.classList.remove('hover');
      });
      fileInput.addEventListener('change', function (e) {
        dateiauswahl(e);
      });
    }
  };

  var uploaderWrapper = document.querySelectorAll('.uploader-wrapper');
  Array.prototype.forEach.call(uploaderWrapper, function (elm) {
    jtfUploadFile(elm, {
      id: elm.querySelector('.legacy-uploader input[type="file"]').getAttribute('id'),
      uploadMaxSize: elm.querySelector('.legacy-uploader input[type="hidden"]').getAttribute('value')
    });
  });
});
