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
 * @var   string  $fieldAttributes   Attributes for the field.
 * @var   string  $buttonAttributes  Attributes for the button.
 * @var   string  $dataAttribute     Additional data-attributes for the field.
 * @var   string  $buttonIcon        Icon classfor the button.
 */

?>
<div class="field-calendar">
	<div class="input-group">
		<input type="text"
			<?php echo $fieldAttributes; ?>
			<?php echo $dataAttribute; ?>
		/>
		<div class="input-group-btn">
			<button type="button" <?php echo $buttonAttributes; ?>>
				<span class="<?php echo $buttonIcon; ?>" aria-hidden="true"></span>
			</button>
		</div>
	</div>
</div>
