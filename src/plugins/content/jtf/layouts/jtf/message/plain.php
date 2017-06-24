<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/

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
			echo "====================" . "\n";
			echo $legend . "\n";
		}

		echo "====================" . "\n";

		foreach ($fieldset->field as $field)
		{
			$label = trim(JText::_((string) $field['label']));
			$value = $form->getValue((string) $field['name']);
			$type  = (string) $field['type'];

			if (!empty($field['notmail']))
			{
				continue;
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
				foreach ($value as $_value)
				{
					$values[] = trim(JText::_($_value));
				}
				$value = implode(", ", $values);
				unset($values);
			}
			else
			{
				$value = trim(JText::_($value));
			}


			echo strip_tags($label) . ": ";
			echo $value ? strip_tags($value) : '--';
			echo "\n\r";
		}
	}
}
