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
 * @var   array   $fieldAttributes   Attributes for the field.
 * @var   array   $buttonAttributes  Attributes for the button.
 * @var   string  $buttonIcon        Icon classfor the button.
 */

?>
<div class="field-calendar">
	<div class="input-append">
		<input type="text" <?php echo $fieldAttributes; ?> />
		<div class="btn-group">
			<button type="button" <?php echo $buttonAttributes; ?>>
				<span class="<?php echo $buttonIcon; ?>"></span>
			</button>
		</div>
	</div>
</div>
