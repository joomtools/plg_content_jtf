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
 * @var   string  $buttonicon   Classes special for the button to set an icon.
 * @var   string  $class        Classes for the input.
 * @var   string  $description  Description of the field.
 * @var   string  $heading      Tag for heading (h1, h2, h3, etc.).
 * @var   string  $label        Label of the field.
 */

// Including fallback code for HTML5 non supported browsers.
HTMLHelper::_('jquery.framework');
HTMLHelper::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true));

$class = !empty($class) ? ' class="' . $class . '"' : '';

if (!empty($close))
{
	$html[] = '<button type="button" class="uk-alert-close" uk-close></button>';
}

$html[] = !empty($label) ? '<' . $heading . '>' . $label . '</' . $heading . '>' : '';
$html[] = !empty($description) ? $description : '';

?>
<div<?php echo $class; ?> uk-alert>
	<?php echo implode('', $html); ?>
</div>
