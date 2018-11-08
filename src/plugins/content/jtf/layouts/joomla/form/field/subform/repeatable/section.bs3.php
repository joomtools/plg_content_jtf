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
 * @var JForm   $form       The form instance for render the section
 * @var string  $basegroup  The base group name
 * @var string  $group      Current group name
 * @var array   $buttons    Array of the buttons that will be rendered
 */
extract($displayData);
$subformClass = !empty($form->getAttribute('class')) ? ' ' . $form->getAttribute('class') : '';
?>

<div class="subform-repeatable-group<?php echo $subformClass;?> subform-repeatable-group-<?php echo $unique_subform_id; ?>" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">

<?php foreach ($form->getGroup('') as $field) : ?>
	<?php echo $field->renderField(); ?>
<?php endforeach; ?>
	<?php if (!empty($buttons)) : ?>
		<div class="btn-toolbar pull-right" role="toolbar">
			<div class="btn-group btn-group-sm">
				<?php if (!empty($buttons['add'])) : ?>
					<a class="group-add btn btn-success group-add-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_ADD'); ?>"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></a>
				<?php endif; ?>
				<?php if (!empty($buttons['remove'])) : ?>
					<a class="group-remove btn btn-danger group-remove-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_REMOVE'); ?>"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span></a>
				<?php endif; ?>
				<?php if (!empty($buttons['move'])) : ?>
					<a class="group-move btn btn-primary group-move-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_MOVE'); ?>"><span class="glyphicon glyphicon-move" aria-hidden="true"></span></a>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
