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
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $repeat          Allows extensions to duplicate elements.
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $options         Options available for this field.
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attribute for eg, data-*.
 */

// If there are no options don't render anything
if (empty($options)) {
    return '';
}

$isBtnYesNo  = strpos(trim($class), 'btn-group-yesno') !== false;

// Build the fieldset attributes array.
$fieldsetAttributes          = [];
$fieldsetAttributes['class'] = ['radio radio-group mb-0'];
$fieldsetAttributes['id']    = $id;

// Add the fieldset class on UIKit framework
!in_array($framework, array('uikit', 'uikit3')) ? null : $fieldsetAttributes['class'][] = 'uk-fieldset';

if ($required) {
    $fieldsetAttributes['required']      = 'required';
    $fieldsetAttributes['aria-required'] = 'true';
    $fieldsetAttributes['class'][]       = 'required';
}

$fieldsetAttributes['class'] = implode(' ', $fieldsetAttributes['class']);

!$readonly ? null : $fieldsetAttributes['readonly'] = 'readonly';
!$disabled ? null : $fieldsetAttributes['disabled'] = 'disabled';
!$autofocus ? null : $fieldsetAttributes['autofocus'] = 'autofocus';

$fieldsetAttributes = ArrayHelper::toString($fieldsetAttributes);
?>
<fieldset <?php echo $fieldsetAttributes; ?><?php echo $dataAttribute ? ' ' . $dataAttribute : ''; ?>>
	<legend class="visually-hidden">
        <?php echo $label; ?>
	</legend>
	<?php
    $btnGroupElement            = [];
    $btnGroupElement['class'][] = 'btn-group mb-0';
    $btnGroupElement['class'][] = trim($class);
    $btnGroupElement['class']   = implode(' ', $btnGroupElement['class']);

    $btnGroupElement = ArrayHelper::toString($btnGroupElement);

    ?>
	<div <?php echo $btnGroupElement; ?>>
		<?php foreach ($options as $i => $option) :
			$optionId = $id . $i;

            // Build the label attributes array.
            $optionLabelAttributes          = [];
            $optionLabelAttributes['class'] = [];
            $optionLabelAttributes['for']   = $optionId;

            empty($option->labelclass) ? null : $hasBtnOutline  = strpos(trim($option->labelclass), 'btn-outline-') !== false;

//			!($option->value === $value) ? null : $optionLabelAttributes['class'][] = 'active';
            empty($option->labelclass) ? null : $optionLabelAttributes['class'][] = $option->labelclass;

            // Initialize some option attributes.
            if ($isBtnYesNo) {
                // Set the button classes for the yes/no group
                switch ($option->value) {
                    case '0':
                        $hasBtnOutline ? null : $optionLabelAttributes['class'][] = 'btn-outline-danger';
                        break;
                    case '1':
                        $hasBtnOutline ? null : $optionLabelAttributes['class'][] = 'btn-outline-success';
                        break;
                    default:
                        $hasBtnOutline ? null : $optionLabelAttributes['class'][] = 'btn-outline-secondary';
                        break;
                }
            } else {
                $hasBtnOutline ? null : $optionLabelAttributes['class'][] = 'btn-outline-secondary';
			}

            $optionLabelAttributes['class'] = implode(' ', $optionLabelAttributes['class']);

            empty($option->disable) ? null : $optionLabelAttributes['disabled'] = 'disabled';

            if ($disabled || $readonly){
				$optionLabelAttributes['style'] = 'pointer-events: none';
            }

            // Build the option attributes array.
			$optionAttributes          = [];
            $optionAttributes['class'] = [];
			$optionAttributes['type']  = 'radio';
			$optionAttributes['id']    = $optionId;
			$optionAttributes['name']  = $name;
            $optionAttributes['value'] = $option->value;

			if (!is_numeric($option->value) && !empty($option->value)) {
                $optionAttributes['value'] = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
			}

            empty($option->class) ? null : $optionAttributes['class'][] = $option->class;
            !($option->value === $value) ? null : $optionAttributes['class'][] = 'active';

            $optionAttributes['class'] = implode(' ', $optionAttributes['class']);

            empty($option->onclick) ? null : $optionAttributes['onclick'] = $option->onclick;
            empty($option->onchange) ? null : $optionAttributes['onchange'] = $option->onchange;
            empty($option->disable) ? null : $optionAttributes['disabled'] = 'disabled';
            !($option->value === $value) ? null : $optionAttributes['checked'] = 'checked';

			$optionAttributes      = ArrayHelper::toString($optionAttributes);
			$optionLabelAttributes = ArrayHelper::toString($optionLabelAttributes);
			?>

			<input <?php echo $optionAttributes; ?> />
			<label <?php echo $optionLabelAttributes ?>
				<?php if (!empty($option->optionattr)) :
					Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('showon');
					HTMLHelper::_('script', 'plugins/content/jtf/assets/js/jtfShowon.min.js', array('version' => 'auto'), array('defer' => 'defer'));

					echo $option->optionattr; ?>
				<?php endif; ?>
			>
				<?php echo $option->text; ?>
			</label>
		<?php endforeach; ?>
	</div>
</fieldset>
