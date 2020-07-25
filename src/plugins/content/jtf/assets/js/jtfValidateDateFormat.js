"use strict";

/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */
var jtfDomIsReady = window.jtfDomIsReady || {};
jtfDomIsReady(function () {
  document.formvalidator.setHandler('dateformat', function (value) {
    var dayformat = ['L', 'D.M.YY', 'D-M-YY', 'D/M/YY', 'D.M.YYYY', 'D-M-YYYY', 'D/M/YYYY', 'DD.MM.YY', 'DD-MM-YY', 'DD/MM/YY', 'DD.MM.YYYY', 'DD-MM-YYYY', 'DD/MM/YYYY'],
        moment = window.moment || {};
    return moment(value, dayformat, 'de', true).isValid();
  });
});
