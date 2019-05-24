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
  var forms = document.querySelectorAll('.jtf.contact-form form');
  forms.forEach(function (form) {
    var formId = form.getAttribute('id'),
        submit = document.querySelector('.jtf.contact-form form#' + formId + ' [type="submit"]');

    var ttf = jtfttf[formId],
        spanCounter = document.createElement('span'),
        myTimer = function myTimer() {
      spanCounter.innerHTML = ttf--;
      submit.appendChild(spanCounter);
      timer = setTimeout(myTimer, 1000);
    };

    submit.setAttribute('disabled', 'disabled');
    spanCounter.setAttribute('class', 'jtf-timer ' + jtfBadgeClass[formId]);
    spanCounter.setAttribute('style', 'margin-left: 1em;');
    submit.appendChild(spanCounter);
    var timer = setTimeout(myTimer, 1000);
    setTimeout(function () {
      clearTimeout(timer);
      submit.removeAttribute('disabled');
      submit.removeChild(spanCounter);
    }, (jtfttf[formId] + 1) * 1000);
  });
});
