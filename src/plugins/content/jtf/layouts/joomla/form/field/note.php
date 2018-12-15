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

use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string  $buttonclass  Classes special for the button.
 * @var   string  $class        Classes for the input.
 * @var   string  $description  Description of the field.
 * @var   string  $heading      Tag for heading (h1, h2, h3, etc.).
 * @var   string  $label        Label of the field.
 */

// Including fallback code for HTML5 non supported browsers.
HTMLHelper::_('jquery.framework');
HTMLHelper::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true));

$role       = '';
$ariaLabel  = '';
$buttonicon = '&times;';

if (in_array($this->getOptions()->get('suffixes')[0], array('bs3', 'bs4',)))
{
	$role       = ' role="alert"';
	$ariaLabel  = ' aria-label="Close"';
	$buttonicon = '<span aria-hidden="true">&times;</span>';
}

$class = !empty($class) ? ' class="' . $class . '"' : '';
$close = $close == 'true' ? 'alert' : $close;

if (!empty($close))
{
	$html[] = '<button type="button" class="' . $buttonclass . '" data-dismiss="' . $close . '"' . $ariaLabel . '>' . $buttonicon . '</button>';
}

$html[] = !empty($label) ? '<' . $heading . '>' . $label . '</' . $heading . '>' : '';
$html[] = !empty($description) ? $description : '';

?>
<div<?php echo $class . $role; ?>>
	<?php echo implode('', $html); ?>
</div>
