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
 * @var   string   $class         Classes for the input.
 * @var   string   $close         Set close icon for the notice.
 * @var   string   $description   Description of the field.
 * @var   string   $label         Label of the field.
 * @var   string   $heading       Heading h1-h6.
 */

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true));

$class = !empty($class) ? ' class="' . $class . '"' : '';

if (!empty($close))
{
	$html[] = '<button type="button" class="uk-alert-close uk-close"></button>';
}

$html[] = !empty($label) ? '<' . $heading . '>' . $label . '</' . $heading . '>' : '';
$html[] = !empty($description) ? $description : '';

?>
<div<?php echo $class; ?> data-uk-alert>
	<?php echo implode('', $html); ?>
</div>
