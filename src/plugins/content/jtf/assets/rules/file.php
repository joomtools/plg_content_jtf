<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  11.1
 */
class JFormRuleFile extends JFormRule
{
	/**
	 * Method to test the value.
	 *
	 * @param   SimpleXMLElement $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed            $value     The form field value to validate.
	 * @param   string           $group     The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry         $input     An optional Registry object with the entire data set to validate against the entire form.
	 * @param   JForm            $form      The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		$return   = true;
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		$accept = (string) $element['accept'];

		if (!$accept)
		{
			return true;
		}

		$acceptFileType = array();
		$acceptFileMime = array();
		$accept         = explode(',', (string) $element['accept']);

		foreach ($accept as $type)
		{
			if (strpos($type, '.') !== false)
			{
				$acceptFileType[] = ltrim($type, '.');
			}
			elseif (strpos($type, '/') !== false)
			{
				$acceptFileMime[] = trim(str_replace('*', '.*', $type));
			}
		}

		$allowedType = implode('|', $acceptFileType);
		$allowedMime = implode('|', $acceptFileMime);

		foreach ($value as $file)
		{
			$test = false;

			if ($allowedMime)
			{
				$test = preg_match('@^(' . $allowedMime . ')$@i', $file['type']);
			}

			if (!$test && $allowedType)
			{
				$test = preg_match('/\.(?:' . $allowedType . ')$/i', $file['name']);
			}

			if (!$test)
			{
				$return = false;
			}
		}

		return $return;
	}
}
