<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Framework;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\Utilities\ArrayHelper;
use Jtf\Form\Form;

/**
 * Helper for css framework
 *
 * @since  3.0.0
 */
class FrameworkHelper
{
	/**
	 * @var   boolean
	 *
	 * @since  3.0.0
	 */
	private static $flexboxCssFix = false;

	/**
	 * @var   boolean
	 *
	 * @since  3.0.0
	 */
	private static $frameworkCssSet = false;

	/**
	 * @var   Form
	 *
	 * @since  3.0.0
	 */
	private $form;

	/**
	 * @var   array[]
	 *
	 * @since  3.0.0
	 */
	private $classes = array(
		'frwk'     => array(),
		'form'     => array(),
		'fieldset' => array(),
		'field'    => array(),
	);

	/**
	 * @var   string[]
	 *
	 * @since  3.0.0
	 */
	private $fieldAttributes = array(
		'gridgroup',
		'gridglabel',
		'gridfield',
		'class',
		'labelClass',
		'hiddenlabel',
		'buttonclass',
		'icon',
		'buttonicon',
		'uploadicon',
		'optionclass',
		'optionlabelclass',
		'descriptionclass',
	);

	/**
	 * @var   object
	 *
	 * @since  3.0.0
	 */
	private $frwk;

	/**
	 * @var   array[]
	 *
	 * @since  3.0.0
	 */
	private $frwkClasses = array();

	/**
	 * @var   array[]
	 *
	 * @since  3.0.0
	 */
	private $hiddenLabel = array(
		'form' => null,
		'fieldset' => null,
		'field' => null,
	);

	/**
	 * @var   string[]
	 *
	 * @since  3.0.0
	 */
	private $hiddenLabelTypes = array(
		'note',
		'submit'
	);

	/**
	 * Set framework specific css classes
	 *
	 * @param   Form  $form  Form to manipulate
	 *
	 * @return  Form
	 *
	 * @since  3.0.0
	 */
	public static function setFrameworkClasses(Form $form): Form
	{
		$self       = new static;
		$self->form = $form;

		$self->getFrameworkClass();
		$self->getFormAttributes();
		$self->getFieldsetAttributes();

		return $self->form;
	}

	/**
	 * Get framework specific css classes from defined framework class
	 *
	 * @return  void
	 *
	 * @since  3.0.0
	 */
	private function getFrameworkClass()
	{
		$form        =& $this->form;
		$orientation = null;

		if (!empty($form->getAttribute('orientation', '')))
		{
			$orientation = $form->getAttribute('orientation', '');
		}

		$framework = 'bs2';

		if (version_compare(JVERSION, '4', 'ge'))
		{
			$framework = 'bs4';
		}

		if (!empty($form->framework[0]) && $form->framework[0] != 'joomla')
		{
			$framework = $form->framework[0];
		}

		if (in_array($framework, array('uikit')))
		{
			$this->setFlexboxCssFix();
		}

		$frwkClassName     = 'Jtf\\Framework\\' . ucfirst($framework);
		$frwk              = new $frwkClassName($orientation);
		$this->frwk        = $frwk;
		$this->frwkClasses = $frwk->getClasses();

		$this->setFrameworkCss($frwk->getCss());

		if (!empty($this->frwkClasses['gridgroup']))
		{
			$this->classes['frwk']['gridgroup'] = $this->getClassArray($this->frwkClasses['gridgroup']);
		}

		if (!empty($this->frwkClasses['gridlabel']))
		{
			$this->classes['frwk']['gridlabel'] = $this->getClassArray($this->frwkClasses['gridlabel']);
		}

		if (!empty($this->frwkClasses['gridfield']))
		{
			$this->classes['frwk']['gridfield'] = $this->getClassArray($this->frwkClasses['gridfield']);
		}

		// Probably not more needed
		// $form->frwkClasses = $frwk;
	}

	/**
	 * Set css fix for uikit 2 flexbox
	 *
	 * @return  void
	 *
	 * @since  3.0.0
	 */
	private function setFlexboxCssFix()
	{
		if (self::$flexboxCssFix !== true)
		{
			$cssFix = '.fix-flexbox{max-width:100%;box-sizing:border-box;}';
			Factory::getDocument()->addStyleDeclaration($cssFix);
			self::$flexboxCssFix = true;
		}
	}

