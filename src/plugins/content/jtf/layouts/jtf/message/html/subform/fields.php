<?php
/**
 * @package          Joomla.Plugin
 * @subpackage       Content.Jtf
 *
 * @author           Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2018 JoomTools.de - All rights reserved.
 * @license          GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * ---------------------
 * @var   JForm   $form
 * @var   mixed   $value
 * @var   string  $type
 * @var   string  $fieldName
 * @var   string  $fileClear
 * @var   string  $fileTimeOut
 * @var   bool    $fieldMultiple
 * @var   string  $i
 * @var   string  $type
 */

$subForm       = $form->getField($fieldName)->loadSubForm();
$subFormFields = $subForm->getGroup('');

foreach ($subFormFields as $subFormField)
{
	$values       = ($i !== '')
		? $value[$i]
		: $value;
	$subFormType  = $subFormField->getAttribute('type');
	$subFormLabel = $subFormField->getAttribute('label');
	$subFormName  = $subFormField->getAttribute('name');
	$subFormValue = !empty($values[$subFormName]) ? $values[$subFormName] :'';

	if (!empty($subFormField->getAttribute('notmail')))
	{
		continue;
	}

	if (empty($subFormValue))
	{
		// Comment out 'continue', if you want to submit only filled fields
		// continue;
	}

	if ($subFormType == 'file' && $fileTimeOut == '' && $fileClear > 0)
	{
		$fileTimeOut .= '<tr><td colspan="2">';
		$fileTimeOut .= JText::sprintf('JTF_FILE_TIMEOUT', $fileClear);
		$fileTimeOut .= '</td></tr>';

		$form->setAttribute('fileTimeOut', $fileTimeOut);
	}

	if (is_array($subFormValue))
	{
		foreach ($subFormValue as $_key => $_value)
		{
			if ($subFormType == 'file')
			{
				$subFormValues[] = '<a href="' . $_value . '" download>' . $_key . '</a> *';
			}
			else
			{
				$subFormValues[] = strip_tags(trim(JText::_($_value)));
			}
		}

		if (empty($subFormValues))
		{
			$subFormValues = array();
		}

		$subFormValue = implode(", ", $subFormValues);
		unset ($subFormValues);

	} ?>
	<tr>
		<th style="width:30%; text-align: left;">
			<?php echo strip_tags(JText::_($subFormLabel)); ?>
		</th>
		<td><?php echo !empty($subFormValue)
				? nl2br(JText::_($subFormValue))
				: '--'; ?>
		</td>
	</tr>
	<?php
}

