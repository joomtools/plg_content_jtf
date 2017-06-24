/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/
jQuery(document).ready(function () {
	document.formvalidator.setHandler('plz', function (value) {
		regex = /^\d{5}$/;
		return regex.test(value);
	});
});