	/**
	 * Set framework specific css
	 *
	 * @param   string  $css  Css to add as style declaration in head
	 *
	 * @return  void
	 *
	 * @since  3.0.0
	 */
	private function setFrameworkCss(string $css)
	{
		if (self::$frameworkCssSet !== true)
		{
			Factory::getDocument()->addStyleDeclaration($css);
			self::$frameworkCssSet = true;
		}
	}

	/**
	 * Format form field css classes to an array
	 *
	 * @param   mixed  $classes  String or array with css classes
	 * @param   array  $target   Array to merge css classes
	 *
	 * @return  array
	 *
	 * @since  3.0.0
	 */
	private function getClassArray($classes, &$target = array()): array
	{
		if (empty($classes))
		{
			return $target;
		}

		if (is_string($classes))
		{
			$value = explode(' ', $classes);

			if (count($value) > 1)
			{
				$target = $this->getClassArray($value, $target);
			}
			else
			{
				$target[] = trim($classes);
			}
		}

		if (is_array($classes))
		{
			foreach ($classes as $class)
			{
				$class  = trim($class);
				$target = $this->getClassArray($class, $target);
			}
		}

		return $target;
	}

	/**
	 * Get special attributes from form element
	 *
	 * @return  void
	 *
	 * @since  3.0.0
	 */
	private function getFormAttributes()
	{
		$form        =& $this->form;
		$formClasses = $this->getClassArray($this->frwkClasses['form']);
		$formClasses = ArrayHelper::arrayUnique(
			array_merge(
				$formClasses,
				$this->getClassArray($form->getAttribute('class'))
			)
		);

		$this->hiddenLabel['form'] = !empty($form->getAttribute('hiddenLabel'))
			? filter_var($form->getAttribute('hiddenLabel'), FILTER_VALIDATE_BOOLEAN)
			: null;

		$this->hiddenLabel['form'] = empty($this->hiddenLabel['form']) && !empty($form->getAttribute('hiddenlabel'))
			? filter_var($form->getAttribute('hiddenlabel'), FILTER_VALIDATE_BOOLEAN)
			: null;

		if (!empty($formClasses))
		{
			$form->setAttribute('class', implode(' ', $formClasses));
		}

		$this->classes['form']['gridgroup'] = $this->getClassArray($form->getAttribute('gridgroup'));
		$this->classes['form']['gridlabel'] = $this->getClassArray($form->getAttribute('gridlabel'));
		$this->classes['form']['gridfield'] = $this->getClassArray($form->getAttribute('gridfield'));
	}

