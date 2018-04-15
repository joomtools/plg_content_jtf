<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

extract($displayData);

$fieldsets = $form->getXML();

foreach ($fieldsets->fieldset as $fieldset)
{
	if (!empty($fieldset['name']) && (string) $fieldset['name'] == 'submit')
	{
		continue;
	}

	$fieldsetLabel = (string) $fieldset['label'];

	if (count($fieldset->field))
	{
		if (!empty($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel))))
		{
			echo "====================\n";
			echo $legend . "\n";
		}

		echo "====================\n";

		foreach ($fieldset->field as $field)
		{
			$label = trim(JText::_((string) $field['label']));
			$value = $form->getValue((string) $field['name']);
			$type  = (string) $field['type'];
			$fileTimeOut = '';

			if (!empty($field['notmail']))
			{
				continue;
			}

			if ($type == 'file' && $fileClear > 0)
			{
				$fileTimeOut .= JText::sprintf('JTF_FILE_TIMEOUT', $fileClear);
			}

			if ($type == 'spacer')
			{
				$label = '&nbsp;';
				$value = trim(JText::_((string) $field['label']));
			}

			if (empty($value))
			{
				// Comment out 'continue', if you want to submit only filled fields
				//continue;
			}

			if (is_array($value))
			{
				if ($type != 'subform')
				{
					foreach ($value as $_key => $_value)
					{
						if ($type == 'file')
						{
							$values[] = $_key . ': ' . $_value . ' *';
						}
						else
						{
							$values[] = strip_tags(trim(JText::_($_value)));
						}
					}

					$value = implode(", ", $values);
					unset($values);
				}
			}

			echo strip_tags($label) . ": ";

			if (!is_array($value))
			{
				echo $value ? strip_tags($value) : '--';
			}
			else
			{
				if ($type == 'subform')
				{
					$formname   = $form->getFormControl();
					$fieldname  = (string) $field['name'];
					$formsource = (string) $field['formsource'];
					$setTable   = false;
					$counter    = count($value) - 1;

					if (!empty($value))
					{
						$setTable = true;
						echo "\n====\n";

					}

					foreach ($value as $valuesKey => $subValues)
					{
						$control = $formname . '[' . $valuesKey . ']';
						$subForm = $form::getInstance(
							'subform.' . $valuesKey,
							$formsource,
							array('control' => $control)
						);

						foreach ($subForm->getGroup('') as $subFormField)
						{
							$subFormType = $subFormField->getAttribute('type');
							$subFormLabel = $subFormField->getAttribute('label');
							$subFormName = $subFormField->getAttribute('name');
							$subFormValue = $form->getValue($fieldname . '.' . $valuesKey . '.' . $subFormName);

							if ($subFormType == 'file' && $fileTimeOut == '' && $fileClear > 0)
							{
								$fileTimeOut .= JText::sprintf('JTF_FILE_TIMEOUT', $fileClear);
							}

							if (empty($subFormValue))
							{
								// Comment out 'continue', if you want to submit only filled fields
								//continue;
							}

							if (is_array($subFormValue))
							{
								foreach ($subFormValue as $_key => $_value)
								{
									if ($subFormType == 'file')
									{
										$subFormValues[] = $_key . ': ' . $_value . ' *';
									}
									else
									{
										$subFormValues[] = strip_tags(trim(JText::_($_value)));
									}
								}

								$subFormValue = implode(", ", $subFormValues);
								unset($subFormValues);
							}

							echo strip_tags(JText::_($subFormLabel)) . ': ';
							echo $subFormValue ? strip_tags(JText::_($subFormValue)) : '--';
							echo "\n";
						}

						if ($valuesKey < $counter)
						{
							echo "\n";
						}
					}

					if ($setTable)
					{
						echo "====\n";
					}
				}
				else
				{
					foreach ($value as $_value)
					{
						echo $_value ? strip_tags($_value) : '--';
						echo "\n";
					}
				}
			}

			echo "\n";
			echo $fileTimeOut;
		}
	}
}
