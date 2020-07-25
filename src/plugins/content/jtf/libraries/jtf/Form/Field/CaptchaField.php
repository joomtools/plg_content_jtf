<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Jtf\Form\FormFieldExtension;

/**
 * Captcha field.
 *
 * @since   3.0.0
 */
class CaptchaField extends \Joomla\CMS\Form\Field\CaptchaField
{
	use FormFieldExtension {
		setup as traitSetup;
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$app = Factory::getApplication();

		$default = $app->get('captcha');

		if ($app->isClient('site') && $this->form instanceof \Jtf\Form\Form)
		{
			$app->getParams()->set('captcha', $default);
		}

		if (!$this->traitSetup($element, $value, $group))
		{
			return false;
		}

		return parent::setup($element, $value, $group);
	}
}
