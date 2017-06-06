<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.jtf
 *
 * @author       Guido De Gobbis
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/

defined('_JEXEC') or die;

extract($displayData);

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');

$formClass    = !empty($form->getAttribute('class')) ? ' ' . (string) $form->getAttribute('class') : '';
$invalidColor = '#ff0000';

JFactory::getDocument()->addScriptDeclaration(
<<<JS
	jQuery(document).ready(function($){
		$("body").on('DOMSubtreeModified', "#system-message-container", function() {
			var error = $(this).find('alert-error');
			if (undefined !== error) {
				$('html, body').animate({
						scrollTop: $(this).offset().top-100
					}, 1000);
			}
		});
	});
JS
);

JFactory::getDocument()->addStyleDeclaration(
<<<CSS
	.invalid { 
		border-color:{$invalidColor}!important;
		color:{$invalidColor}!important;
	}
	label.invalid { color:{$invalidColor}!important; }
	.inline { display:inline-block!important; }
	.uk-form-icon:not(.uk-form-icon-flip)>select {
        padding-left: 40px !important;
	}
CSS
);
?>
<div class="contact-form">
	<p><strong><?php echo JText::_('JTF_REQUIRED_FIELDS_LABEL'); ?></strong></p>
	<form name="<?php echo $id . $index; ?>_form"
		  id="<?php echo $id . $index; ?>_form"
		  action="<?php echo JRoute::_("index.php"); ?>"
		  method="post"
		  class="uk-form form-validate<?php echo $formClass; ?>"
		<?php echo $enctype ?>>
		<?php
		$fieldsets         = $form->getXML();

		foreach ($fieldsets->fieldset as $fieldset) :

			$fieldsetLabel = (string) $fieldset['label'];
			$fieldsetDesc  = (string) $fieldset['description'];
			$fieldsetClass = (string) $fieldset['class']
				? ' class="' . (string) $fieldset['class'] . '"'
				: '';
			?>

			<fieldset<?php echo $fieldsetClass; ?>>
				<?php if (!empty($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel)))) : ?>
					<legend><?php echo $legend; ?></legend>
				<?php endif; ?>
				<?php if (!empty($fieldsetDesc) && strlen($desc = trim(JText::_($fieldsetDesc)))) : ?>
					<p><?php echo $desc; ?></p>
				<?php endif; ?>
				<?php foreach ($fieldset->field as $field)
				{
					echo $form->renderField((string) $field['name']);
				}
				?>
			</fieldset>
		<?php endforeach; ?>

		<input type="hidden" name="option" value="<?php echo JFactory::getApplication()->input->get('option'); ?>" />
		<input type="hidden" name="task" value="<?php echo $id . $index; ?>_sendmail" />
		<input type="hidden" name="view" value="<?php echo JFactory::getApplication()->input->get('view'); ?>" />
		<input type="hidden" name="itemid" value="<?php echo JFactory::getApplication()->input->get('idemid'); ?>" />
		<input type="hidden" name="id" value="<?php echo JFactory::getApplication()->input->get('id'); ?>" />
		<?php echo JHtml::_('form.token'); ?>

	</form>
</div>
