/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2025 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

"use strict";

var jtfSetNovalidate = function jtfSetNovalidate(field) {
    if (!field.classList.contains('novalidate')) {
      field.classList.add('novalidate');
      field.setAttribute('disabled', 'disabled');
      if (field.classList.contains('required')) {
        field.removeAttribute('required');
        field.removeAttribute('aria-required');
      }
      if (field.classList.contains('invalid')) {
        field.removeAttribute('aria-invalid');
      }
    }
  },
  jtfRemoveNovalidate = function jtfRemoveNovalidate(field) {
    if (field.classList.contains('novalidate')) {
      field.classList.remove('novalidate');
      field.removeAttribute('disabled');
      if (field.classList.contains('required')) {
        field.setAttribute('required', 'required');
        field.setAttribute('aria-required', 'true');
      }
      if (field.classList.contains('invalid')) {
        field.setAttribute('aria-invalid', 'true');
      }
    }
  },
  jtfToggleNovalidate = function jtfToggleNovalidate(field, isActive) {
    if (isActive) {
      jtfRemoveNovalidate(field);
    } else {
      jtfSetNovalidate(field);
    }
  },
  jtfInitShowon = function initShowon(parent) {
    var showonObserverConfig = {
        attributes: true,
        childList: false,
        characterData: false
      },
      formFields = parent.querySelectorAll('input, select, textarea, fieldset'),
      showonObserver = new MutationObserver(function () {
        var isActive = parent.style.display !== 'none' && !parent.classList.contains('hidden');
        Array.prototype.forEach.call(formFields, function (field) {
          jtfToggleNovalidate(field, isActive);
        });
      });
    showonObserver.observe(parent, showonObserverConfig);
  };
document.addEventListener('DOMContentLoaded', function () {
  var jtfShowonElements = document.querySelectorAll('[data-showon]');
  Array.prototype.forEach.call(jtfShowonElements, function (parent) {
    var isActive = parent.style.display !== 'none' && !parent.classList.contains('hidden'),
      formFields = parent.querySelectorAll('input, select, textarea, fieldset');
    if (!isActive && parent.classList.contains('uk-display-block')) {
      parent.classList.remove('uk-display-block');
    }
    Array.prototype.forEach.call(formFields, function (field) {
      jtfToggleNovalidate(field, isActive);
    });
    jtfInitShowon(parent);
  });
});
