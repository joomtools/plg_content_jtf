<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Jtf\Form\Form;

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
    echo "\n====\n";

    if ($fieldMultiple) {
        $displayData['value'] = array_values($value);
        $counter              = count($value) - 1;

        for ($i = 0; $i <= $counter; $i++) {
            $displayData['i'] = $i;

            echo $this->sublayout('fields', $displayData);

            if ($i < $counter) {
                echo "\n";
            }
        }
    } else {
        $displayData['i'] = '';

        echo $this->sublayout('fields', $displayData);
    }

    echo "====\n";
} else {
    echo "--\n";
}
