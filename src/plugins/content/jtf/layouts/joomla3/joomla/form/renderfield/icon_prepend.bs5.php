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
 * ---------------------
 * @var   string  $icon   Icon class.
 * @var   string  $input  The input field html code.
 */

?>
<div class="input-group">
	<div class="input-group-text">
		<span class="<?php echo $icon; ?>"></span>
	</div>
	<?php echo $input; ?>
</div>
