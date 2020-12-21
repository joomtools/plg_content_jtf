<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form;

defined('JPATH_PLATFORM') or die;

use Jtf\Layout\FileLayout;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * Abstract Form Field class for the Joomla Platform.
 *
 * @since  3.0.0
 */
trait FormFieldExtension
{
	/**
	 * The control.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $control;

	/**
	 * The hidden state for the form field label.
	 *
	 * @var   boolean
	 *
	 * @since  3.0.0
	 */
	protected $hiddenlabel = false;

	/**
	 * The value of the gridgruop attribute.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $gridgroup;

	/**
	 * The value of the gridlabel attribute.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $gridlabel;

	/**
	 * The value of the gridfield attribute.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $gridfield;

	/**
	 * The value of the optionlabelclass attribute.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $optionlabelclass;

	/**
	 * The value of the optionclass attribute.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $optionclass;

	/**
	 * The value of the icon attribute.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $icon;

	/**
	 * The value of the buttonclass attribute.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $buttonclass;

	/**
	 * The value of the buttonicon attribute.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $buttonicon;

	/**
	 * The value of the description class based on framework.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $descriptionclass;

	/**
	 * Set the value for field description type.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $showfielddescriptionas = 'text';

	/**
	 * Set the value for field marker.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $fieldmarker = 'optional';

	/**
	 * Set the value for field marker place.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $fieldmarkerplace = 'field';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since  3.0.0
	 */
	public function __get($name)
	{
		switch (strtolower($name))
		{
			case 'control':
			case 'hiddenlabel':
			case 'hiddenLabel':
			case 'optionclass':
			case 'optionlabelclass':
			case 'gridgroup':
			case 'gridlabel':
			case 'gridfield':
			case 'icon':
			case 'buttonclass':
			case 'buttonicon':
			case 'descriptionclass':
			case 'showfielddescriptionas':
			case 'fieldmarker':
			case 'fieldmarkerplace':
				$name = strtolower($name);

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
	 * @since  3.0.0
	 */
	public function __set($name, $value)
	{
		switch (strtolower($name))
		{
			case 'control':
			case 'optionclass':
			case 'optionlabelclass':
			case 'gridgroup':
			case 'gridlabel':
			case 'gridfield':
			case 'icon':
			case 'buttonclass':
			case 'buttonicon':
			case 'descriptionclass':
			case 'showfielddescriptionas':
			case 'fieldmarker':
			case 'fieldmarkerplace':
				$name = strtolower($name);
				$this->$name = (string) $value;
				break;

			case 'hiddenlabel':
			case 'hiddenLabel':
				$value       = (string) $value;
				$name = strtolower($name);
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
	 * @since  3.0.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null): bool
	{
		if (parent::setup($element, $value, $group))
		{
			$attributes = array(
				'control',
				'hiddenlabel',
				'icon',
				'buttonclass',
				'buttonicon',
				'gridgroup',
				'gridlabel',
				'gridfield',
				'optionclass',
				'optionlabelclass',
				'descriptionclass',
				'showfielddescriptionas',
				'fieldmarker',
				'fieldmarkerplace',
			);

			foreach ($attributes as $attributeName)
			{
				switch ($attributeName)
				{
					case 'showfielddescriptionas':
					case 'fieldmarker':
					case 'fieldmarkerplace':
						$this->__set($attributeName, $this->form->$attributeName);
						break;

					default:
						$this->__set($attributeName, $element[$attributeName]);
						break;
				}
			}

			if ($this->readonly)
			{
				$element['required'] = 'false';
				$this->__set('required', 'false');
			}

			if ($this->disabled)
			{
				$element['required'] = 'false';
				$this->__set('required', 'false');
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
	 * @since  3.0.0
	 */
	public function renderField($options = array()): string
	{
		$type                = strtolower($this->type);
		$fieldMarker         = $this->fieldmarker;
		$issetHiddenLabel    = $this->hiddenlabel;
		$issetLabel          = !empty($label = $this->getAttribute('label'));
		$issetHint           = !empty($hint = $this->hint);
		$newHint             = array();
		$fieldMarkerDesc     = Text::_('JTF_FIELD_MARKED_DESC_' . strtoupper($fieldMarker));
		$isHintExcludedField = in_array(
			$type,
			array(
				'editor',
				'checkbox',
				'checkboxes',
				'radio',
				'list',
				'captcha',
				'file',
			)
		);

		$isDisabledDescriptionField = in_array(
			$type,
			array(
				'note',
				'spacer',
				'submit',
			)
		);

		if ($issetHiddenLabel)
		{
			$this->fieldmarkerplace       = 'hint';
			$this->showfielddescriptionas = 'text';
			$this->labelclass             = !empty((string) $this->labelclass)
				? 'jtfhp ' . (string) $this->labelclass
				: 'jtfhp';

			if (!$issetHint && $issetLabel && !$isHintExcludedField)
			{
				$newHint[] = Text::_($label);
			}
		}

		if ($issetHint && !$isHintExcludedField)
		{
			$newHint[] = $hint;
		}

		if ($isHintExcludedField && $this->fieldmarkerplace == 'hint')
		{
			$this->fieldmarkerplace = 'field';
		}

		if ($this->fieldmarkerplace == 'hint'
			&& !$isDisabledDescriptionField
			&& (($fieldMarker == 'required' && $this->required) || ($fieldMarker != 'required' && !$this->required))
		)
		{
			$newHint[]  = Text::_('JTF_FIELD_MARKED_HINT_' . strtoupper($fieldMarker));
			$this->hint = implode(' ', $newHint);
		}

		if ($isDisabledDescriptionField || $this->fieldmarkerplace != 'field')
		{
			$fieldMarkerDesc = '';
		}

		// Description preprocess
		$description = !empty($this->description) && !$isDisabledDescriptionField ? $this->description : null;
		$description = !empty($description) && $this->translateDescription ? Text::_($description) : $description;

		$options['id']                     = $this->id;
		$options['required']               = $this->required;
		$options['icon']                   = $this->getAttribute('icon');
		$options['gridGroup']              = $this->getAttribute('gridgroup');
		$options['gridLabel']              = $this->getAttribute('gridlabel');
		$options['gridField']              = $this->getAttribute('gridfield');
		$options['hiddenLabel']            = $issetHiddenLabel;
		$options['description']            = $description;
		$options['descriptionClass']       = $this->descriptionclass;
		$options['fieldMarker']            = $fieldMarker;
		$options['fieldMarkerPlace']       = $this->fieldmarkerplace;
		$options['fieldMarkerDesc']        = $fieldMarkerDesc;
		$options['showFieldDescriptionAs'] = $this->showfielddescriptionas;

		return parent::renderField($options);
	}

	/**
	 * @return  Form
	 *
	 * @since  3.0.0
	 */
	public function getForm(): Form
	{
		return $this->form;
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since  3.0.0
	 */
	protected function getLayoutData(): array
	{
		$data = parent::getLayoutData();

		return array_merge($data, array(
				'control'                => $this->control,
				'buttonClass'            => $this->buttonclass,
				'buttonIcon'             => $this->buttonicon,
				'optionLabelClass'       => $this->optionlabelclass,
				'optionClass'            => $this->optionclass,
				'framework'              => $this->form->framework[0],
				'fieldMarker'            => $this->fieldMarker,
				'fieldMarkerPlace'       => $this->fieldMarkerPlace,
				'showFieldDescriptionAs' => $this->showFieldDescriptionAs,
//				'gridGroup'        => $this->gridgroup,
//				'gridLabel'        => $this->gridlabel,
//				'gridField'        => $this->gridfield,
//				'icon'             => $this->icon,
//				'descriptionclass' => $this->descriptionclass,
			)
		);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  3.0.0
	 */
	protected function getOptions(): array
	{
		$options = parent::getOptions();

		// Define global option class attributes
		$globalOptonClass      = empty((string) $this->optionclass)
			? array()
			: explode(' ', trim((string) $this->optionclass));
		$globalOptonLabelClass = empty((string) $this->optionlabelclass)
			? array()
			: explode(' ', trim((string) $this->optionlabelclass));

		foreach ($options as &$option)
		{
			$optonClass      = empty((string) $this->optionclass)
				? array()
				: explode(' ', trim((string) $this->optionclass));
			$optonClass      = ArrayHelper::arrayUnique(array_merge($globalOptonClass, $optonClass));
			$optonLabelClass = ArrayHelper::arrayUnique($globalOptonLabelClass);

			$option->class = implode(' ', $optonClass);
			$option->labelclass = implode(' ', $optonLabelClass);
		}

		return $options;
	}

	/**
	 * Get the renderer
	 *
	 * @param   string  $layoutId  Id to load
	 *
	 * @return  FileLayout
	 *
	 * @since  3.0.0
	 */
	protected function getRenderer($layoutId = 'default'): FileLayout
	{
		$renderer = new FileLayout($layoutId);

		$renderer->setDebug($this->isDebugEnabled());

		$layoutPaths = $this->getLayoutPaths();

		if ($layoutPaths)
		{
			$renderer->setIncludePaths($layoutPaths);
		}

		$framework = !empty($this->form->framework) ? $this->form->framework : array();

		// Set Framework as Layout->Suffix
		if (!empty($framework))
		{
			$renderer->setSuffixes($framework);
		}

		$layoutFileExists = $renderer->checkLayoutExists();

		if (!$layoutFileExists)
		{
			throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->fieldname));
		}

		return $renderer;
	}

	/**
	 * Allow to override renderer include paths in child fields
	 *
	 * @return  array
	 *
	 * @since  3.0.0
	 */
	protected function getLayoutPaths(): array
	{
		return !empty($this->form->layoutPaths) ? $this->form->layoutPaths : parent::getLayoutPaths();
	}

	/**
	 * Is debug enabled for this field
	 *
	 * @return  boolean
	 *
	 * @since  3.0.0
	 */
	protected function isDebugEnabled(): bool
	{
		return ($this->getAttribute('debug', 'false') === 'true' || !empty($this->form->rendererDebug));
	}
}
