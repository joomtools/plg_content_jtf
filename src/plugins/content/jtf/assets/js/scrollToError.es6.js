/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2019 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 **/

((document, window, domIsReady) => {
	"use strict";

	domIsReady(() => {
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
})(document, window, domIsReady);