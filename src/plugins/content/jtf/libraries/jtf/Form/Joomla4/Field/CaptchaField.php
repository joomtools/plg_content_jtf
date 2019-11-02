<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2018 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Field;

defined('JPATH_PLATFORM') or die;

use Jtf\Form\FormField;
use Jtf\Captcha\Captcha;

/**
 * Captcha field.
 *
 * @since   3.0.0
 */
class CaptchaField extends FormField
{
	/**
	 * The field type.
	 *
	 * @var     string
	 * @since   3.0.0
	 */
	protected $type = 'Captcha';

	/**
	 * The captcha base instance of our type.
	 *
	 * @var     Captcha
	 * @since   3.0.0
	 */
	protected $_captcha;

	/**
	 * Global application object
	 *
	 * @var     \Joomla\CMS\Application\CMSApplication
	 * @since   3.0.0
	 */
	protected $app = null;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 * @since   3.0.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'plugin':
			case 'namespace':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return   void
	 * @since    3.0.0
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'plugin':
			case 'namespace':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return   boolean  True on success.
	 * @since    3.0.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);
		$default = $this->app->get('captcha');

		if ($this->app->isClient('site') && !$this->form instanceof \Jtf\Form\Form)
		{
			$default = $this->app->getParams()->get('captcha', $default);
		}

		$plugin = $this->element['plugin'] ?
			(string) $this->element['plugin'] :
			$default;

		$this->plugin = $plugin;

		if ($plugin === 0 || $plugin === '0' || $plugin === '' || $plugin === null)
		{
			$this->hidden = true;

			return false;
		}
		else
		{
			// Force field to be required. There's no reason to have a captcha if it is not required.
			// Obs: Don't put required="required" in the xml file, you just need to have validate="captcha"
			$this->required = true;

			if (strpos($this->class, 'required') === false)
			{
				$this->class .= ' required';
			}
		}

		$this->namespace = $this->element['namespace'] ? (string) $this->element['namespace'] : $this->form->getName();

		try
		{
			// Get an instance of the captcha class that we are using
			$this->_captcha = Captcha::getInstance($this->plugin, array('namespace' => $this->namespace));

			/**
			 * Give the captcha instance a possibility to react on the setup-process,
			 * e.g. by altering the XML structure of the field, for example hiding the label
			 * when using invisible captchas.
			 */
			$this->_captcha->setupField($this, $element);
		}
		catch (\RuntimeException $e)
		{
			$this->_captcha = null;
			$this->app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $result;
	}

	/**
	 * Method to get the field input.
	 *
	 * @return   string  The field input.
	 * @since    3.0.0
	 */
	protected function getInput()
	{
		if ($this->hidden || $this->_captcha == null)
		{
			return '';
		}

		try
		{
			return $this->_captcha->display($this->name, $this->id, $this->class);
		}
		catch (\RuntimeException $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
		}
		return '';
	}
}
