<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
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
 */

if (is_array($value))
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

	if (empty($values))
	{
		$values = array();
	}

	$value = implode(", ", $values);
	unset($values);

}

echo !empty($value)
	? strip_tags(JText::_($value))
	: '--';
echo "\n";

