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
 * @var JForm  $tmpl            The Empty form for template
 * @var array  $forms           Array of JForm instances for render the rows
 * @var bool   $multiple        The multiple state for the form field
 * @var int    $min             Count of minimum repeating in multiple mode
 * @var int    $max             Count of maximum repeating in multiple mode
 * @var string $fieldname       The field name
 * @var string $control         The forms control
 * @var string $label           The field label
 * @var string $description     The field description
 * @var array  $buttons         Array of the buttons that will be rendered
 * @var bool   $groupByFieldset Whether group the subform fields by it`s fieldset
 */
extract($displayData);

// Add script
if ($multiple)
{
	JHtml::_('jquery.ui', array('core', 'sortable'));
	JHtml::_('script', 'system/subform-repeatable.js', array('version' => 'auto', 'relative' => true));
}

$sublayout = empty($groupByFieldset) ? 'section' : 'section-byfieldsets'; ?>

	<div class="subform-repeatable-wrapper subform-layout uk-width-1-1">
		<div class="subform-repeatable uk-grid"
			 data-bt-add="a.group-add-<?php echo $unique_subform_id; ?>"
			 data-bt-remove="a.group-remove-<?php echo $unique_subform_id; ?>"
			 data-bt-move="a.group-move-<?php echo $unique_subform_id; ?>"
			 data-minimum="<?php echo $min; ?>" data-maximum="<?php echo $max; ?>">

			<?php if (!empty($buttons['add'])) : ?>
			<div class="uk-margin-bottom uk-width-1-1">
				<div class="uk-button-group">
					<a class="uk-button uk-button-small uk-button-success group-add-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_ADD'); ?>"><span class="uk-icon-plus"></span>
					</a>
				</div>
			</div>
			<?php endif; ?>

			<div class="uk-width-1-1">
				<div class="uk-grid">
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
				endforeach; ?>
				</div>
			</div>
			<?php if ($multiple) : ?>
				<script type="text/subform-repeatable-template-section" class="subform-repeatable-template-section">
					<?php
					$tmpl = FrameworkHelper::setFrameworkClasses($tmpl);
					echo $this->sublayout($sublayout,
						array(
							'form'      => $tmpl,
							'basegroup' => $fieldname,
							'group'     => $fieldname . 'X',
							'buttons' => $buttons,
							'unique_subform_id' => $unique_subform_id,
						)
					); ?>
				</script>
			<?php endif; ?>
		</div>
	</div>
<script>
	(function ($) {
		$(document).on('subform-row-add', function (event, row) {
			document.formvalidator = new JFormValidator();

			if ($(row).find('.uploader-wrapper') !== undefined) {
				$(row).find('.uploader-wrapper').each(function() {
					$(this).jtfUploadFile({
						id: $(this).find('.legacy-uploader input[type="file"]').attr('id'),
						uploadMaxSize: $(this).find('.legacy-uploader input[type="hidden"]').attr('value')
					});
				});
			}
		});
	})(jQuery);
</script>