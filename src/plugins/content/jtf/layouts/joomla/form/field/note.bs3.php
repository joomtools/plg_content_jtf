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
 * @var   string  $buttonclass  Classes special for the button.
 * @var   string  $class        Classes for the input.
 * @var   string  $description  Description of the field.
 * @var   string  $label        Label of the field.
 */

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true));

$role  = ' role="alert"';
$class = !empty($class) ? ' class="' . $class . '"' : '';
$close = $close == 'true' ? 'alert' : $close;

if (!empty($close))
{
	$html[] = '<button type="button" class="close ' . $buttonclass . '" data-dismiss="' . $close . '">';
	$html[] = '<span aria-hidden="true">&times;</span>';
	$html[] = '</button>';
}

$html[] = !empty($label) ? '<' . $heading . '>' . $label . '</' . $heading . '>' : '';
$html[] = !empty($description) ? $description : '';

?>
<div<?php echo $class . $role; ?>>
	<?php echo implode('', $html); ?>
</div>
