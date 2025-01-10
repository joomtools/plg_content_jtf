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
 * @var   string  $i
 */

$subForm       = $form->getField($fieldName)->loadSubForm();
$subFormFields = $subForm->getGroup('');

foreach ($subFormFields as $subFormField) {
    $values       = ($i !== '')
        ? $value[$i]
        : $value;
    $subFormType  = $subFormField->getAttribute('type');
    $subFormLabel = $subFormField->getAttribute('label');
    $subFormName  = $subFormField->getAttribute('name');
    $subFormValue = !empty($values[$subFormName]) ? $values[$subFormName] : '';

    if (!empty($subFormField->getAttribute('notmail'))) {
        continue;
    }

    if (empty($subFormValue)) {
        // Comment out 'continue', if you want to submit only filled fields
        // continue;
    }

    if ($subFormType == 'file' && $fileTimeOut == '' && $fileClear > 0) {
        $form->setAttribute('fileTimeOut', Text::sprintf('JTF_FILE_TIMEOUT', $fileClear));
    }

    if (is_array($subFormValue)) {
        foreach ($subFormValue as $_key => $_value) {
            if ($subFormType == 'file') {
                $subFormValues[] = $_key . ': ' . $_value . ' *';
            } else {
                $subFormValues[] = strip_tags(trim(Text::_($_value)));
            }
        }

        if (empty($subFormValues)) {
            $subFormValues = array();
        }

        $subFormValue = implode("\n", $subFormValues);
        unset ($subFormValues);
    }

    echo strip_tags(Text::_($subFormLabel)) . ': ';
    echo !empty($subFormValue)
        ? strip_tags(Text::_($subFormValue))
        : '--';
    echo "\n";
}

