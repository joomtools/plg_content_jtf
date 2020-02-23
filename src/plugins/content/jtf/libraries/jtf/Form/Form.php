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
 * @link   http://www.w3.org/TR/html4/interact/forms.html
 * @link   http://www.w3.org/TR/html5/forms.html
 * @since  11.1
 */
class Form extends \Joomla\CMS\Form\Form
{
	/**
	 * Array of layoutPaths.
	 *
	 * @var    array
	 * @since  11.1
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
	 * @since   11.1
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
	 *
	 * @since   JTF 3.0.0
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
	 *
	 * @since   JTF 3.0.0
	 */
	public function resetData()
	{
		$this->data = new Registry;
	}
}
