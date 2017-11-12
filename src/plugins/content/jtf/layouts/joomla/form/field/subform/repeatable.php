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
	JHtml::_('script', 'plugins/content/jtf/assets/js/system/subform-repeatable.js', array('version' => 'auto'));
}

$sublayout = empty($groupByFieldset) ? 'section' : 'section-byfieldsets';
$layoutPaths = $this->getIncludePaths();

?>

<div class="row-fluid">
	<div class="subform-repeatable-wrapper subform-layout">
		<div class="subform-repeatable"
			 data-bt-add="a.group-add" data-bt-remove="a.group-remove" data-bt-move="a.group-move"
			 data-repeatable-element="div.subform-repeatable-group" data-minimum="<?php echo $min; ?>"
			 data-maximum="<?php echo $max; ?>">
			<div class="btn-toolbar">
				<div class="btn-group">
					<a class="group-add btn btn-mini button btn-success"><span class="icon-plus"></span> </a>
				</div>
			</div>
			<?php
			foreach ($forms as $k => $form) :
				// Set Layouts override
				$form->layoutPaths = $layoutPaths;

				echo $this->sublayout($sublayout,
					array(
						'form' => $form,
						'basegroup' => $fieldname,
						'group' => $fieldname . $k,
						'buttons' => $buttons)
				);
			endforeach;
			?>
			<?php if ($multiple) : ?>
				<script type="text/subform-repeatable-template-section" class="subform-repeatable-template-section">
					<?php
					// Set Layouts override
					$tmpl->layoutPaths = $layoutPaths;

					echo $this->sublayout($sublayout,
						array(
							'form' => $tmpl,
							'basegroup' => $fieldname,
							'group' => $fieldname . 'X',
							'buttons' => $buttons)
					);
					?>
				</script>
			<?php endif; ?>
		</div>
	</div>
</div>
<script>
	jQuery(document).on('subform-row-add', function (event, row) {
		document.formvalidator = new JFormValidator();
	})
</script>