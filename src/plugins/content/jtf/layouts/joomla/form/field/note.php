<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('JPATH_BASE') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string  $buttonClass  Classes special for the button.
 * @var   string  $class        Classes for the input.
 * @var   string  $description  Description of the field.
 * @var   string  $heading      Tag for heading (h1, h2, h3, etc.).
 * @var   string  $label        Label of the field.
 */

$role       = '';
$ariaLabel  = '';
$buttonIcon = '&times;';

if (in_array($this->getOptions()->get('suffixes')[0], array('bs3', 'bs4',)))
{
	$role       = ' role="alert"';
	$ariaLabel  = ' aria-label="Close"';
	$buttonIcon = '<span aria-hidden="true">&times;</span>';
}

$class = !empty($class) ? ' class="' . $class . '"' : '';
$close = $close == 'true' ? 'alert' : $close;

if (!empty($close))
{
	$html[] = '<button type="button" class="' . $buttonClass . '" data-dismiss="' . $close . '"' . $ariaLabel . '>' . $buttonIcon . '</button>';
}

$html[] = !empty($label) ? '<' . $heading . '>' . $label . '</' . $heading . '>' : '';
$html[] = !empty($description) ? $description : '';

?>
<div<?php echo $class . $role; ?>>
	<?php echo implode('', $html); ?>
</div>
