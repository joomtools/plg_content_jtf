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
  document.formvalidator.setHandler('plz', function (value) {
    var regex = /^\d{5}$/;
    return regex.test(value);
  });
});
