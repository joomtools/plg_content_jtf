<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2018 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

/**
 * Form Class for the Joomla Platform.
 *
 * This class implements a robust API for constructing, populating, filtering, and validating forms.
 * It uses XML definitions to construct form fields and a variety of field and rule classes to
 * render and validate the form.
 *
 * @since   3.0.0
 */
class Form extends \Joomla\CMS\Form\Form
{
	/**
	 * Array of layoutPaths.
	 *
	 * @var     array
	 * @since   3.0.0
	 */
	public $layoutPaths = array();
	/**
	 * Array of Set enctype.
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $setEnctype = false;


	/**
	 * Method to instantiate the form object.
	 *
	 * @param   string  $name     The name of the form.
	 * @param   array   $options  An array of form options.
	 *
	 * @since   3.0.0
	 */
	public function __construct($name, array $options = array())
	{
		parent::__construct($name, $options);
	}

	/**
	 * Method to get an instance of a form.
	 *
	 * @param   string          $name     The name of the form.
	 * @param   string          $data     The name of an XML file or string to load as the form definition.
	 * @param   array           $options  An array of form options.
	 * @param   boolean         $replace  Flag to toggle whether form fields should be replaced if a field
	 *                                    already exists with the same group/name.
	 * @param   string|boolean  $xpath    An optional xpath to search for the fields.
	 *
	 * @return  Form  Form instance.
	 *
	 * @since   1.7.0
	 * @deprecated  5.0 Use the FormFactory service from the container
	 * @throws  \InvalidArgumentException if no data provided.
	 * @throws  \RuntimeException if the form could not be loaded.
	 */
	public static function getInstance($name, $data = null, $options = array(), $replace = true, $xpath = false)
	{
		if (version_compare(JVERSION, '4', 'lt'))
		{
			return parent::getInstance($name, $data, $options, $replace, $xpath);
		}

		// Reference to array with form instances
		$forms = &self::$forms;

		// Only instantiate the form if it does not already exist.
		if (!isset($forms[$name]))
		{
			$data = trim($data);

			if (empty($data))
			{
				throw new \InvalidArgumentException(sprintf('%1$s(%2$s, *%3$s*)', __METHOD__, $name, gettype($data)));
			}

			// Instantiate the form.
			$forms[$name] = Factory::getContainer()->get(FormFactoryInterface::class)->createForm($name, $options);

			// Load the data.
			if (substr($data, 0, 1) == '<')
			{
				if ($forms[$name]->load($data, $replace, $xpath) == false)
				{
					throw new \RuntimeException(sprintf('%s() could not load form', __METHOD__));
				}
			}
			else
			{
				if ($forms[$name]->loadFile($data, $replace, $xpath) == false)
				{
					throw new \RuntimeException(sprintf('%s() could not load file', __METHOD__));
				}
			}
		}

		return $forms[$name];
	}

	/**
	 * Set the value of an attribute of the form itself
	 *
	 * @param   string  $name   Name of the attribute to get
	 * @param   string  $value  Value to set for the attribute
	 *
	 * @return   void
	 * @since    3.0.0
	 */
	public function setAttribute($name, $value = null)
	{
		if ($this->xml instanceof \SimpleXMLElement)
		{
			$attributes = $this->xml->attributes();

			if (!empty($value))
			{
				// Ensure that the attribute exists
				if (empty($attributes[$name]))
				{
					$this->xml->addAttribute($name, trim($value));
				}
				else
				{
					$attributes[$name] = trim($value);
				}
			}
		}

		$this->syncPaths();
	}

	/**
	 * Reset submitted Values
	 *
	 * @return   void
	 * @since    3.0.0
	 */
	public function resetData()
	{
		$this->data = new Registry;
	}
}
