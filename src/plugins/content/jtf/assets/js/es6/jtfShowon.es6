/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

let jtfDomIsReady = window.jtfDomIsReady || {};

jtfDomIsReady(() => {
  const setNovalidate = (field) => {
      if (!field.classList.contains('novalidate')) {
        field.classList.add('novalidate');
        field.setAttribute('disabled', 'disabled');
      }
    },

    removeNovalidate = (field) => {
      if (field.classList.contains('novalidate')) {
        field.classList.remove('novalidate');
        field.removeAttribute('disabled');
      }
    },

    toggleNovalidate = (field, isActive) => {
      if (isActive) {
        removeNovalidate(field);
      } else {
        setNovalidate(field);
      }
    };

  let showonElements = document.querySelectorAll('[data-showon]');
  Array.prototype.forEach.call(showonElements, function (parent) {
    let isActive = false,
      formFields = parent.querySelectorAll('input, select, textarea, fieldset');

    if (parent.style.display !== 'none') {
      isActive = true;
    }

    if (!isActive && parent.classList.contains('uk-display-block')) {
      parent.classList.remove('uk-display-block');
    }

    Array.prototype.forEach.call(formFields, function (field) {
      toggleNovalidate(field, isActive);
    });

    const showonObserverConfig = {
        attributes: true,
        childList: false,
        characterData: false
      },

      showonObserver = new MutationObserver(function () {
        isActive = parent.style.display !== 'none';

        Array.prototype.forEach.call(formFields, function (field) {
          toggleNovalidate(field, isActive);
        });
      });

    showonObserver.observe(parent, showonObserverConfig);
  });
});
