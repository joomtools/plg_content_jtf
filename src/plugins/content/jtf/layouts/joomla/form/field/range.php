<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2025 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

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
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 * @var   array    $inputType       Options available for this field.
 * @var   string   $accept          File types that are accepted.
 * @var   string   $dataAttribute   Miscellaneous data attributes preprocessed for HTML output
 * @var   array    $dataAttributes  Miscellaneous data attribute for eg, data-*.
 */

HTMLHelper::_('stylesheet', 'plg_content_jtf/jtfRange.min.css', ['version' => 'auto', 'relative' => true]);

if (version_compare(JVERSION, '4', 'lt')) {
    // Including fallback code for HTML5 non-supported browsers.
    HTMLHelper::_('jquery.framework');
    HTMLHelper::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));
}

// Initialize some field attributes.
$attributes          = array();
$attributes['class'] = $class ? 'form-range ' . $class : 'form-range';
$disabled ? $attributes['disabled'] = 'disabled' : null;
$readonly ? $attributes['readonly'] = 'readonly' : null;
$autofocus ? $attributes['autofocus'] = 'autofocus' : null;
!empty($onchange) ? $attributes['onchange'] = $onchange : null;
!empty($max) ? $attributes['max'] = $max : null;
!empty($step) ? $attributes['step'] = $step : null;
!empty($min) ? $attributes['min'] = $min : null;
$value = is_numeric($value) ? (float) $value : $min;
?>
	<input
		type="range"
		name="<?php echo $name; ?>"
		id="<?php echo $id; ?>"
		oninput="this.nextElementSibling.value=this.value"
		value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"
        <?php echo ArrayHelper::toString($attributes); ?>
        <?php if (version_compare(JVERSION, '4', 'ge')) : ?>
            <?php echo $dataAttribute; ?>
        <?php endif; ?>
	/>
	<output class="range-desc"><?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?></output>
<?php echo $description ? ' <span class="range-desc">' . htmlspecialchars($description, ENT_COMPAT, 'UTF-8') . '</span>' : null; ?>
