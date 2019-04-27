/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2019 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 **/

(function (document, window, domIsReady) {
	"use strict";

	domIsReady(function () {
		let ttf = jtfttf,
			submit = document.querySelector('.contact-form [type="submit"]'),
			spanCounter = document.createElement('span'),
			myTimer = (() => {
				spanCounter.innerHTML = ttf--;
				submit.appendChild(spanCounter);
				timer = setTimeout(myTimer, 1000);
			});

		submit.setAttribute('disabled', 'disabled');
		spanCounter.setAttribute('class', 'jtf-timer ' + jtfBadgeClass);
		spanCounter.setAttribute('style', 'margin-left: 1em;');
		submit.appendChild(spanCounter);

		let timer = setTimeout(myTimer, 1000);

		setTimeout(() => {
			clearTimeout(timer);
			submit.removeAttribute('disabled');
			submit.removeChild(spanCounter);
		}, (jtfttf + 1) * 1000);
	});
})(document, window, domIsReady);
