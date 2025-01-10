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

use Joomla\CMS\Language\Text;
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

if (is_array($value)) {
    foreach ($value as $_key => $_value) {
        if ($type == 'file') {
            $values[] = $_key . ': ' . $_value . ' *';
        } else {
            $values[] = strip_tags(trim(Text::_($_value)));
        }
    }

    if (empty($values)) {
        $values = array();
    }

    $value = implode(", ", $values);
    unset($values);
}

echo !empty($value)
    ? strip_tags(Text::_($value))
    : '--';
echo "\n";

