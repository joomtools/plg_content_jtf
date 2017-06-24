<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/

defined('_JEXEC') or die;

extract($displayData);

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');

$invalidColor = '#ff0000';

JFactory::getDocument()->addScriptDeclaration(
	<<<JS
	jQuery(document).ready(function($){
		$("body").on('DOMSubtreeModified', "#system-message-container", function() {
			var error = $(this).find('alert-error');
			if (undefined !== error) {
				$('html, body').animate({
						scrollTop: $(this).offset().top-100
					}, 500, 'linear');
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
	{$frwkCss}
CSS
);
?>
<div class="contact-form">
	<p><strong><?php echo JText::_('JTF_REQUIRED_FIELDS_LABEL'); ?></strong></p>
	<form name="<?php echo $id . $index; ?>_form"
		  id="<?php echo $id . $index; ?>_form"
		  action="<?php echo JRoute::_("index.php"); ?>"
		  method="post"
		  class="<?php echo $formclass; ?>"
		<?php echo $enctype ?>>

		<?php
		$fieldsets         = $form->getXML();

		foreach ($fieldsets->fieldset as $fieldset) :
			$fieldsetLabel = (string) $fieldset['label'];
			$fieldsetDesc  = (string) $fieldset['description'];
			$fieldsetClass = (string) $fieldset['class']
				? ' class="' . (string) $fieldset['class'] . '"'
				: '';

			$gridFieldset          = array();
			$gridFieldset['label'] = array();
			$gridFieldset['field'] = array();

			if (!empty((string) $fieldset['gridlabel']))
			{
				$gridFieldset['label'] = explode(' ', (string) $fieldset['gridlabel']);
			}

			if (!empty((string) $fieldset['gridfield']))
			{
				$gridFieldset['field'] = explode(' ', (string) $fieldset['gridfield']);
			}
			?>

			<fieldset<?php echo $fieldsetClass; ?>>

				<?php if (!empty($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel)))) : ?>
					<legend><?php echo $legend; ?></legend>
				<?php endif; ?>

				<?php if (!empty($fieldsetDesc) && strlen($desc = trim(JText::_($fieldsetDesc)))) : ?>
					<p><?php echo $desc; ?></p>
				<?php endif; ?>

				<?php
				foreach ($fieldset->field as $field)
				{
					$fieldname = (string) $field['name'];

					$gridlabel = $form->getFieldAttribute($fieldname, 'gridlabel');
					$gridfield = $form->getFieldAttribute($fieldname, 'gridfield');

					$gridlabel = array_merge($gridFieldset['label'], explode(' ', $gridlabel));
					$gridfield = array_merge($gridFieldset['field'], explode(' ', $gridfield));

					if (!empty($gridlabel))
					{
						$form->setFieldAttribute($fieldname, 'gridlabel', implode(' ', array_unique($gridlabel)));
					}

					if (!empty($gridfield))
					{
						$form->setFieldAttribute($fieldname, 'gridfield', implode(' ', array_unique($gridfield)));
					}

					echo $form->renderField($fieldname);
				}
				?>
			</fieldset>
		<?php endforeach; ?>
		<?php echo $controlFields ?>
		<?php echo JHtml::_('form.token'); ?>

	</form>
</div>
