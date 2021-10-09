<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Jtf\Framework\FrameworkHelper;

extract($displayData);

/**
 * Make thing clear
 *
 * @var   JForm  $tmpl              The Empty form for template
 * @var   array  $forms             Array of JForm instances for render the rows
 * @var   bool   $multiple          The multiple state for the form field
 * @var   int    $min               Count of minimum repeating in multiple mode
 * @var   int    $max               Count of maximum repeating in multiple mode
 * @var   string $fieldname         The field name
 * @var   string $control           The forms control
 * @var   string $label             The field label
 * @var   string $description       The field description
 * @var   array  $buttons           Array of the buttons that will be rendered
 * @var   int    $unique_subform_id Whether group the subform fields by it`s fieldset
 */

foreach ($forms as $k => $form)
{
	$form = FrameworkHelper::setFrameworkClasses($form, true);
	echo $this->sublayout('section',
		array(
			'form'              => $form,
			'basegroup'         => $fieldname,
			'group'             => $fieldname . $k,
			'buttons'           => $buttons,
			'unique_subform_id' => $unique_subform_id,
		)
	);
} ?>

<?php if ($multiple) : ?>
	<template type="text/subform-repeatable-template-section" class="subform-repeatable-template-section hidden">
		<?php $tmpl = FrameworkHelper::setFrameworkClasses($tmpl, true);
		echo $this->sublayout('section',
			array(
				'form'              => $tmpl,
				'basegroup'         => $fieldname,
				'group'             => $fieldname . 'X',
				'buttons'           => $buttons,
				'unique_subform_id' => $unique_subform_id,
			)
		); ?>
	</template>
<?php endif; ?>
<?php if (version_compare(JVERSION, 4, 'lt')) : ?>
	<script>
		(function ($) {
			$(document).on('subform-row-add', function (event, row) {
				document.formvalidator = new JFormValidator();

				if (!!row.querySelector('.uploader-wrapper')) {
					var jtfUploadFile = window.jtfUploadFile || {};
					Array.prototype.forEach.call(row.querySelectorAll('.uploader-wrapper'), function (el) {
						jtfUploadFile(el, {
							id: el.querySelector('input[type="file"].file-uplaoder').getAttribute('id'),
							uploadMaxSize: el.querySelector('input[type="hidden"].file-uplaoder').getAttribute('value')
						});
					});
				}
			});
		})(jQuery);
	</script>
<?php else: ?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			document.addEventListener('subform-row-add', function (event) {
				var row = event.target;
				document.formvalidator = new JFormValidator();

				if (!!row.querySelector('.uploader-wrapper')) {
					var jtfUploadFile = window.jtfUploadFile || {};
					Array.prototype.forEach.call(row.querySelectorAll('.uploader-wrapper'), function (el) {
						jtfUploadFile(el, {
							id: el.querySelector('input[type="file"].file-uplaoder').getAttribute('id'),
							uploadMaxSize: el.querySelector('input[type="hidden"].file-uplaoder').getAttribute('value')
						});
					});
				}
			});
		});
	</script>
<?php endif; ?>