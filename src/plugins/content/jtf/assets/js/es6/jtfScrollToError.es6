/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

document.addEventListener('DOMContentLoaded', () => {
  var systemMessageContainer = document.querySelector('#system-message-container');
  var errorMessageObserverConfig = {
    attributes: false,
    childList: true,
    characterData: false
  };
  var errorMessageObserver = new MutationObserver(() => {
    var errorMessage = systemMessageContainer.querySelector('.alert-error');

    if (null !== errorMessage) {
      window.scrollTo({
        top: errorMessage.getBoundingClientRect().top - 100,
        left: 0,
        behavior: "smooth"
      });
    }
  });
  errorMessageObserver.observe(systemMessageContainer, errorMessageObserverConfig);
});
