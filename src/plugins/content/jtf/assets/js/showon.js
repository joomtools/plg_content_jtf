/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/
jQuery(function ($) {
	"use strict";

	$.fn.isOnScreen = function()
	{
		var win = $(window);

		var viewport = {
			top : win.scrollTop(),
			left : win.scrollLeft()
		};
		viewport.right = viewport.left + win.width();
		viewport.bottom = viewport.top + win.height();

		var bounds = this.offset();
		bounds.right = bounds.left + this.outerWidth();
		bounds.bottom = bounds.top + this.outerHeight();

		return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));
	};

	$.fn.toggleNovalidate = function (activate) {
		if (activate > 0) {
			$(this).removeNovalidate();
		}
		else {
			$(this).setNovalidate();
		}
	};

	$.fn.setNovalidate = function () {
		if (!$(this).hasClass('novalidate')) {
			$(this).addClass('novalidate').attr('disabled', 'disabled');
		}
	};

	$.fn.removeNovalidate = function () {
		if ($(this).hasClass('novalidate')) {
			$(this).removeClass('novalidate').removeAttr('disabled');
		}
	};

	$('[data-showon]').each(function (index, elm) {
		var $elm = $(elm),

			dataShowon = $.parseJSON($elm.attr('data-showon')),
			dataShowonId = dataShowon[0].field,
			dataShowonValues = dataShowon[0].values,

			$setter = $('[name^="' + dataShowonId + '"]'),
			setterVal = $setter.val(),
			isCheckbox = $setter.is('[type="checkbox"]'),
			isSelect = $setter.is('select'),
			isRadio = $setter.is('[type="radio"]');

		if (isRadio) {
			setterVal = $setter.filter(':checked').val();
		}

		if (isSelect) {
			setterVal = $setter.val();
		}

		if (setterVal === undefined) {
			setterVal = 0;
		}

		$elm.hide();
		$elm.find('input').setNovalidate();
		$elm.find('select').setNovalidate();
		$elm.find('textarea').setNovalidate();
		$elm.find('fieldset').setNovalidate();

		if ($.inArray(setterVal.toString(), dataShowonValues) != -1) {
			if ((isCheckbox && $setter.prop('checked')) || (isSelect && $setter.prop('selected')) || isRadio) {
				$elm.find('input').removeNovalidate();
				$elm.find('select').removeNovalidate();
				$elm.find('textarea').removeNovalidate();
				$elm.find('fieldset').removeNovalidate();
				$elm.show();
			}
		}

		$setter.on('change', function () {
			var toggler = 0,
				setterVal = $(this).filter(':checked').val();

			if (isSelect) {
				setterVal = $setter.val();
			}

			if (setterVal === undefined) {
				setterVal = 0;
			}

			if ($.inArray(setterVal.toString(), dataShowonValues) != -1) {
				toggler = 1
			}

			$elm.find('input').toggleNovalidate(toggler);
			$elm.find('select').toggleNovalidate(toggler);
			$elm.find('textarea').toggleNovalidate(toggler);
			$elm.find('fieldset').toggleNovalidate(toggler);

			if (toggler == 1) {
				$elm.show(400);
			}
			else {
				$elm.hide(400);
			}
		});

	});
});
