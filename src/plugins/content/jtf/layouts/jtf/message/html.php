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
use JoomTools\Plugin\Content\Jtf\Form\Form;

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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
	  xmlns:v="urn:schemas-microsoft-com:vml"
	  xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0 " />
	<meta name="format-detection" content="telephone=no"/>
	<style type="text/css">
		body { -webkit-text-size-adjust: 100% !important; -ms-text-size-adjust: 100% !important; -webkit-font-smoothing: antialiased !important; }
		table { border-collapse: collapse; mso-table-lspace: 0px; mso-table-rspace: 0px; }
		td, a, span { border-collapse: collapse; mso-line-height-rule: exactly; }
	</style>
	<!--[if gte mso 9]><xml>
		<o:OfficeDocumentSettings>
			<o:AllowPNG/>
			<o:PixelsPerInch>96</o:PixelsPerInch>
		</o:OfficeDocumentSettings>
	</xml><![endif]-->
</head>
<body>
<?php foreach ($fieldsets->fieldset as $fieldset) {
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
    <?php endif; } ?>
</body>
</html>
