"use strict";

/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */
var jtfDomIsReady = function jtfDomIsReady(fn) {
  if (document.readyState !== 'loading') {
    console.info('Dom is readyState');
    fn();
  } else if (document.addEventListener) {
    document.addEventListener('DOMContentLoaded', fn);
    console.info('Dom is DOMContentLoaded');
  } else {
    document.attachEvent('onreadystatechange', function () {
      if (document.readyState !== 'loading') {
        console.info('Dom is onreadystatechange');
        fn();
      }
    });
  }
};
