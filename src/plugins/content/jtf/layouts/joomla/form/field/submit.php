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
 *
 * @var   string  $buttonClass  Classes special for the button.
 * @var   string  $buttonIcon   Classes special for the button to set an icon.
 * @var   string  $id           DOM id of the field.
 * @var   string  $label        Label of the field.
 */

?>
<button id="<?php echo $id; ?>" class="validate<?php echo !empty($buttonClass) ? ' ' . $buttonClass : ''; ?>"
		type="submit">
	<?php if (!empty($buttonIcon)) : ?>
		<span class="<?php echo $buttonIcon; ?>">&nbsp;</span>
	<?php endif; ?>

	<?php echo $label; ?>
</button>
