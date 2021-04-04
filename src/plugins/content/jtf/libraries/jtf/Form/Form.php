<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\Form as JForm;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Form Class for the Joomla Platform.
 *
 * This class implements a robust API for constructing, populating, filtering, and validating forms.
 * It uses XML definitions to construct form fields and a variety of field and rule classes to
 * render and validate the form.
 *
 * @since  __DEPLOY_VERSION__
 */
class Form extends JForm
{
	/**
	 * The form object errors array.
	 *
	 * @var   array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $subFormErrors = array();

	/**
	 * Array of layoutPaths.
	 *
	 * @var   array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $layoutPaths = array();

	/**
	 * Array of frameworks.
	 *
	 * @var   array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $framework = array();

	/**
	 * Set enctype.
	 *
	 * @var   boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $setEnctype = false;

	/**
	 * Set the option to display the required field description.
	 *
	 * @var   boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $showRequiredFieldDescription = true;

	/**
	 * Set the value for field description type.
	 *
	 * @var   string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $showfielddescriptionas = 'text';

	/**
	 * Set the value for field marker.
	 *
	 * @var   string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $fieldmarker = 'optional';

	/**
	 * Set the value for field marker place.
	 *
	 * @var   string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $fieldmarkerplace = 'field';

	/**
	 * Method to instantiate the form object.
	 *
	 * @param   string  $name     The name of the form.
	 * @param   array   $options  An array of form options.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(string $name, array $options = array())
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
	 * @since  __DEPLOY_VERSION__
	 *
	 * @throws  \InvalidArgumentException if no data provided.
	 * @throws  \RuntimeException if the form could not be loaded.
	 */
	public static function getInstance($name, $data = null, $options = array(), $replace = true, $xpath = false): Form
	{
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
			$forms[$name] = new self($name, $options);

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
	 * @param   null    $value  Value to set for the attribute
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setAttribute(string $name, $value = null)
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
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function resetData()
	{
		$this->data = new Registry;
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
	 * @since  __DEPLOY_VERSION__
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
			$name  = (string) $field['name'];

			// Get the group names as strings for ancestor fields elements.
			$attrs  = $field->xpath('ancestor::fields[@name]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			$group  = implode('.', $groups);

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
			if ($valid !== true)
			{
				$this->setErrors($valid);
				$return = false;
			}
		}

		return $return;
	}
	/**
	 * Method to validate a JFormField object based on field data.
	 *
	 * @param   \SimpleXMLElement  $element  The XML element object representation of the form field.
	 * @param   string             $group    The optional dot-separated form group path on which to find the field.
	 * @param   mixed              $value    The optional value to use as the default for the field.
	 * @param   Registry|null      $input    An optional Registry object with the entire data set to validate
	 *                                       against the entire form.
	 *
	 * @return  boolean|\Exception  Boolean true if field value is valid, Exception on failure.
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 * @throws  \RuntimeException
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function validateField(\SimpleXMLElement $element, $group = null, $value = null, Registry $input = null)
	{
		$name = (string) $element['name'];

		if (!empty($showOn = (string) $element['showon']))
		{
			$isShown = $this->isFieldShown($showOn);

			// Remove required flag before the validation, if field is not shown
			if (!$isShown)
			{
				$element['required'] = 'false';
				$element['disabled'] = 'true';

				if ($input)
				{
					$fieldExistsInRequestData = $input->exists($name) || $input->exists($group . '.' . $name);

					if ($fieldExistsInRequestData)
					{
						if ($input->exists($name))
						{
							$input->set($name, '');
						}

						if ($input->exists($group . '.' . $name))
						{
							$input->set($group . '.' . $name, '');
						}
					}
				}
			}
		}

		$valid = parent::validateField($element, $group, $value, $input);

		if ($valid instanceof \Exception && (string) $element['type'] === 'subform')
		{
			// Get the subform errors.
			$errors = $this->getErrors($name);
			$errors = array_unique($errors, SORT_STRING);

			// Merge the errors.
			$valid = array($valid);
			$valid = array_merge($valid, $errors);
		}

		return $valid;
	}

	/**
	 * Evaluates whether the field was displayed
	 *
	 * @param   string  $showOn  The value of the show on attribute.
	 *
	 * @return  boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function isFieldShown(string $showOn): bool
	{
		$regex = array(
			'search' => array(
				'[AND]',
				'[OR]',
			),
			'replace' => array(
				' [AND]',
				' [OR]',
			),
		);

		$showOn       = str_replace($regex['search'], $regex['replace'], $showOn);
		$showOnValues = explode(' ', $showOn);

		return $this->fieldIsShownValidation($showOnValues);
	}

	/**
	 * Evaluate showon values
	 *
	 * @param   string[]  $values  Array of strings with show on name:value pair
	 *
	 * @return  boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function fieldIsShownValidation(array $values): bool
	{
		$valuesSum      = count($values) - 1;
		$conditionValid = array();
		$values         = (array) $values;
		$isShown        = false;

		if (empty($values))
		{
			return false;
		}

		foreach ($values as $key => $value)
		{
			$not       = false;
			$glue      = '';
			$separator = ':';

			if (strpos($value, '[OR]') !== false)
			{
				$glue      = 'or';
				$value = strtr($value, array('[OR]' => ''));
			}

			if (strpos($value, '[AND]') !== false)
			{
				$glue = 'and';
				$value = strtr($value, array('[AND]' => ''));
			}

			if (strpos($value, '!') !== false)
			{
				$not       = true;
				$separator = '!:';
			}

			list($fieldName, $expectedValue) = explode($separator, $value);

			$fieldValue      = (array) $this->getValue($fieldName);
			$valueValidation = (($not === false && in_array($expectedValue, $fieldValue))
				|| ($not === true && !in_array($expectedValue, $fieldValue)));

			if ($glue === '')
			{
				if ((int) $key === (int) $valuesSum)
				{
					return $valueValidation;
				}

				$conditionValid[$key] = $valueValidation;
			}

			if ($glue == 'and')
			{
				$isShown              = $conditionValid[$key - 1] && $valueValidation;
				$conditionValid[$key] = $isShown;
			}

			if ($glue == 'or')
			{
				$isShown              = $conditionValid[$key - 1] || $valueValidation;
				$conditionValid[$key] = $isShown;
			}
		}

		return $isShown;
	}

	/**
	 * Return all errors, if any.
	 *
	 * @param   string|null  $subForm  The unique Id of the Subform to add the errors in $subFormErrors
	 *
	 * @return  array  Array of error messages or RuntimeException objects.
	 *
	 * @since   1.7.0
	 */
	public function getErrors($subFormId = null)
	{
		if (!is_null($subFormId))
		{
			return $this->subFormErrors[$subFormId];
		}

		return $this->errors;
	}

	/**
	 * Add instanceof Exception to the errors array
	 *
	 * @param   \Exception|\Exception[]  $errors   Single Exception or array of Exceptions
	 * @param   string|null              $subForm  The unique Id of the Subform to add the errors in $subFormErrors
	 *
	 * @return   void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setErrors($errors, $subFormId = null)
	{
		if (is_array($errors))
		{
			foreach ($errors as $error)
			{
				$this->setErrors($error, $subFormId);
			}

			return;
		}

		if ($errors instanceof \Exception)
		{
			if (!is_null($subFormId))
			{
				$this->subFormErrors[$subFormId][] = $errors;

				return;
			}

			$this->errors[] = $errors;
		}
	}
}
