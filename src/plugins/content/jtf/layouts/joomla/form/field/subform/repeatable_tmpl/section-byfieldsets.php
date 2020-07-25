<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Make thing clear
 *
 * @var JForm   $form               The form instance for render the section
 * @var string  $basegroup          The base group name
 * @var string  $group              Current group name
 * @var array   $buttons            Array of the buttons that will be rendered
 * @var int     $unique_subform_id  Whether group the subform fields by it`s fieldset
 */

$subformClass = !empty($form->getAttribute('class')) ? ' ' . $form->getAttribute('class') : ''; ?>

<div class="subform-repeatable-group<?php echo $subformClass; ?> subform-repeatable-group-<?php echo $unique_subform_id; ?>"
	 data-base-name="<?php echo $basegroup; ?>"
	 data-group="<?php echo $group; ?>">

	<?php foreach ($form->getFieldsets() as $fieldset) :
		$fieldsetClass = !empty($fieldset->class) ? ' class="' . $fieldset->class . '"' : '';
		$legendClass = !empty($fieldset->legendClasses) ? ' class="' . $fieldset->legendClasses . '"' : ''; ?>

		<fieldset<?php echo $fieldsetClass; ?>>

			<?php if (!empty($fieldset->label)) : ?>
				<legend<?php echo $legendClass; ?>><?php echo trim(Text::_($fieldset->label)); ?></legend>
			<?php endif; ?>

			<?php if (!empty($fieldset->description)) : ?>
				<p class="fieldset-description"><?php echo trim(Text::_($fieldset->description)); ?></p>
			<?php endif; ?>

			<?php foreach ($form->getFieldset($fieldset->name) as $field)
			{
				echo $field->renderField();
			}
			?>
		</fieldset>
	<?php endforeach; ?>

	<?php if (!empty($buttons)) : ?>
		<div class="btn-toolbar text-right">
			<div class="btn-group">
				<?php if (!empty($buttons['add'])) : ?>
					<a class="btn btn-mini button btn-success group-add-<?php echo $unique_subform_id; ?>"
					   aria-label="<?php echo JText::_('JGLOBAL_FIELD_ADD'); ?>">
						<span class="icon-plus" aria-hidden="true"></span>
					</a>
				<?php endif; ?>
				<?php if (!empty($buttons['remove'])) : ?>
					<a class="btn btn-mini button btn-danger group-remove-<?php echo $unique_subform_id; ?>"
					   aria-label="<?php echo JText::_('JGLOBAL_FIELD_REMOVE'); ?>">
						<span class="icon-minus" aria-hidden="true"></span>
					</a>
				<?php endif; ?>
				<?php if (!empty($buttons['move'])) : ?>
					<a class="btn btn-mini button btn-primary group-move-<?php echo $unique_subform_id; ?>"
					   aria-label="<?php echo JText::_('JGLOBAL_FIELD_MOVE'); ?>">
						<span class="icon-move" aria-hidden="true"></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
/div>
