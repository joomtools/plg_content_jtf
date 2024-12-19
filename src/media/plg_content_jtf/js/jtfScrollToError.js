/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

"use strict";

document.addEventListener('DOMContentLoaded', function () {
  var systemMessageContainer = document.querySelector('#system-message-container'),
    errorMessageObserverConfig = {
      attributes: false,
      childList: true,
      characterData: false
    },
    errorMessageObserver = new MutationObserver(function () {
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