	/**
	 * Get special attributes from fieldset element
	 *
	 * @return  void
	 *
	 * @since  3.0.0
	 */
	private function getFieldsetAttributes()
	{
		$form            =& $this->form;
		$fieldsets       = $form->getXml();
		$fieldsetClasses = array();

		if (!empty($fieldsets->fieldset))
		{
			foreach ($fieldsets->fieldset as $fieldset)
			{
				$fieldsetName = (string) $fieldset['name'];

				$this->hiddenLabel['fieldset'] = !empty((string) $fieldset['hiddenLabel'])
					? filter_var((string) $fieldset['hiddenLabel'], FILTER_VALIDATE_BOOLEAN)
					: null;

				$this->hiddenLabel['fieldset'] = empty($this->hiddenLabel['fieldset']) && !empty((string) $fieldset['hiddenlabel'])
					? filter_var((string) $fieldset['hiddenlabel'], FILTER_VALIDATE_BOOLEAN)
					: null;

				$fieldsetClasses['class'] = array();
				$fieldsetClasses['labelclass'] = array();
				$fieldsetClasses['descriptionclass'] = array();

				$orientation = (string) $fieldset['orientation'];
				$orientation = $this->frwk->getOrientationClass($orientation);

				if (!empty($this->frwkClasses['fieldset']['class']))
				{
					$fieldsetClasses['class'] = $this->getClassArray($this->frwkClasses['fieldset']['class']);
				}

				if (!empty($orientation))
				{
					$fieldsetClasses['class'][] = $orientation;
				}

				if (!empty($this->frwkClasses['fieldset']['labelclass']))
				{
					$fieldsetClasses['labelclass'] = $this->getClassArray($this->frwkClasses['fieldset']['labelclass']);
				}

				if (!empty($this->frwkClasses['fieldset']['descriptionclass']))
				{
					$fieldsetClasses['descriptionclass'] = $this->getClassArray($this->frwkClasses['fieldset']['descriptionclass']);
				}

				if (!empty((string) $fieldset['class']))
				{
					$fieldsetClasses['class'] = ArrayHelper::arrayUnique(
						array_merge(
							$this->getClassArray($fieldsetClasses['class']),
							$this->getClassArray((string) $fieldset['class'])
						)
					);
				}

				if (!empty((string) $fieldset['labelclass']))
				{
					$fieldsetClasses['labelclass'] = ArrayHelper::arrayUnique(
						array_merge(
							$this->getClassArray($fieldsetClasses['labelclass']),
							$this->getClassArray((string) $fieldset['labelclass'])
						)
					);
				}

				if (!empty((string) $fieldset['labelClass']))
				{
					$fieldsetClasses['labelclass'] = ArrayHelper::arrayUnique(
						array_merge(
							$this->getClassArray($fieldsetClasses['labelclass']),
							$this->getClassArray((string) $fieldset['labelClass'])
						)
					);

					$form->setFieldAttribute($fieldsetName, 'labelclass', $fieldsetClasses['labelclass']);
				}

				if (!empty((string) $fieldset['descriptionclass']))
				{
					$fieldsetClasses['descriptionclass'] = ArrayHelper::arrayUnique(
						array_merge(
							$this->getClassArray($fieldsetClasses['descriptionclass']),
							$this->getClassArray((string) $fieldset['descriptionclass'])
						)
					);
				}

				if (!empty((string) $fieldset['descClass']))
				{
					$fieldsetClasses['descriptionclass'] = ArrayHelper::arrayUnique(
						array_merge(
							$this->getClassArray($fieldsetClasses['descriptionclass']),
							$this->getClassArray((string) $fieldset['descClass'])
						)
					);

					$form->setFieldAttribute($fieldsetName, 'descriptionclass', $fieldsetClasses['descriptionclass']);
				}

				foreach ($fieldsetClasses as $classKey => $classValue)
				{
					if (!empty($classValue))
					{
						$fieldset[$classKey] = implode(' ', $classValue);
					}
				}

				$this->classes['fieldset']['gridgroup'] = null;
				$this->classes['fieldset']['gridlabel'] = null;
				$this->classes['fieldset']['gridfield'] = null;

				if (!empty((string) $fieldset['gridgroup']))
				{
					$this->classes['fieldset']['gridgroup'] = $this->getClassArray((string) $fieldset['gridgroup']);
				}

				if (!empty((string) $fieldset['gridlabel']))
				{
					$this->classes['fieldset']['gridlabel'] = $this->getClassArray((string) $fieldset['gridlabel']);
				}

				if (!empty((string) $fieldset['gridfield']))
				{
					$this->classes['fieldset']['gridfield'] = $this->getClassArray((string) $fieldset['gridfield']);
				}

				$fields = $form->getFieldset($fieldsetName);

				foreach ($fields as $field)
				{
					// Recursion on subform field
					if (strtolower($field->type) == 'subform')
					{
						self::setFrameworkClasses($field->loadSubForm());

						if ($field->loadSubForm()->setEnctype)
						{
							$this->form->setEnctype = true;
						}
					}

					$this->setFieldClass($field);
				}
			}
		}
		else
		{
			$fields = $form->getGroup('');

			foreach ($fields as $field)
			{
				$this->setFieldClass($field);
			}
		}
	}

