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
  document.formvalidator.setHandler('dateformat', (value) => {
    let dayformat = [
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
      moment = window.moment || {};

    return moment(value, dayformat, 'de', true).isValid();
  });
});
