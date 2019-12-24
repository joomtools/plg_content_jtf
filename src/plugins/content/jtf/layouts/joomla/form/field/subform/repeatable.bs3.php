<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Jtf\Frameworks\FrameworkHelper;

/**
 * Make thing clear
 *
 * @var JForm   $tmpl             The Empty form for template
 * @var array   $forms            Array of JForm instances for render the rows
 * @var bool    $multiple         The multiple state for the form field
 * @var int     $min              Count of minimum repeating in multiple mode
 * @var int     $max              Count of maximum repeating in multiple mode
 * @var string  $fieldname        The field name
 * @var string  $control          The forms control
 * @var string  $label            The field label
 * @var string  $description      The field description
 * @var array   $buttons          Array of the buttons that will be rendered
 * @var bool    $groupByFieldset  Whether group the subform fields by it`s fieldset
 */
extract($displayData);

// Add script
if ($multiple)
{
	JHtml::_('jquery.ui', array('core', 'sortable'));
	JHtml::_('script', 'system/subform-repeatable.js', array('version' => 'auto', 'relative' => true));
}

$sublayout = empty($groupByFieldset) ? 'section' : 'section-byfieldsets'; ?>

<div class="row">
	<div class="subform-repeatable-wrapper subform-layout">
		<div class="subform-repeatable"
			 data-bt-add="a.group-add-<?php echo $unique_subform_id; ?>"
			 data-bt-remove="a.group-remove-<?php echo $unique_subform_id; ?>"
			 data-bt-move="a.group-move-<?php echo $unique_subform_id; ?>"
			 data-minimum="<?php echo $min; ?>" data-maximum="<?php echo $max; ?>">

			<?php if (!empty($buttons['add'])) : ?>
				<div class="btn-toolbar" role="toolbar">
					<div class="btn-group btn-group-sm">
						<a class="group-add btn btn-mini button btn-success group-add-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_ADD'); ?>">
							<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
						</a>
					</div>
				</div>
			<?php endif; ?>

			<div class="row">
			<?php
			foreach ($forms as $k => $form) :
				$form = FrameworkHelper::setFrameworkClasses($form);
				echo $this->sublayout($sublayout,
					array(
						'form' => $form,
						'basegroup' => $fieldname,
						'group' => $fieldname . $k,
						'buttons' => $buttons,
						'unique_subform_id' => $unique_subform_id,
					)
				);
			endforeach;
			?>
			</div>
			<?php if ($multiple) : ?>
				<template type="text/subform-repeatable-template-section" class="subform-repeatable-template-section">
					<?php $tmpl = FrameworkHelper::setFrameworkClasses($tmpl);
					echo $this->sublayout($sublayout,
						array(
							'form' => $tmpl,
							'basegroup' => $fieldname,
							'group' => $fieldname . 'X',
							'buttons' => $buttons,
							'unique_subform_id' => $unique_subform_id,
						)
					);
					?>
				</template>
			<?php endif; ?>
		</div>
	</div>
</div>
<script>
	(function ($) {
		$(document).on('subform-row-add', function (event, row) {
			document.formvalidator = new JFormValidator();

			if ($(row).find('.uploader-wrapper') !== undefined) {
				$(row).find('.uploader-wrapper').each(function() {
					jtfUploadFile(this, {
						id: this.querySelector('.legacy-uploader input[type="file"]').getAttribute('id'),
						uploadMaxSize: this.querySelector('.legacy-uploader input[type="hidden"]').getAttribute('value')
					});
				});
			}
		});
	})(jQuery);
</script>