<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use JoomTools\Plugin\Content\Jtf\Form\Form;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   Form    $tmpl             The Empty form for template
 * @var   array   $forms            Array of JForm instances for render the rows
 * @var   bool    $multiple         The multiple state for the form field
 * @var   int     $min              Count of minimum repeating in multiple mode
 * @var   int     $max              Count of maximum repeating in multiple mode
 * @var   string  $name             Name of the input field.
 * @var   string  $fieldname        The field name
 * @var   string  $control          The forms control
 * @var   string  $label            The field label
 * @var   string  $description      The field description
 * @var   array   $buttons          Array of the buttons that will be rendered
 * @var   bool    $groupByFieldset  Whether group the subform fields by it`s fieldset
 */

$layout = new FileLayout('joomla.form.field.subform.repeatable_tmpl');
$layout->setSuffixes($tmpl->framework);
$layout->addIncludePaths($tmpl->layoutPaths);
$layout->setDebug($tmpl->rendererDebug);
?>

<div class="row-fluid">
	<div class="subform-repeatable-wrapper subform-layout">
        <?php if (version_compare(JVERSION, 4, 'lt')) : ?>
		<div class="subform-repeatable"
			 data-bt-add="a.group-add-<?php echo $displayData['unique_subform_id']; ?>"
			 data-bt-remove="a.group-remove-<?php echo $displayData['unique_subform_id']; ?>"
			 data-bt-move="a.group-move-<?php echo $displayData['unique_subform_id']; ?>"
			 data-minimum="<?php echo $displayData['min']; ?>"
			 data-maximum="<?php echo $displayData['max']; ?>">

            <?php if (!empty($buttons['add'])) : ?>
				<div class="btn-toolbar">
					<div class="btn-group">
						<a class="btn btn-mini button btn-success group-add-<?php echo $displayData['unique_subform_id']; ?>"
						   aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>">
							<span class="icon-plus" aria-hidden="true"></span>
						</a>
					</div>
				</div>
            <?php endif; ?>
            <?php else: ?>
			<joomla-field-subform class="subform-repeatable row" name="<?php echo $name; ?>"
								  button-add=".group-add"
								  button-remove=".group-remove"
								  button-move="<?php echo empty($buttons['move']) ? '' : '.group-move' ?>"
								  repeatable-element=".subform-repeatable-group"
								  minimum="<?php echo $min; ?>"
								  maximum="<?php echo $max; ?>">
                <?php if (!empty($buttons['add'])) : ?>
					<div class="btn-toolbar">
						<div class="btn-group">
							<a class="group-add btn btn-sm button btn-success"
							   aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>" tabindex="0">
								<span class="icon-plus icon-white" aria-hidden="true"></span> </a>
						</div>
					</div>
                <?php endif; ?>
                <?php endif; ?>

                <?php echo $layout->render($displayData); ?>

                <?php if (version_compare(JVERSION, 4, 'lt')) : ?>
		</div>
    <?php else: ?>
		</joomla-field-subform>
    <?php endif; ?>
	</div>
</div>
