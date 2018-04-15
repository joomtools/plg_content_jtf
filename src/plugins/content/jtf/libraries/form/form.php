<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

//namespace Joomla\CMS\Form;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Form\Form;

JLoader::import('joomla.filesystem.path');

/**
 * Form Class for the Joomla Platform.
 *
 * This class implements a robust API for constructing, populating, filtering, and validating forms.
 * It uses XML definitions to construct form fields and a variety of field and rule classes to
 * render and validate the form.
 *
 * @link   http://www.w3.org/TR/html4/interact/forms.html
 * @link   http://www.w3.org/TR/html5/forms.html
 * @since  11.1
 */
class JTFForm extends Form
{

	/**
	 * Array of layoutPaths.
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $layoutPaths = array();

	/**
	 * Method to instantiate the form object.
	 *
	 * @param   string  $name     The name of the form.
	 * @param   array   $options  An array of form options.
	 *
	 * @since   11.1
	 */
	public function __construct($name, array $options = array())
	{
		parent::__construct($name, $options);
	}

	/**
	 * Method to validate form data.
	 *
	 * Validation warnings will be pushed into JForm::errors and should be
	 * retrieved with JForm::getErrors() when validate returns boolean false.
	 *
	 * @param   array   $data   An array of field values to validate.
	 * @param   string  $group  The optional dot-separated form group path on which to filter the
	 *                          fields to be validated.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function validate($data, $group = null)
	{
		// Make sure there is a valid JForm XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			return false;
		}

		$return = true;

		// Create an input registry object from the data to validate.
		$input = new Registry($data);

		// Get the fields for which to validate the data.
		$fields = $this->findFieldsByGroup($group);

		if (!$fields)
		{
			// PANIC!
			return false;
		}

		// Validate the fields.
		foreach ($fields as $field)
		{
			$value = null;
			$name = (string) $field['name'];

			if (!$input->exists($name))
			{
				continue;
			}

			// Get the group names as strings for ancestor fields elements.
			$attrs = $field->xpath('ancestor::fields[@name]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			$group = implode('.', $groups);

			// Get the value from the input data.
			if ($group)
			{
				$value = $input->get($group . '.' . $name);
			}
			else
			{
				$value = $input->get($name);
			}

			// Validate the field.
			$valid = $this->validateField($field, $group, $value, $input);

			// Check for an error.
			if ($valid instanceof \Exception)
			{
				$this->errors[] = $valid;
				$return         = false;
			}
		}

		return $return;
	}

	/**
	 * Set the value of an attribute of the form itself
	 *
	 * @param   string  $name   Name of the attribute to get
	 * @param   string  $value  Value to set for the attribute
	 *
	 * @return  void
	 *
	 * @since   3.7
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
}
