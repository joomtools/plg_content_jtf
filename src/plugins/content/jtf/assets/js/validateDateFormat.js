"use strict";

/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2019 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 **/

domIsReady(function () {
	document.formvalidator.setHandler('dateformat', function(value) {
		var dayformat = [
			'L',
			'D.M.YY',
			'D.M.YY hh:mm:ss',
			'D-M-YY',
			'D-M-YY hh:mm:ss',
			'D/M/YY',
			'D/M/YY hh:mm:ss',
			'D.M.YYYY',
			'D.M.YYYY hh:mm:ss',
			'D-M-YYYY',
			'D-M-YYYY hh:mm:ss',
			'D/M/YYYY',
			'D/M/YYYY hh:mm:ss',
			'DD.MM.YY',
			'DD.MM.YY hh:mm:ss',
			'DD-MM-YY',
			'DD-MM-YY hh:mm:ss',
			'DD/MM/YY',
			'DD/MM/YY hh:mm:ss',
			'DD.MM.YYYY',
			'DD.MM.YYYY hh:mm:ss',
			'DD-MM-YYYY',
			'DD-MM-YYYY hh:mm:ss',
			'DD/MM/YYYY',
			'DD/MM/YYYY hh:mm:ss'
		],
			isValid = moment(value, dayformat, 'de', true).isValid();

		return isValid;
	});
});
