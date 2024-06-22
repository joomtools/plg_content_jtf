/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

document.addEventListener('DOMContentLoaded', () => {
	document.formvalidator.setHandler('plz', (value) => {
		let regex = /^\d{5}$/;
		return regex.test(value);
	});
});
