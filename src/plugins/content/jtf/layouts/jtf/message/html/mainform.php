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

use Joomla\CMS\Language\Text;
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

if (is_array($value)) {
    foreach ($value as $_key => $_value) {
        if ($type == 'file') {
            $values[] = '<a href="' . $_value . '" download>' . $_key . '</a> *';
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
    ? nl2br(Text::_($value))
    : '--';

