<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Rule;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Jtf\Form\Form;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  3.0.0
 */
class ColorRule extends FormRule
{
	/**
	 * Method to test for a valid color in hexadecimal.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   null               $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   Registry|null      $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form|null          $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since  3.0.0
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null): bool
	{
		$value = trim($value);

		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		if ($value == 'none' || $value == 'transparent')
		{
			return true;
		}

		if ($value[0] != '#')
		{
			return false;
		}

		// Remove the leading # if present to validate the numeric part
		$value = ltrim($value, '#');

		// The value must be 6 or 3 characters long
		if (!((strlen($value) == 6 || strlen($value) == 3) && ctype_xdigit($value)))
		{
			return false;
		}

		return true;
	}
}
