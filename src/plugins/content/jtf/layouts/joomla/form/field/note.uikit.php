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
 * @var   string  $buttonIcon   Classes special for the button to set an icon.
 * @var   string  $class        Classes for the input.
 * @var   string  $description  Description of the field.
 * @var   string  $heading      Tag for heading (h1, h2, h3, etc.).
 * @var   string  $label        Label of the field.
 */

$class = !empty($class) ? ' class="' . $class . '"' : '';

if (!empty($close))
{
	$html[] = '<button type="button" class="' . $buttonClass . '"></button>';
}

$html[] = !empty($label) ? '<' . $heading . '>' . $label . '</' . $heading . '>' : '';
$html[] = !empty($description) ? $description : '';

?>
<div<?php echo $class; ?> data-uk-alert>
	<?php echo implode('', $html); ?>
</div>
