"use strict";

/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */
document.addEventListener('DOMContentLoaded', function () {
	const options = {
		attributes: true
	}
	const observer = new MutationObserver(function (jtfLabel) {
		console.log('jtfLabel: ', jtfLabel);

		jtfLabel.forEach(function (mutation) {
			if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
				// handle class change
				var isInvalid = mutation.target.classList.contains('invalid');
				console.log('isInvalid: ', isInvalid);

				var jtfMarker = mutation.target.parentNode.parentNode.querySelector('.marker');
				console.log('jtfMarker: ', jtfMarker);

				if (null !== jtfMarker) {
					if (isInvalid) {
						jtfMarker.classList.add('invalid');
					} else {
						jtfMarker.classList.remove('invalid');
					}
				}
			}
		});
	});

	var jtfLabels = document.querySelectorAll('.jtf label');
	console.log('jtfLabels: ', jtfLabels);

	Array.prototype.forEach.call(jtfLabels, function(element) {
		console.log('element: ', element);
		observer.observe(element, options);
	});
});
