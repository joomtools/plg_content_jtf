<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('JPATH_BASE') or die;

extract($displayData);

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'system/core.js', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'plugins/content/jtf/assets/js/combo.uikit.js', array('version' => 'auto'));

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
			<ul class="uk-nav uk-nav-dropdown">
				<?php foreach ($options as $option) : ?>
					<li><a href="#"><?php echo $option->text; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</div>
