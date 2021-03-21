/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

document.addEventListener('DOMContentLoaded', () => {
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

  Array.prototype.forEach.call(showonElements, (parent) => {
    let isActive = (parent.style.display !== 'none' && !parent.classList.contains('hidden')),
        formFields = parent.querySelectorAll('input, select, textarea, fieldset');

    if (!isActive && parent.classList.contains('uk-display-block')) {
      parent.classList.remove('uk-display-block');
    }

    Array.prototype.forEach.call(formFields, (field) => {
      toggleNovalidate(field, isActive);
    });

    const showonObserverConfig = {
          attributes: true,
          childList: false,
          characterData: false
        },

        showonObserver = new MutationObserver(() => {
          isActive = (parent.style.display !== 'none' && !parent.classList.contains('hidden'));

          Array.prototype.forEach.call(formFields, (field) => {
            toggleNovalidate(field, isActive);
          });
        });

    showonObserver.observe(parent, showonObserverConfig);
  });
});
