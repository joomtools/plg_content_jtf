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

	if (count($fieldset->field)) : ?>
		<?php if (!empty($fieldsetLabel) && strlen($legend = trim(JText::_($fieldsetLabel)))) : ?>
            <h1><?php echo $legend; ?></h1>
		<?php endif; ?>

        <table cellpadding="2" border="1">
            <tbody>
			<?php foreach ($fieldset->field as $field) :
				$label       = trim(JText::_((string) $field['label']));
				$value       = $form->getValue((string) $field['name']);
				$type        = (string) $field['type'];
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
				else
				{
					$value = trim(JText::_($value));
				} ?>
                <tr>
                    <th style="width:30%; text-align: left;">
						<?php echo strip_tags($label); ?>
					</th>
					<td><?php echo $value ? nl2br(strip_tags($value)) : '--';

						if ($type == 'subform')
						{

							$formname   = $form->getFormControl();
							$fieldname  = (string) $field['name'];
							$formsource = (string) $field['formsource'];
							$setTable = false;
							$counter =count($value) -1;

							if (!empty($value))
							{
								$setTable = true; ?>
								<table cellpadding="2" border="1">
								<tbody>
								<?php
							}


							foreach ($value as $valuesKey => $subValues)
							{
								$control    = $formname . '[' . $valuesKey . ']';
								$subForm = $form::getInstance(
									'subform.' . $valuesKey,
									$formsource,
									array('control' => $control)
								);

								foreach ($subForm->getGroup('') as $subFormField)
								{
									$subFormLabel = $subFormField->getAttribute('label');
									$subFormValue = $subFormField->value;

									if (empty($subFormValue))
									{
										// Comment out 'continue', if you want to submit only filled fields
										//continue;
									}

									if (is_array($subFormValue))
									{
										$subFormValue = trim($subFormValue[0]);
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
										<td colspan="2"><?php echo $subFormValue
												? nl2br(strip_tags(JText::_($subFormValue)))
												: '--'; ?>
										</td>
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
						?></td>
				</tr>
				<?php echo $fileTimeOut; ?>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif;
}
