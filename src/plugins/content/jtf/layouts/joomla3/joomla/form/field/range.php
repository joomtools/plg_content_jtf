<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

extract($displayData);

/**
 * Layout variables
 * -----------------
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
 */

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));

// Initialize some field attributes.
$attributes = array();
$class ? $attributes['class'] = $class : null;
$disabled ? $attributes['disabled'] = 'disabled' : null;
$readonly ? $attributes['readonly'] = 'readonly' : null;
$autofocus ? $attributes['autofocus'] = 'autofocus' : null;
!empty($onchange) ? $attributes['onchange'] = $onchange : null;
!empty($max) ? $attributes['max'] = $max : null;
!empty($step) ? $attributes['step'] = $step : null;
!empty($min) ? $attributes['min'] = $min : null;
$value = is_numeric($value) ? (float) $value : $min;
?>
<input type="range"
	   name="<?php echo $name; ?>"
	   id="<?php echo $id; ?>"
	   value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"
	   oninput="this.nextElementSibling.value = this.value"
	<?php echo ArrayHelper::toString($attributes); ?>
	    />
<output><?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?></output><?php
echo $description ? ' ' . htmlspecialchars($description, ENT_COMPAT, 'UTF-8') : null; ?>