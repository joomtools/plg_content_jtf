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
 * ---------------------
 * @var   string  $icon   Icon class.
 * @var   string  $input  The input field html code.
 */

?>
<div class="uk-inline uk-width-1-1">
	<span class="uk-form-icon" uk-icon="icon: <?php echo $icon; ?>"></span>
	<?php echo $input; ?>
</div>

