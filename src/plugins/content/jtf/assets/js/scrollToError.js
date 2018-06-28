/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/
jQuery(function ($) {
	$("body").on('DOMSubtreeModified', "#system-message-container", function () {
		var error = $(this).find('alert-error');
		if (undefined !== error) {
			$('html, body').animate({
				scrollTop: $(this).offset().top - 100
			}, 500, 'linear');
		}
	});
});
