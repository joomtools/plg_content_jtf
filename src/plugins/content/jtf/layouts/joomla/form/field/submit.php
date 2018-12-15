<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2018 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('JPATH_BASE') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete  Autocomplete attribute for the field.
 * @var   boolean  $autofocus     Is autofocus enabled?
 * @var   string   $buttonclass   Classes special for the button.
 * @var   string   $buttonicon    Classes special for the button to set an icon.
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

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true));
?>
<button id="<?php echo $id; ?>" class="validate<?php echo !empty($buttonclass) ? ' ' . $buttonclass : ''; ?>"
		type="submit">
	<?php if (!empty($buttonicon)) : ?>
		<span class="<?php echo $buttonicon; ?>">&nbsp;</span>
	<?php endif; ?>

	<?php echo $label; ?>
</button>
