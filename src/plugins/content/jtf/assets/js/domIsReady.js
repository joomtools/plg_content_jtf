"use strict";

/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2019 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 **/
var domIsReady = {};

domIsReady = function (domIsReady) {
  var isBrowserIeOrNot = function isBrowserIeOrNot() {
    return !document.attachEvent || typeof document.attachEvent === "undefined" ? 'not-ie' : 'ie';
  };

  domIsReady = function domIsReady(callback) {
    if (callback && typeof callback === 'function') {
      if (isBrowserIeOrNot() !== 'ie') {
        document.addEventListener("DOMContentLoaded", function () {
          return callback();
        });
      } else {
        document.attachEvent("onreadystatechange", function () {
          if (document.readyState === "complete") {
            return callback();
          }
        });
      }
    } else {
      console.error('The callback is not a function!');
    }
  };

  return domIsReady;
}(domIsReady);
