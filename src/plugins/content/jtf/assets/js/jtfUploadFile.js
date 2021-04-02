"use strict";

/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */
var jtfUploadFile = function jtfUploadFile(elm, optionlist) {
  console.log('elm', elm);
  console.log('optionlist', optionlist);
  var Joomla = window.Joomla || {},
      mimelite = window.mimelite || {},
      acceptedType = '',
      acceptedExt = '';

  var allowedExt = elm.querySelector('.allowedExt'),
      dragarea = elm.querySelector('.dragarea'),
      fileInput = document.querySelector('#' + optionlist.id),
      uploadList = elm.querySelector('.upload-list'),
      label = document.querySelector('label[for="' + optionlist.id + '"'),
      accept = fileInput.getAttribute('accept').split(','),
      maxsize = optionlist.uploadMaxSize,
      msgAllowedExt = Joomla.Text._('JTF_JS_UPLOAD_ALLOWED_FILES_EXT', ''),
      errorFileSize = Joomla.Text._('JTF_JS_UPLOAD_ERROR_MESSAGE_SIZE', ''),
      errorFileType = Joomla.Text._('JTF_JS_UPLOAD_ERROR_FILE_NOT_ALLOWED', '');

  console.log('allowedExt', allowedExt);
  console.log('dragarea', dragarea);
  console.log('fileInput', fileInput);
  console.log('uploadList', uploadList);
  console.log('label', label);
  console.log('accept', accept);
  console.log('maxsize', maxsize); // Set list of mimetype and file extension

  for (var i = 0, sep = ''; i < accept.length; ++i) {
    var mimeType = mimelite.getType(accept[i].replace('.', '')),
        fileExt = mimelite.getExtension(accept[i].replace('.', ''));

    if (mimeType === null) {
      mimeType = mimelite.getTypes(accept[i].replace('.', ''));
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

  acceptedExt = '.' + acceptedExt.replace(/,/g, ' .');
  acceptedType = acceptedType.replace(/,/g, ' ');
  allowedExt.innerHTML = msgAllowedExt + acceptedExt;

  var allowedFile = function allowedFile(fileType) {
    if (fileType) {
      var patt = new RegExp(fileType);

      if (patt.test(acceptedType)) {
        return true;
      }
    }

    return false;
  };

  var getTotalFilesSize = function getTotalFilesSize(files) {
    var size = 0;

    for (var _i = 0, f; _i < files.length; _i++) {
      f = files[_i];
      size += f.size;
    }

    return size;
  };

  var setInvalid = function setInvalid() {
    label.classList.add('invalid');
    label.setAttribute('aria-invalid', true);

    if (dragarea !== null) {
      dragarea.classList.add('invalid');
    }

    fileInput.classList.add('invalid');
    fileInput.setAttribute('aria-invalid', true);
  };

  var unsetInvalid = function unsetInvalid() {
    label.classList.remove('invalid');
    label.setAttribute('aria-invalid', false);

    if (dragarea !== null) {
      dragarea.classList.remove('invalid');
    }

    fileInput.classList.remove('invalid');
    fileInput.setAttribute('aria-invalid', false);
  };

  var humanReadableSize = function humanReadableSize(bytes) {
    var si = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
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
  };

  var dateiauswahl = function dateiauswahl(elm) {
    var files = {},
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
    var uploadsize = getTotalFilesSize(files);
    console.log('dateiauswahl -> uploadsize', uploadsize);

    for (var _i2 = 0, c = 1, f; _i2 < files.length; _i2++, c++) {
      f = files[_i2];
      var filename = '<strong>' + f.name + '</strong> ';
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
      document.formvalidator.setHandler('file', function () {
        return true;
      });
    }

    if (uploadError.length > 0) {
      errorWrapper = Joomla.Text._('JTF_JS_ERROR_WRAPPER_' + jtfFrwk, '').replace('%s', uploadError.join('<br />'));
      setInvalid();
      document.formvalidator.setHandler('file', function () {
        return false;
      });
    }

    console.log('errorWrapper', errorWrapper);
    console.log('uploadListWrapper', uploadListWrapper);
    uploadList.innerHTML = errorWrapper + uploadListWrapper;
  };

  if (label && dragarea) {
    console.info('label Eventhandler aufgerufen.');
    var prevClassState = label.classList.contains('invalid'),
        labelClassObserver = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.attributeName === "class") {
          var currentClassState = mutation.target.classList.contains('invalid');

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
    labelClassObserver.observe(label, {
      attributes: true
    });
  }

  if (typeof fileInput !== 'undefined') {
    console.info('fileInput Eventhandler aufgerufen.');
    fileInput.addEventListener('change', function (e) {
      e.preventDefault();
      e.stopPropagation();
      dateiauswahl(e);
    }, false);
  }
};

var jtfFrwk = window.jtfFrwk || 'BS2';
document.addEventListener('DOMContentLoaded', function () {
  var uploaderWrapper = document.querySelectorAll('.uploader-wrapper');
  console.log('uploaderWrapper', uploaderWrapper);
  Array.prototype.forEach.call(uploaderWrapper, function (elm) {
    jtfUploadFile(elm, {
      id: elm.querySelector('input[type="file"].file-uplaoder').getAttribute('id'),
      uploadMaxSize: elm.querySelector('input[type="hidden"].file-uplaoder').getAttribute('value')
    });
  });
});
