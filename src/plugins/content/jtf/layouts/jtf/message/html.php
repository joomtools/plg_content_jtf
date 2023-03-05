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
 * @var   int     $id
 * @var   Form    $form
 * @var   string  $fileClear
 * @var   string  $formClass
 * @var   string  $controlFields
 * @var   bool    $enctype
 */

$form->setAttribute('fileTimeOut', '');
$fieldsets = $form->getXML();

foreach ($fieldsets->fieldset as $fieldset) {
    if (!empty($fieldset['name']) && (string) $fieldset['name'] == 'submit') {
        continue;
    }

    $fieldsetLabel = (string) $fieldset['label'];

    if (count($fieldset->field)) : ?>
        <?php if (!empty($fieldsetLabel) && strlen($legend = trim(Text::_($fieldsetLabel)))) : ?>
			<h1><?php echo $legend; ?></h1>
        <?php endif; ?>

		<table cellpadding="2" border="0">
			<tbody>
            <?php foreach ($fieldset->field as $field) :
                $label = trim(Text::_((string) $field['label']));
                $value = $form->getValue((string) $field['name']);
                $type = (string) $field['type'];
                $fileTimeOut = '';

                if (!empty($field['notmail'])) {
                    continue;
                }

                if ($type == 'note') {
                    $value = trim(Text::_((string) $field['description']));
                }

                if ($type == 'file' && $fileClear > 0) {
                    $fileTimeOut .= '<tr><td colspan="2">';
                    $fileTimeOut .= Text::sprintf('JTF_FILE_TIMEOUT', $fileClear);
                    $fileTimeOut .= '</td></tr>';
                }

                if ($type == 'spacer') {
                    $label = '&nbsp;';
                    $value = trim(Text::_((string) $field['label']));
                }

                if (empty($value)) {
                    // Comment out 'continue', if you want to submit only filled fields
                    // continue;
                }

                $sublayoutValues = array(
                    'form'          => $form,
                    'value'         => $value,
                    'type'          => $type,
                    'fieldName'     => (string) $field['name'],
                    'fieldMultiple' => filter_var($field['multiple'], FILTER_VALIDATE_BOOLEAN),
                    'fileClear'     => $fileClear,
                    'fileTimeOut'   => $fileTimeOut,
                );
                ?>
				<tr>
					<th style="width:30%; text-align: left;">
                        <?php echo strip_tags($label); ?>
					</th>
					<td>
                        <?php if ($type == 'subform') {
                            echo $this->sublayout('subform', $sublayoutValues);
                        } else {
                            echo $this->sublayout('mainform', $sublayoutValues);
                        } ?>
					</td>
				</tr>
            <?php endforeach; ?>
            <?php if (empty($fileTimeOut)) {
                $fileTimeOut = $form->getAttribute('fileTimeOut', '');
            }

            echo $fileTimeOut; ?>
			</tbody>
		</table>
    <?php endif;
}
