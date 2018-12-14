<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   System.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2018 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

/**
 * Abstract Form Field class for the Joomla Platform.
 *
 * @since  11.1
 */
abstract class FormField extends \Joomla\CMS\Form\FormField
{
	/**
	 * The hidden state for the form field label.
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $hiddenLabel = false;

	/**
	 * The value of the gridgruop attribute.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $gridgroup;

	/**
	 * The value of the gridlabel attribute.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $gridlabel;

	/**
	 * The value of the gridfield attribute.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $gridfield;

	/**
	 * The value of the optionlabelclass attribute.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $optionlabelclass;

	/**
	 * The value of the optionclass attribute.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $optionclass;

	/**
	 * The value of the icon attribute.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $icon;

	/**
	 * The value of the buttonclass attribute.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $buttonclass;

	/**
	 * The value of the buttonicon attribute.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $buttonicon;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   Form  $form  The form to attach to the form field object.
	 *
	 * @since   3.0.0
	 */
	public function __construct($form = null)
	{

		if (property_exists($this, 'app'))
		{
			$reflection = new \ReflectionClass($this);

			if ($reflection->getProperty('app')->isPrivate() === false && $this->app === null)
			{
				$this->app = Factory::getApplication();
			}
		}

		if (property_exists($this, 'db'))
		{
			$reflection = new \ReflectionClass($this);

			if ($reflection->getProperty('db')->isPrivate() === false && $this->db === null)
			{
				$this->db = Factory::getDbo();
			}
		}

		parent::__construct($form);
	}
	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   11.1
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'hiddenLabel':
			case 'optionclass':
			case 'optionlabelclass':
			case 'gridgroup':
			case 'gridlabel':
			case 'gridfield':
			case 'icon':
			case 'buttonclass':
			case 'buttonicon':
				return $this->$name;

			default:
				return parent::__get($name);
		}

		return;
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'optionclass':
			case 'optionlabelclass':
			case 'gridgroup':
			case 'gridlabel':
			case 'gridfield':
			case 'icon':
			case 'buttonclass':
			case 'buttonicon':
				$this->$name = (string) $value;
				break;

			case 'hiddenLabel':
				$value = (string) $value;
				$this->$name = ($value === 'true' || $value === $name || $value === '1');
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		if (parent::setup($element, $value, $group))
		{
			$attributes = array(
				'icon', 'buttonclass', 'buttonicon', 'gridgroup', 'gridlabel', 'gridfield', 'optionclass', 'optionlabelclass'
			);

			foreach ($attributes as $attributeName)
			{
				$this->__set($attributeName, $element[$attributeName]);
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to get a control group with label and input.
	 *
	 * @param   array  $options  Options to be passed into the rendering of the field
	 *
	 * @return  string  A string containing the html for the control group
	 *
	 * @since   3.2
	 */
	public function renderField($options = array())
	{
		if (!empty($options['hiddenLabel'])
			&& !empty($this->getAttribute('label'))
			&& !in_array(strtolower($this->type), array('submit', 'editor', 'checkbox', 'checkboxes', 'radio'))
		)
		{
			$star = '';

			if ($this->getAttribute('required'))
			{
				$star = ' *';
			}

			$hint = Text::_($this->getAttribute('label')) . $star;

			$this->hint = $hint;
		}

		$formControl = $this->formControl;

		if (empty($formControl))
		{
			$formControl = $this->form->getName();
		}

		$options['icon']             = $this->getAttribute('icon');
		$options['buttonclass']      = $this->getAttribute('buttonclass');
		$options['buttonicon']       = $this->getAttribute('buttonicon');
		$options['gridgroup']        = $this->getAttribute('gridgroup');
		$options['gridlabel']        = $this->getAttribute('gridlabel');
		$options['gridfield']        = $this->getAttribute('gridfield');
		$options['optionlabelclass'] = $this->getAttribute('optionlabelclass');
		$options['optionclass']      = $this->getAttribute('optionclass');

		return parent::renderField($options);
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since 3.5
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		return array_merge($data, array(
			'gridgroup'        => $this->gridgroup,
			'gridlabel'        => $this->gridlabel,
			'gridfield'        => $this->gridfield,
			'icon'             => $this->icon,
			'buttonclass'      => $this->buttonclass,
			'buttonicon'       => $this->buttonicon,
			'optionlabelclass' => $this->optionlabelclass,
			'optionclass'      => $this->optionclass,
		));
	}

	/**
	 * Allow to override renderer include paths in child fields
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	protected function getLayoutPaths()
	{
		return !empty($this->form->layoutPaths) ? $this->form->layoutPaths : array();
	}

	/**
	 * Get the renderer
	 *
	 * @param   string  $layoutId  Id to load
	 *
	 * @return   FileLayout
	 *
	 * @since   3.5
	 */
	protected function getRenderer($layoutId = 'default')
	{
		$renderer  = parent::getRenderer($layoutId);
		$framework = !empty($this->form->framework) ? $this->form->framework : array();

		// Set Framwork as Layout->Suffix
		if (!empty($framework) && $framework[0] != 'joomla')
		{
			$renderer->setSuffixes($framework);
		}

		$layoutPaths = $this->getLayoutPaths();

		if ($layoutPaths)
		{
			$renderer->addIncludePaths($layoutPaths);
		}

		return $renderer;
	}

	/**
	 * Get the renderer
	 *
	 * @param   string  $layoutId  Id to load
	 *
	 * @return   FileLayout
	 *
	 * @since   3.5
	 */
	protected function getRenderers($layoutId = 'default')
	{
		$renderer  = new FileLayout($layoutId);
		$framework = !empty($this->form->framework) ? $this->form->framework : array();

		// Set Framwork as Layout->Suffix
		if (!empty($framework) && $framework[0] != 'joomla')
		{
			$renderer->setSuffixes($framework);
		}

		$renderer->setDebug($this->isDebugEnabled());

		$layoutPaths = $this->getLayoutPaths();

		if ($layoutPaths)
		{
			$renderer->addIncludePaths($layoutPaths);
		}

		return $renderer;
	}

	/**
	 * Is debug enabled for this field
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	protected function isDebugEnabled()
	{
		return ($this->getAttribute('debug', 'false') === 'true' || !empty($this->form->rendererDebug));
	}

	/**
	 * @return   \Joomla\CMS\Form\Form
	 * @since    JTF 3.0.0
	 */
	public function getForm()
	{
		return $this->form;
	}
}
