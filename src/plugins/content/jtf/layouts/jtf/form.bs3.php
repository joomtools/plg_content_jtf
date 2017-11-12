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

extract($displayData);
$formClass = !empty($form->getAttribute('class')) ? ' ' . (string) $form->getAttribute('class') : '';

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('script', 'plugins/content/jtf/assets/js/scrollToError.js', array('version' => 'auto'));

$invalidColor = '#ff0000';
$invalidBackgroundColor = '#f2dede';

JFactory::getDocument()->addStyleDeclaration(
	".invalid:not(label) { 
		border-color: " . $invalidColor . " !important;
		background-color: " . $invalidBackgroundColor . " !important;
	}
	.invalid { color: " . $invalidColor . " !important; }
	.inline { display: inline-block !important; }"
	. $frwkCss
);

?>
<div class="contact-form">
	<p><strong><?php echo JText::_('JTF_REQUIRED_FIELDS_LABEL'); ?></strong></p>
	<form name="<?php echo $id . $index; ?>_form"
	      id="<?php echo $id . $index; ?>_form"
	      action="<?php echo JRoute::_("index.php"); ?>"
	      method="post"
	      class="form-validate<?php echo $formClass; ?>"
	      enctype="multipart/form-data">
		<?php

		$fieldsets = $form->getXML();

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

		<input type="hidden" name="option" value="<?php echo JFactory::getApplication()->input->get('option'); ?>"/>
		<input type="hidden" name="task" value="<?php echo $id . $index; ?>_sendmail"/>
		<input type="hidden" name="view" value="<?php echo JFactory::getApplication()->input->get('view'); ?>"/>
		<input type="hidden" name="itemid" value="<?php echo JFactory::getApplication()->input->get('idemid'); ?>"/>
		<input type="hidden" name="id" value="<?php echo JFactory::getApplication()->input->get('id'); ?>"/>
		<?php echo JHtml::_('form.token'); ?>

	</form>
</div>
