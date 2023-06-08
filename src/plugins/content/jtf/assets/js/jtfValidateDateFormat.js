"use strict";

/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */
document.addEventListener('DOMContentLoaded', function () {
  document.formvalidator.setHandler('dateformat', function (value) {
    var dayformat = ['L', 'D.M.YY', 'D.M.YY hh:mm', 'D.M.YY hh:mm:ss', 'D-M-YY', 'D-M-YY hh:mm', 'D-M-YY hh:mm:ss', 'D/M/YY', 'D/M/YY hh:mm', 'D/M/YY hh:mm:ss', 'D.M.YYYY', 'D.M.YYYY hh:mm', 'D.M.YYYY hh:mm:ss', 'D-M-YYYY', 'D-M-YYYY hh:mm', 'D-M-YYYY hh:mm:ss', 'D/M/YYYY', 'D/M/YYYY hh:mm', 'D/M/YYYY hh:mm:ss', 'DD.MM.YY', 'DD.MM.YY hh:mm', 'DD.MM.YY hh:mm:ss', 'DD-MM-YY', 'DD-MM-YY hh:mm', 'DD-MM-YY hh:mm:ss', 'DD/MM/YY', 'DD/MM/YY hh:mm', 'DD/MM/YY hh:mm:ss', 'DD.MM.YYYY', 'DD.MM.YYYY hh:mm', 'DD.MM.YYYY hh:mm:ss', 'DD-MM-YYYY', 'DD-MM-YYYY hh:mm', 'DD-MM-YYYY hh:mm:ss', 'DD/MM/YYYY', 'DD/MM/YYYY hh:mm', 'DD/MM/YYYY hh:mm:ss'],
        moment = window.moment || {};
    return moment(value, dayformat, 'de', false).isValid();
  });
});
