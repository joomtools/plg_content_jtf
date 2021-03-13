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
  const systemMessageContainer = document.querySelector('#system-message-container');
  const errorMessageObserverConfig = {
    attributes: false,
    childList: true,
    characterData: false
  };
  const errorMessageObserver = new MutationObserver(() => {
    const errorMessage = systemMessageContainer.querySelector('.alert-error');
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
