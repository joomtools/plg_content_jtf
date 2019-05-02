/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2019 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 **/

let domIsReady = {};
domIsReady = function (domIsReady) {
	const isBrowserIeOrNot = () => {
		return !document.attachEvent || typeof document.attachEvent === "undefined" ? 'not-ie' : 'ie';
	};

	domIsReady = (callback) => {
		if (callback && typeof callback === 'function') {
			if (isBrowserIeOrNot() !== 'ie') {
				document.addEventListener("DOMContentLoaded", () => {
					return callback();
				});
			} else {
				document.attachEvent("onreadystatechange", () => {
					if (document.readyState === "complete") {
						return callback();
					}
				});
			}
		} else {
			console.error('The callback is not a function!');
		}
	};

	return domIsReady;
}(domIsReady);