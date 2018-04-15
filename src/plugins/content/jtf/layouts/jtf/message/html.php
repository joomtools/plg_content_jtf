<?php
/**
 * @package          Joomla.Plugin
 * @subpackage       Content.Jtf
 *
 * @author           Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license          GNU General Public License version 3 or later
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

	if (count($fieldset->field)) : ?>
		<?php if (!empty($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel)))) : ?>
			<h1><?php echo $legend; ?></h1>
		<?php endif; ?>

		<table cellpadding="2" border="1">
			<tbody>
			<?php foreach ($fieldset->field as $field) :
				$label = trim(JText::_((string) $field['label']));
				$value = $form->getValue((string) $field['name']);
				$type = (string) $field['type'];
				$fileTimeOut = '';

				if (!empty($field['notmail']))
				{
					continue;
				}

				if ($type == 'file' && $fileClear > 0)
				{
					$fileTimeOut .= '<tr><td colspan="2">';
					$fileTimeOut .= JText::sprintf('JTF_FILE_TIMEOUT', $fileClear);
					$fileTimeOut .= '</td></tr>';
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
								$values[] = '<a href="' . $_value . '" download>' . $_key . '</a> *';
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
				?>
				<tr>
					<th style="width:30%; text-align: left;">
						<?php echo strip_tags($label); ?>
					</th>
					<td>
					<?php if (!is_array($value))
					{
						echo strip_tags($value);
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
								$setTable = true; ?>
								<table cellpadding="2" border="1">
								<tbody>
								<?php
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
									$subFormType  = $subFormField->getAttribute('type');
									$subFormLabel = $subFormField->getAttribute('label');
									$subFormName  = $subFormField->getAttribute('name');
									$subFormValue = $form->getValue($fieldname . '.' . $valuesKey . '.' . $subFormName);

									if ($subFormType == 'file' && $fileTimeOut == '' && $fileClear > 0)
									{
										$fileTimeOut .= '<tr><td colspan="2">';
										$fileTimeOut .= JText::sprintf('JTF_FILE_TIMEOUT', $fileClear);
										$fileTimeOut .= '</td></tr>';
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
												$subFormValues[] = '<a href="' . $_value . '" download>' . $_key . '</a> *';
											}
											else
											{
												$subFormValues[] = strip_tags(trim(JText::_($_value)));
											}
										}

										$subFormValue = implode(", ", $subFormValues);
										unset($subFormValues);
									} ?>
									<tr>
										<th style="width:30%; text-align: left;">
											<?php echo strip_tags(JText::_($subFormLabel)); ?>
										</th>
										<td><?php echo $subFormValue
												? nl2br(strip_tags(JText::_($subFormValue)))
												: '--'; ?>
										</td>
									</tr>
									<?php
								}

								if ($valuesKey < $counter)
								{
									?>
									<tr>
										<td colspan="2">&nbsp;</td>
									</tr>
									<?php
								}

							}

							if ($setTable)
							{
								?>
								</tbody>
								</table>
								<?php
							}
						}
					} ?>
					</td>
				</tr>
				<?php echo $fileTimeOut; ?>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif;
}
