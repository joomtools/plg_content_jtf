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
 * @var   string   $autocomplete  Autocomplete attribute for the field.
 * @var   boolean  $autofocus     Is autofocus enabled?
 * @var   string   $class         Classes for the input.
 * @var   string   $description   Description of the field.
 * @var   boolean  $disabled      Is this field disabled?
 * @var   string   $group         Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden        Is this field hidden in the form?
 * @var   string   $hint          Placeholder for the field.
 * @var   string   $id            DOM id of the field.
 * @var   string   $label         Label of the field.
 * @var   string   $labelclass    Classes to apply to the label.
 * @var   boolean  $multiple      Does this field support multiple values?
 * @var   string   $name          Name of the input field.
 * @var   string   $onchange      Onchange attribute for the field.
 * @var   string   $onclick       Onclick attribute for the field.
 * @var   string   $pattern       Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly      Is this field read only?
 * @var   boolean  $repeat        Allows extensions to duplicate elements.
 * @var   boolean  $required      Is this field required?
 * @var   integer  $size          Size attribute of the input.
 * @var   boolean  $spellcheck    Spellcheck state for the form field.
 * @var   string   $validate      Validation rules to apply.
 * @var   string   $value         Value attribute of the field.
 * @var   array    $options       Options available for this field.
 */

// Build the fieldset attributes array.
$fieldsetAttributes          = array();
$fieldsetAttributes['id']    = $id;
$fieldsetAttributes['class'] = 'radio radio-group';

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
    <?php foreach ($options as $i => $option) :
        $optionId = $id . $i;

        // Build the label attributes array.
        $optionLabelAttributes        = array();
        $optionLabelAttributes['for'] = $optionId;

        empty($option->labelclass) ? null : $optionLabelAttributes['class'] = $option->labelclass;
        empty($option->disable) ? null : $optionLabelAttributes['disabled'] = 'disabled';

        // Build the option attributes array.
        $optionAttributes          = array();
        $optionAttributes['type']  = 'radio';
        $optionAttributes['id']    = $optionId;
        $optionAttributes['name']  = $name;
        $optionAttributes['value'] = empty($option->value) ? '' : htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');

        empty($option->class) ? null : $optionAttributes['class'] = $option->class;
        empty($option->onclick) ? null : $optionAttributes['onclick'] = $option->onclick;
        empty($option->onchange) ? null : $optionAttributes['onchange'] = $option->onchange;
        empty($option->disable) ? null : $optionAttributes['disabled'] = 'disabled';
        !($option->value === $value) ? null : $optionAttributes['checked'] = 'checked';

        $optionAttributes      = ArrayHelper::toString($optionAttributes);
        $optionLabelAttributes = ArrayHelper::toString($optionLabelAttributes);
        ?>

		<div<?php echo $fieldElementClass; ?>>
			<input <?php echo $optionAttributes; ?> />
			<label <?php echo $optionLabelAttributes ?>
                <?php if (!empty($option->optionattr)) :
                    if (version_compare(JVERSION, 4, 'lt')) {
                        HTMLHelper::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
                    } else {
                        Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('showon');
                    }

                    HTMLHelper::_('script', 'plugins/content/jtf/assets/js/jtfShowon.min.js', array('version' => 'auto'), array('defer' => 'defer'));

                    echo $option->optionattr; ?>
                <?php endif; ?>
			>
                <?php echo $option->text; ?>
			</label>
		</div>
    <?php endforeach; ?>
</fieldset>
