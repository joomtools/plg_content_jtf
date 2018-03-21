<?php
/**
 * @package          Joomla.Plugin
 * @subpackage       Content.Jtf
 *
 * @author           Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license          GNU General Public License version 3 or later
 */

defined('JPATH_BASE') or die;

extract($displayData);

/**
 * Layout variables
 * ---------------------
 *    $options         : (array)  Optional parameters
 *    $label           : (string) The html code for the label (not required if $options['hiddenLabel'] is true)
 *    $input           : (string) The input field html code
 */

?>
<div class="input-group">
		<span class="input-group-addon">
			<span class="<?php echo $icon; ?>"></span>
		</span>
	<?php echo $input; ?>
</div>
