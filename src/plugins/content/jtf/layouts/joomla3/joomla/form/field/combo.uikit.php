<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

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
 */

HTMLHelper::_('script', 'plugins/content/jtf/assets/js/jtfCombo.uikit.min.js', array('version' => 'auto'));

$attr = !empty($class) ? ' class="combobox ' . $class . '"' : ' class="combobox"';
$attr .= !empty($size) ? ' size="' . $size . '"' : '';
$attr .= !empty($readonly) ? ' readonly' : '';
$attr .= !empty($disabled) ? ' disabled' : '';
$attr .= !empty($required) ? ' required aria-required="true"' : '';

// Initialize JavaScript field attributes.
$attr .= !empty($onchange) ? ' onchange="' . $onchange . '"' : '';

?>
<div class="combobox">
	<input type="text"
		   name="<?php echo $name; ?>"
		   id="<?php echo $id; ?>"
		   value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"
		<?php echo $attr; ?>
		   autocomplete="off"/>
	<div class="uk-button-group uk-display-inline-block">
		<button type="button" class="uk-button">
			<span class="uk-icon-caret-down"></span>
		</button>
		<div class="uk-dropdown uk-dropdown-bottom">
			<ul class="uk-nav uk-nav-dropdown uk-display-inline-block">
				<?php foreach ($options as $option) : ?>
					<li><a href="#"><?php echo $option->text; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>
