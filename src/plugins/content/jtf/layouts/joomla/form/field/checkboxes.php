<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var   string  $autocomplete   Autocomplete attribute for the field.
 * @var   boolean $autofocus      Is autofocus enabled?
 * @var   string  $class          Classes for the input.
 * @var   string  $description    Description of the field.
 * @var   boolean $disabled       Is this field disabled?
 * @var   string  $group          Group the field belongs to. <fields> section in form XML.
 * @var   boolean $hidden         Is this field hidden in the form?
 * @var   string  $hint           Placeholder for the field.
 * @var   string  $id             DOM id of the field.
 * @var   string  $label          Label of the field.
 * @var   string  $labelclass     Classes to apply to the label.
 * @var   boolean $multiple       Does this field support multiple values?
 * @var   string  $name           Name of the input field.
 * @var   string  $onchange       Onchange attribute for the field.
 * @var   string  $onclick        Onclick attribute for the field.
 * @var   string  $pattern        Pattern (Reg Ex) of value of the form field.
 * @var   boolean $readonly       Is this field read only?
 * @var   boolean $repeat         Allows extensions to duplicate elements.
 * @var   boolean $required       Is this field required?
 * @var   integer $size           Size attribute of the input.
 * @var   boolean $spellcheck     Spellcheck state for the form field.
 * @var   string  $validate       Validation rules to apply.
 * @var   string  $value          Value attribute of the field.
 * @var   array   $checkedOptions Options that will be set as checked.
 * @var   boolean $hasValue       Has this field a value assigned?
 * @var   array   $options        Options available for this field.
 * @var   string  $dataAttribute  Miscellaneous data attributes preprocessed for HTML output
 * @var   array   $dataAttributes Miscellaneous data attributes for eg, data-*.
 */

// Build the fieldset attributes array.
$fieldsetAttributes          = [];
$fieldsetAttributes['id']    = $id;
$fieldsetAttributes['class'] = 'checkboxes checkboxes-group';

$fieldElementClass = empty(trim($class)) ? '' : ' class="' . trim($class) . '"';

!$readonly ? null : $fieldsetAttributes['readonly'] = 'readonly';
!$disabled ? null : $fieldsetAttributes['disabled'] = 'disabled';
!$autofocus ? null : $fieldsetAttributes['autofocus'] = 'autofocus';

if ($required) {
    $fieldsetAttributes['required']      = 'required';
    $fieldsetAttributes['aria-required'] = 'true';
}

$fieldsetAttributes = ArrayHelper::toString($fieldsetAttributes);
?>
<fieldset <?php echo $fieldsetAttributes; ?>>
	<legend class="visually-hidden"><?php echo $label; ?></legend>
    <?php foreach ($options as $i => $option) :
        $optionId = $id . $i;
        $isChecked = in_array((string) $option->value, $checkedOptions, true);
        $isChecked = (!$hasValue && $option->checked) ? true : $isChecked;

        // Build the label attributes array.
        $optionLabelAttributes        = [];
        $optionLabelAttributes['for'] = $optionId;

        empty($option->labelclass) ? null : $optionLabelAttributes['class'] = $option->labelclass;
        empty($option->disable) ? null : $optionLabelAttributes['disabled'] = 'disabled';

        // Build the option attributes array.
        $optionAttributes          = [];
        $optionAttributes['type']  = 'checkbox';
        $optionAttributes['id']    = $optionId;
        $optionAttributes['name']  = $name;
        $optionAttributes['value'] = empty($option->value) ? '' : htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');

        empty($option->class) ? null : $optionAttributes['class'] = $option->class;
        empty($option->onclick) ? null : $optionAttributes['onclick'] = $option->onclick;
        empty($option->onchange) ? null : $optionAttributes['onchange'] = $option->onchange;
        empty($option->disable) || !$disabled ? null : $optionAttributes['disabled'] = 'disabled';
        !$isChecked ? null : $optionAttributes['checked'] = 'checked';

        $optionAttributes      = ArrayHelper::toString($optionAttributes);
        $optionLabelAttributes = ArrayHelper::toString($optionLabelAttributes);
        ?>

		<div<?php echo $fieldElementClass; ?>>
			<input <?php echo $optionAttributes; ?> />
			<label <?php echo $optionLabelAttributes ?>
                <?php if (!empty($option->optionattr)) :
                    Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('showon');
                    HTMLHelper::_('script', 'plg_content_jtf/jtfShowon.min.js', ['version' => 'auto', 'relative' => true], ['defer' => 'defer']);
                    echo $option->optionattr; ?>
                <?php endif; ?>
			>
                <?php echo $option->text; ?>
			</label>
		</div>
    <?php endforeach; ?>
</fieldset>
