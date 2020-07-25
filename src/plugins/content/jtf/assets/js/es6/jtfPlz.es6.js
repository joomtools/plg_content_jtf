/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

let jtfDomIsReady = window.jtfDomIsReady || {};

jtfDomIsReady(() => {
  document.formvalidator.setHandler('plz', function (value) {
    var regex = /^\d{5}$/;
    return regex.test(value);
  });
});
