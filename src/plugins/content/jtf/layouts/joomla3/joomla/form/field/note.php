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

$role        = '';
$ariaLabel   = '';
$buttonIcon  = '&times;';
$close       = $close == 'true' ? 'alert' : $close;
$dataDismiss = ' data-dismiss="' . $close . '"';

if (in_array($this->getOptions()->get('suffixes')[0], array('bs3', 'bs4', 'bs5')))
{
	$role        = ' role="alert"';
	$ariaLabel   = ' aria-label="Close"';
	$buttonIcon  = '<span aria-hidden="true">&times;</span>';
}

if ($this->getOptions()->get('suffixes')[0] == 'bs5')
{
	$class       = !empty($class) ? $class . ' alert-dismissible fade show' : 'alert-dismissible fade show';
	$buttonClass = 'btn-close';
	$dataDismiss = ' data-bs-dismiss="' . $close . '"';
	$buttonIcon  = '';
}

$class = !empty($class) ? ' class="' . $class . '"' : '';

if (!empty($close))
{
	$html[] = '<button type="button" class="' . $buttonClass . '"' . $dataDismiss . $ariaLabel . '>' . $buttonIcon . '</button>';
}

$html[] = !empty($label) ? '<' . $heading . '>' . $label . '</' . $heading . '>' : '';
$html[] = !empty($description) ? $description : '';

?>
<div<?php echo $class . $role; ?>>
	<?php echo implode('', $html); ?>
</div>