	/**
	 * Set framework specific css classes to form field
	 *
	 * @param   FormField  $field  Form field to manipulate
	 *
	 * @return  void
	 *
	 * @since  3.0.0
	 */
	private function setFieldClass(FormField $field)
	{
		$form        =& $this->form;
		$frwkClasses = $this->frwkClasses;
		$classes     = $this->classes;
		$type        = $field->getAttribute('type');
		$fieldName   = $field->getAttribute('name');

		$frwkClassesOptionsFields = array(
			'checkboxes', 'radio',
		);

		$frwkClassesDefaultFields = array(
			'text', 'email', 'plz', 'tel', 'list', 'combo', 'category', 'calendar',
		);

		$buttonWithIconFields = array(
			'submit', 'calendar', 'file', 'note',
		);

		$noIconFields = array(
			'checkboxes', 'checkbox', 'radio', 'captcha', 'textarea',
		);

		$this->hiddenLabel['field'] = !empty($field->getAttribute('hiddenLabel'))
			? filter_var($field->getAttribute('hiddenLabel'), FILTER_VALIDATE_BOOLEAN)
			: null;

		$this->hiddenLabel['field'] = empty($this->hiddenLabel['field']) && !empty($field->getAttribute('hiddenlabel'))
			? filter_var($field->getAttribute('hiddenlabel'), FILTER_VALIDATE_BOOLEAN)
			: null;

		switch (true)
		{
			case !is_null($this->hiddenLabel['field']):
				$fieldHiddenLabel = $this->hiddenLabel['field'];
				break;

			case !is_null($this->hiddenLabel['fieldset']):
				$fieldHiddenLabel = $this->hiddenLabel['fieldset'];
				break;

			case !is_null($this->hiddenLabel['form']):
				$fieldHiddenLabel = $this->hiddenLabel['form'];
				break;

			default:
				$fieldHiddenLabel = false;
		}

		if ($fieldHiddenLabel || in_array($type, $this->hiddenLabelTypes))
		{
			$form->setFieldAttribute($fieldName, 'hiddenlabel', true);
		}

		if (in_array($type, $frwkClassesDefaultFields))
		{
			if (!empty($frwkClasses['default']))
			{
				$classes['frwk']['class'] = array_merge(
					$this->getClassArray($this->frwkClasses['default']),
					!empty($classes['frwk']['class'])
						? $this->getClassArray($classes['frwk']['class'])
						: array()
				);

				$classes['frwk']['class'] = ArrayHelper::arrayUnique($classes['frwk']['class']);

				unset($frwkClasses['default']);
			}
		}

		if (!empty($frwkClasses[$type]))
		{
			$classes['frwk'] = array_merge_recursive(
				$classes['frwk'],
				$frwkClasses[$type]
			);
		}

		foreach ($this->fieldAttributes as $attribute)
		{
			if (!empty($field->getAttribute($attribute, '')))
			{
				$classes['field'][$attribute] = $this->getClassArray($field->getAttribute($attribute));
			}
		}

		if (in_array($type, $noIconFields))
		{
			$form->setFieldAttribute($fieldName, 'icon', null);
		}

		if (in_array($type, $frwkClassesOptionsFields))
		{
			$optionClass = array_merge(
				!empty($classes['frwk']['options']['class'])
					? $this->getClassArray($classes['frwk']['options']['class'])
					: array(),
				!empty($classes['field']['optionclass'])
					? $classes['field']['optionclass']
					: array()
			);

			$optionLabelClass = array_merge(
				!empty($classes['frwk']['options']['labelclass'])
					? $this->getClassArray($classes['frwk']['options']['labelclass'])
					: array(),
				!empty($classes['field']['optionlabelclass'])
					? $classes['field']['optionlabelclass']
					: array()
			);

			$optionClass      = ArrayHelper::arrayUnique($optionClass);
			$optionLabelClass = ArrayHelper::arrayUnique($optionLabelClass);

			$form->setFieldAttribute($fieldName, 'optionclass', implode(' ', $optionClass));
			$form->setFieldAttribute($fieldName, 'optionlabelclass', implode(' ', $optionLabelClass));
		}

		if (in_array($type, $buttonWithIconFields))
		{
			$uploadIcon  = null;
			$buttonIcon  = null;
			$buttonClass = null;

			if (!empty($classes['frwk']['uploadicon']))
			{
				$uploadIcon  = $this->getClassArray($classes['frwk']['uploadicon']);
			}

			if (!empty($classes['frwk']['buttonicon']))
			{
				$buttonIcon  = $this->getClassArray($classes['frwk']['buttonicon']);
			}

			if (!empty($classes['frwk']['buttonclass']))
			{
				$buttonClass = $this->getClassArray($classes['frwk']['buttonclass']);
			}

			if (!empty($classes['field']['icon']))
			{
				if ($type == 'file')
				{
					$uploadIcon = $this->getClassArray($classes['field']['icon']);
				}
				else
				{
					$buttonIcon = $this->getClassArray($classes['field']['icon']);
				}
			}

			if (!empty($classes['field']['uploadicon']))
			{
				$uploadIcon = $this->getClassArray($classes['field']['uploadicon']);
			}

			if (!empty($classes['field']['buttonicon']))
			{
				$buttonIcon = $this->getClassArray($classes['field']['buttonicon']);
			}

			if (!empty($classes['field']['buttonclass']))
			{
				$buttonClass = array_merge(
					!empty($buttonClass)
						? $buttonClass
						: array(),
					!empty($classes['field']['buttonclass'])
						? $this->getClassArray($classes['field']['buttonclass'])
						: array()
				);

				$buttonClass = ArrayHelper::arrayUnique($buttonClass);
			}

			if ($type == 'file')
			{
				$form->setEnctype = true;
			}

			if (!empty($uploadIcon))
			{
				$form->setFieldAttribute($fieldName, 'uploadicon', implode(' ', $uploadIcon));
			}

			if (!empty($buttonIcon))
			{
				$form->setFieldAttribute($fieldName, 'buttonicon', implode(' ', $buttonIcon));
			}

			if (!empty($buttonClass))
			{
				$form->setFieldAttribute($fieldName, 'buttonclass', implode(' ', $buttonClass));
			}

			if (!empty($uploadIcon) || !empty($buttonIcon) || !empty($buttonClass))
			{
				$form->setFieldAttribute($fieldName, 'icon', null);
			}
		}

		if ($type == 'note')
		{
			unset(
				$classes['form']['gridfield'],
				$classes['fieldset']['gridfield'],
				$classes['field']['gridfield'],
				$classes['form']['gridlabel'],
				$classes['fieldset']['gridlabel'],
				$classes['field']['gridlabel']
			);

			$form->setFieldAttribute($fieldName, 'icon', null);
		}

		$fieldClass = array_merge(
			!empty($classes['frwk']['class'])
				? $this->getClassArray($classes['frwk']['class'])
				: array(),
			!empty($classes['field']['class'])
				? $this->getClassArray($classes['field']['class'])
				: array()
		);

		$fieldClass = ArrayHelper::arrayUnique($fieldClass);

		$form->setFieldAttribute($fieldName, 'class', implode(' ', $fieldClass));

		$gridGroup = !empty($classes['frwk']['gridgroup'])
			? $classes['frwk']['gridgroup']
			: array();
		$gridLabel = !empty($classes['frwk']['gridlabel'])
			? $classes['frwk']['gridlabel']
			: array();
		$gridField = !empty($classes['frwk']['gridfield'])
			? $classes['frwk']['gridfield']
			: array();

		if (!empty($classes['field']['gridgroup']))
		{
			$gridGroup = array_merge(
				$gridGroup,
				$classes['field']['gridgroup']
			);
		}
		elseif (!empty($classes['fieldset']['gridgroup']))
		{
			$gridGroup = array_merge(
				$gridGroup,
				$classes['fieldset']['gridgroup']
			);
		}
		elseif (!empty($classes['form']['gridgroup']))
		{
			$gridGroup = array_merge(
				$gridGroup,
				$classes['form']['gridgroup']
			);
		}

		if (!empty($classes['field']['gridlabel']))
		{
			$gridLabel = array_merge(
				$gridLabel,
				$classes['field']['gridlabel']
			);
		}
		elseif (!empty($classes['fieldset']['gridlabel']))
		{
			$gridLabel = array_merge(
				$gridLabel,
				$classes['fieldset']['gridlabel']
			);
		}
		elseif (!empty($classes['form']['gridlabel']))
		{
			$gridLabel = array_merge(
				$gridLabel,
				$classes['form']['gridlabel']
			);
		}

		if (!empty($classes['field']['gridfield']))
		{
			$gridField = array_merge(
				$gridField,
				$classes['field']['gridfield']
			);
		}
		elseif (!empty($classes['fieldset']['gridfield']))
		{
			$gridField = array_merge(
				$gridField,
				$classes['fieldset']['gridfield']
			);
		}
		elseif (!empty($classes['form']['gridfield']))
		{
			$gridField = array_merge(
				$gridField,
				$classes['form']['gridfield']
			);
		}

		if (empty($classes['field']['descriptionclass']) && !empty($frwkClasses['descriptionclass']))
		{
			$form->setFieldAttribute($fieldName, 'descriptionclass', implode(' ', $frwkClasses['descriptionclass']));
		}

		$form->setFieldAttribute($fieldName, 'gridgroup', implode(' ', $gridGroup));
		$form->setFieldAttribute($fieldName, 'gridlabel', implode(' ', $gridLabel));
		$form->setFieldAttribute($fieldName, 'gridfield', implode(' ', $gridField));
	}
}
