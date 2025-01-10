<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2025 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use JoomTools\Plugin\Content\Jtf\Form\Form;

extract($displayData);

/**
 * Layout variables
 * ---------------------
 *
 * @var   Form    $form
 * @var   mixed   $value
 * @var   string  $type
 * @var   string  $fieldName
 * @var   string  $fileClear
 * @var   string  $fileTimeOut
 * @var   bool    $fieldMultiple
 */

if (!empty($value)) {
    ?>
	<table cellpadding="2" border="1">
		<tbody>
        <?php if ($fieldMultiple) {
            $displayData['value'] = array_values($value);
            $counter              = count($value) - 1;

            for ($i = 0; $i <= $counter; $i++) {
                $displayData['i'] = $i;

                echo $this->sublayout('fields', $displayData);

                if ($i < $counter) {
                    ?>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
                    <?php
                }
            }
        } else {
            $displayData['i'] = '';

            echo $this->sublayout('fields', $displayData);
        } ?>
		</tbody>
	</table>
    <?php
} else {
    echo '--';
}
