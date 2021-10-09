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
 * @var   array   $fieldAttributes   Attributes for the field.
 * @var   array   $buttonAttributes  Attributes for the button.
 * @var   string  $buttonIcon        Icon classfor the button.
 */

?>
<div class="field-calendar">
	<div class="uk-inline">
		<input type="text" <?php echo $fieldAttributes; ?> />
		<button type="button" <?php echo $buttonAttributes; ?>>
			<span uk-icon="icon: <?php echo $buttonIcon; ?>"></span>
		</button>
	</div>
</div>
