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
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   boolean  $hasValue        Has this field a value assigned?
 */

HtmlHelper::_('script', 'plugins/content/jtf/assets/js/jtfPlz.min.js', array('version' => 'auto'), array('defer' => 'defer'));

$autocomplete = !$autocomplete ? 'autocomplete="off"' : 'autocomplete="' . $autocomplete . '"';
$autocomplete = $autocomplete === 'autocomplete="on"' ? '' : $autocomplete;

$hint = Text::_($hint);

$attributes = array(
	!empty($class) ? 'class="validate-plz ' . $class . '"' : 'class="validate-plz"',
	!empty($size) ? 'size="' . $size . '"' : '',
	'maxlength="5"',
	$disabled ? 'disabled' : '',
	$readonly ? 'readonly' : '',
	strlen($hint) ? 'placeholder="' . htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') . '"' : '',
	$onchange ? 'onchange="' . $onchange . '"' : '',
	$required ? 'required aria-required="true"' : '',
	$autocomplete,
	$autofocus ? 'autofocus' : '',
	'spellcheck="false"',
	!empty($pattern) ? 'pattern="' . $pattern . '"' : '',
);
?>
<input type="text"
	   name="<?php echo $name; ?>"
	   id="<?php echo $id; ?>"
	   value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"
	<?php echo implode(' ', $attributes); ?>
/>
