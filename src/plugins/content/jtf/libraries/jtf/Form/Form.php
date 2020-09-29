<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

// Add form fields
\JFormHelper::addFieldPath(JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/fields');

// Add form rules
\JFormHelper::addRulePath(JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/rules');
\JLoader::registerNamespace('Joomla\\CMS\\Form\\Rule', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/rules', false, false, 'psr4');

\JLoader::import('joomla.filesystem.path');

/**
 * Form Class for the Joomla Platform.
 *
 * This class implements a robust API for constructing, populating, filtering, and validating forms.
 * It uses XML definitions to construct form fields and a variety of field and rule classes to
 * render and validate the form.
 *
 * @since  3.0.0
 */
class Form extends \Joomla\CMS\Form\Form
{
	/**
	 * Array of layoutPaths.
	 *
	 * @var    array
	 * @since  3.0.0
	 */
	public $layoutPaths = array();

	/**
	 * Array of Set enctype.
	 *
	 * @var    array
	 * @since  3.0.0
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
	 * Set the value of an attribute of the form itself
	 *
	 * @param   string  $name   Name of the attribute to get
	 * @param   string  $value  Value to set for the attribute
	 *
	 * @return  void
	 * @since   3.0.0
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
	 * @return  void
	 * @since   3.0.0
	 */
	public function resetData()
	{
		$this->data = new Registry;
	}

	/**
	 * Method to validate a JFormField object based on field data.
	 *
	 * @param   \SimpleXMLElement  $element  The XML element object representation of the form field.
	 * @param   string             $group    The optional dot-separated form group path on which to find the field.
	 * @param   mixed              $value    The optional value to use as the default for the field.
	 * @param   Registry           $input    An optional Registry object with the entire data set to validate
	 *                                       against the entire form.
	 *
	 * @return  boolean  Boolean true if field value is valid, Exception on failure.
	 *
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 * @since   3.0.0
	 */
	protected function validateField(\SimpleXMLElement $element, $group = null, $value = null, Registry $input = null)
	{
		if (!empty($showOn = (string) $element['showon']))
		{
			$isShown = $this->isFieldShown($showOn);

			// Remove required flag before the validation, if field is not shown
			if (!$isShown)
			{
				$element['required'] = 'false';

				if ($input)
				{
					$fieldExistsInRequestData = $input->exists((string) $element['name']) || $input->exists($group . '.' . (string) $element['name']);

					if ($fieldExistsInRequestData)
					{
						if ($input->exists((string) $element['name']))
						{
							$input->set((string) $element['name'], '');
						}

						if ($input->exists($group . '.' . (string) $element['name']))
						{
							$input->set($group . '.' . (string) $element['name'], '');
						}
					}
				}
			}
		}

		return parent::validateField($element, $group, $value, $input);
	}

	/**
	 * Evaluates whether the field was displayed
	 *
	 * @param   string  $showOn  The value of the showon attribute.
	 *
	 * @return  bool
	 *
	 * @since   3.0.0
	 */
	private function isFieldShown($showOn)
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
	 * @param   string[]  $values  Array of strings with showon name:value pair
	 *
	 * @return  bool
	 *
	 * @since   3.0.0
	 */
	private function fieldIsShownValidation($values)
	{
		$valuesSum      = count($values) -1;
		$conditionValid = array();
		$values         = (array) $values;

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

			$fieldvalue      = (array) $this->getValue($fieldName);
			$valueValidation = (($not === false && in_array($expectedValue, $fieldvalue))
				|| ($not === true && !in_array($expectedValue, $fieldvalue)));

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
}