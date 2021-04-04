<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Joomla\CMS\Form\Rule;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Jtf\Form\Form;

/**
 * Form Rule class for the Joomla Platform
 *
 * @since  __DEPLOY_VERSION__
 */
class CalendarRule extends FormRule
{
	/**
	 * Method to test the calendar value for a valid parts.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   Registry|null      $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form|null          $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, $input = null, $form = null): bool
	{
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required' || (string) $element['required'] == '1');

		if (!$required && empty($value))
		{
			return true;
		}

		if (strtolower($value) == 'now')
		{
			return true;
		}

		try
		{
			$date = Factory::getDate($value, 'UTC');
		}
		catch (\Exception $e)
		{
			return false;
		}

		$config = Factory::getConfig();
		$user   = Factory::getUser();

		// If a known filter is given use it.
		switch (strtoupper((string) $element['filter']))
		{
			case 'SERVER_UTC':
				$date->setTimezone(new \DateTimeZone($config->get('offset')));

				break;

			case 'USER_UTC':
				$date->setTimezone($user->getTimezone());

				break;
		}

		// Transform the date string.
		$controlValue = $date->format('Y-m-d H:i:s', true, false);

		$format = !empty((string) $element['format']) ? (string) $element['format'] : '%Y-%m-%d';

		// Format value when not nulldate ('0000-00-00 00:00:00'), otherwise blank it as it would result in 1970-01-01.
		if ($controlValue != Factory::getDbo()->getNullDate() && strtotime($controlValue) !== false)
		{
			$tz = date_default_timezone_get();
			date_default_timezone_set('UTC');
			$controlValue = strftime($format, strtotime($controlValue));
			date_default_timezone_set($tz);
		}
		else
		{
			return false;
		}

		if ($value != $controlValue)
		{
			$form->bind(array((string) $element['name'] => $controlValue));
		}

		return true;
	}
}
