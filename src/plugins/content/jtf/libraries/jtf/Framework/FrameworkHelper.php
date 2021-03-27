<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
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
 * @since  __DEPLOY_VERSION__
 */
class FrameworkHelper
{
	/**
	 * @var   boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private static $flexboxCssFix = false;

	/**
	 * @var   boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private static $frameworkCssSet = false;

	/**
	 * @var   Form
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $form;

	/**
	 * @var   array[]
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
	 */
	private $fieldAttributes = array(
		'gridgroup',
		'gridlabel',
		'gridfield',
		'class',
		'labelclass',
		'hiddenlabel',
		'hiddenLabel',
		'buttonclass',
		'icon',
		'inline',
		'buttonicon',
		'uploadicon',
		'optionclass',
		'optionlabelclass',
		'descriptionclass',
	);

	/**
	 * @var   object
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $frwk;

	/**
	 * @var   array[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $frwkClasses = array();

	/**
	 * @var   string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $orientation;

	/**
	 * @var   array[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $hiddenLabel = array(
		'form' => null,
		'fieldset' => null,
		'field' => null,
	);

	/**
	 * @var   array[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $gridGroup = array(
		'default' => null,
		'form' => null,
		'fieldset' => null,
		'field' => null,
	);

	/**
	 * @var   array[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $gridLabel = array(
		'default' => null,
		'form' => null,
		'fieldset' => null,
		'field' => null,
	);

	/**
	 * @var   array[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $gridField = array(
		'default' => null,
		'form' => null,
		'fieldset' => null,
		'field' => null,
	);

	/**
	 * @var   string[]
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
	 */
	private function getFrameworkClass()
	{
		$form        =& $this->form;
		$orientation = null;

		if (!empty($form->getAttribute('orientation', '')))
		{
			$orientation = $form->getAttribute('orientation', '');
		}

		$this->orientation = $orientation;

		$framework = 'bs2';

		if (version_compare(JVERSION, '4', 'ge'))
		{
			$framework = 'bs5';
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
			$this->gridGroup['default'] = $this->getClassArray($this->frwkClasses['gridgroup']);
		}

		if (!empty($this->frwkClasses['gridlabel']))
		{
			$this->gridLabel['default'] = $this->getClassArray($this->frwkClasses['gridlabel']);
		}

		if (!empty($this->frwkClasses['gridfield']))
		{
			$this->gridField['default'] = $this->getClassArray($this->frwkClasses['gridfield']);
		}

		// Probably not more needed
		// $form->frwkClasses = $frwk;
	}

	/**
	 * Set css fix for uikit 2 flexbox
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
	 */
	private function getClassArray($classes, &$target = array()): array
	{
		if (empty($classes))
		{
			return $target;
		}

		if (is_string($classes))
		{
			$classes = trim($classes);
			$value   = explode(' ', $classes);

			if (count($value) > 1)
			{
				$target = $this->getClassArray($value, $target);
			}
			else
			{
				$target[] = $classes;
			}
		}

		if (is_array($classes))
		{
			foreach ($classes as $class)
			{
				$target = $this->getClassArray($class, $target);
			}
		}

		return $target;
	}

	/**
	 * Get the final setting for hiddenlabel|gridgroup|gridlabel|gridfield
	 *
	 * @param   string  $attribute    The attribute name to return the value
	 *
	 * @return  boolean|string  Boolean if attribute is hiddenLabel, else css classes
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function getFinalSetting(string $attribute)
	{
		$return = '';

		$searches = array(
			'group',
			'label',
			'field',
		);

		$replaces = array(
			'Group',
			'Label',
			'Field',
		);

		$attribute = str_replace($searches, $replaces, $attribute);

		if (strpos($attribute, 'grid') !== false)
		{
			if (!empty($this->$attribute['default']))
			{
				$return = implode(' ', $this->$attribute['default']);
			}

			switch (true)
			{
				case !empty($this->$attribute['field']):
					$gridClasses = implode(' ', $this->$attribute['field']);
					break;

				case !empty($this->$attribute['fieldset']):
					$gridClasses = implode(' ', $this->$attribute['fieldset']);
					break;

				case !empty($this->$attribute['form']):
					$gridClasses = implode(' ', $this->$attribute['form']);
					break;

				default:
					$classWrapper = 'getOrientation' . ucfirst($attribute) . 'Classes';
					$gridClasses = $this->getClassArray($this->frwk->$classWrapper($this->orientation));
					$gridClasses = implode(' ', $gridClasses);
					break;
			}

			if (!empty($return))
			{
				$return .= ' ';
			}

			$return .= $gridClasses;
		}
		else
		{
			switch (true)
			{
				case !empty($this->$attribute['field']) :
				case !empty($this->$attribute['fieldset']) :
				case !empty($this->$attribute['form']) :
					$return = true;
					break;

				default:
					$return = false;
					break;
			}
		}

		return $return;
	}

	/**
	 * Get special attributes from form element
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
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

		switch (true)
		{
			case !empty($form->getAttribute('hiddenlabel')) :
				$this->hiddenLabel['form'] = filter_var($form->getAttribute('hiddenlabel'), FILTER_VALIDATE_BOOLEAN);
				break;

			case !empty($form->getAttribute('hiddenLabel')) :
				$this->hiddenLabel['form'] = filter_var($form->getAttribute('hiddenLabel'), FILTER_VALIDATE_BOOLEAN);
				break;

			default:
				$this->hiddenLabel['form'] = null;
				break;
		}

		if (!empty($formClasses))
		{
			$form->setAttribute('class', implode(' ', $formClasses));
		}

		$this->gridGroup['form'] = !empty($form->getAttribute('gridgroup', ''))
			? $this->getClassArray($form->getAttribute('gridgroup'))
			: array();

		$this->gridLabel['form'] = !empty($form->getAttribute('gridlabel', ''))
			? $this->getClassArray($form->getAttribute('gridlabel'))
			: array();

		$this->gridField['form'] = !empty($form->getAttribute('gridfield', ''))
			? $this->getClassArray($form->getAttribute('gridfield'))
			: array();
	}

	/**
	 * Get special attributes from fieldset element
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function getFieldsetAttributes()
	{
		$form            =& $this->form;
		$fieldsets       = $form->getXml();
		$fieldsetClasses = array();
		$formOrientation = null;

		if (!empty($form->getAttribute('orientation', '')))
		{
			$formOrientation = $form->getAttribute('orientation', '');
		}

		if (!empty($fieldsets->fieldset))
		{
			foreach ($fieldsets->fieldset as $fieldset)
			{
				$fieldsetName = (string) $fieldset['name'];

				switch (true)
				{
					case !empty((string) $fieldset['hiddenlabel']) :
						$this->hiddenLabel['fieldset'] = filter_var((string) $fieldset['hiddenlabel'], FILTER_VALIDATE_BOOLEAN);
						break;

					case !empty((string) $fieldset['hiddenLabel']) :
						$this->hiddenLabel['fieldset'] = filter_var((string) $fieldset['hiddenLabel'], FILTER_VALIDATE_BOOLEAN);
						break;

					default:
						$this->hiddenLabel['fieldset'] = null;
						break;
				}

				$orientation = !empty((string) $fieldset['orientation'])
					? (string) $fieldset['orientation']
					: $formOrientation;

				$this->orientation = $orientation;

				$this->gridGroup['fieldset'] = !empty((string) $fieldset['gridgroup'])
					? $this->getClassArray((string) $fieldset['gridgroup'])
					: array();

				$this->gridLabel['fieldset'] = !empty((string) $fieldset['gridlabel'])
					? $this->getClassArray((string) $fieldset['gridlabel'])
					: array();


				$this->gridField['fieldset'] = !empty((string) $fieldset['gridfield'])
					? $this->getClassArray((string) $fieldset['gridfield'])
					: array();

				$fieldsetClasses['class'] = $this->getClassArray($this->frwk->getOrientationGridGroupClasses($orientation));
				$fieldsetClasses['labelclass'] = array();
				$fieldsetClasses['descriptionclass'] = array();

				if (!empty($this->frwkClasses['fieldset']['class']))
				{
					$fieldsetClasses['class'] = array_merge(
						$fieldsetClasses['class'],
						$this->frwkClasses['fieldset']['class']
					);
				}

				if (!empty($this->frwkClasses['fieldset']['labelclass']))
				{
					$fieldsetClasses['labelclass'] = $this->frwkClasses['fieldset']['labelclass'];
				}

				if (!empty($this->frwkClasses['fieldset']['descriptionclass']))
				{
					$fieldsetClasses['descriptionclass'] = $this->frwkClasses['fieldset']['descriptionclass'];
				}

				if (!empty((string) $fieldset['class']))
				{
					$fieldsetClasses['class'] = array_merge(
						$fieldsetClasses['class'],
						$this->getClassArray((string) $fieldset['class'])
					);
				}

				if (!empty((string) $fieldset['labelclass']))
				{
					$fieldsetClasses['labelclass'] = array_merge(
						$fieldsetClasses['labelclass'],
						$this->getClassArray((string) $fieldset['labelclass'])
					);
				}

				if (!empty((string) $fieldset['labelClass']))
				{
					$fieldsetClasses['labelclass'] = array_merge(
						$fieldsetClasses['labelclass'],
						$this->getClassArray((string) $fieldset['labelClass'])
					);

					$form->setFieldAttribute($fieldsetName, 'labelClass', null);
				}

				if (!empty((string) $fieldset['descriptionclass']))
				{
					$fieldsetClasses['descriptionclass'] = array_merge(
						$fieldsetClasses['descriptionclass'],
						$this->getClassArray((string) $fieldset['descriptionclass'])
					);
				}

				if (!empty((string) $fieldset['descClass']))
				{
					$fieldsetClasses['descriptionclass'] = array_merge(
						$fieldsetClasses['descriptionclass'],
						$this->getClassArray((string) $fieldset['descClass'])
					);

					$form->setFieldAttribute($fieldsetName, 'descClass', null);
				}

				foreach ($fieldsetClasses as $classKey => $classValue)
				{
					if (!empty($classValue))
					{
						$classValue          = ArrayHelper::arrayUnique($classValue);
						$fieldset[$classKey] = implode(' ', $classValue);

						$form->setFieldAttribute($fieldsetName, $classKey, $fieldset[$classKey]);
					}
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
	 * @since  __DEPLOY_VERSION__
	 */
	private function setFieldClass(FormField $field)
	{
		$form          =& $this->form;
		$frwkClasses   = $this->frwkClasses;
		$classes       = $this->classes;
		$type          = $field->getAttribute('type');
		$fieldName     = $field->getAttribute('name');

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

		if (in_array($type, $frwkClassesDefaultFields))
		{
			if (!empty($frwkClasses['default']))
			{
				$classes['frwk']['class'] = array_merge_recursive(
					$this->getClassArray($frwkClasses['default']),
					!empty($classes['frwk']['class'])
						? $classes['frwk']['class']
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

		switch (true)
		{
			case !empty($classes['field']['hiddenlabel'][0]) :
				$this->hiddenLabel['field'] = filter_var($classes['field']['hiddenlabel'][0], FILTER_VALIDATE_BOOLEAN);
				break;

			case !empty($classes['field']['hiddenLabel'][0]) :
				$this->hiddenLabel['field'] = filter_var($classes['field']['hiddenLabel'][0], FILTER_VALIDATE_BOOLEAN);
				break;

			default:
				$this->hiddenLabel['field'] = false;
				break;
		}

		$fieldHiddenLabel = $this->getFinalSetting('hiddenlabel');

		if (in_array($type, $frwkClassesOptionsFields, true)
			&& !empty($classes['field']['inline'][0])
			&& !empty($classes['frwk']['inline']['class'][0])
		)
		{
//			$fieldInline = filter_var($classes['field']['inline'][0], FILTER_VALIDATE_BOOLEAN);
			$classes['frwk']['class'] = array_merge($classes['frwk']['class'], $classes['frwk']['inline']['class']);

			unset($classes['frwk']['inline']['class']);
		}

//		echo "\nFieldHiddenLabel: $fieldName - $type";
//		var_dump($fieldHiddenLabel);

		if ($fieldHiddenLabel || in_array($type, $this->hiddenLabelTypes, true))
		{
			$form->setFieldAttribute($fieldName, 'hiddenlabel', true);
			$classes['field']['labelclass'][] = 'jtfhp';
		}

		$this->gridGroup['field'] = !empty($classes['field']['gridgroup'])
			? $classes['field']['gridgroup']
			: array();

		$this->gridLabel['field'] = !empty($classes['field']['gridlabel'])
			? $classes['field']['gridlabel']
			: array();

		$this->gridField['field'] = !empty($classes['field']['gridfield'])
			? $classes['field']['gridfield']
			: array();

		!empty($classes['frwk']['gridgroup'])
			? $this->gridGroup['field'] = array_merge(
				$this->gridGroup['field'],
				$classes['frwk']['gridgroup']
			)
			: null;

		!empty($classes['frwk']['gridlabel'])
			? $this->gridLabel['field'] = array_merge(
				$this->gridLabel['field'],
				$classes['frwk']['gridlabel']
			)
			: null;

		!empty($classes['frwk']['gridfield'])
			? $this->gridField['field'] = array_merge(
				$this->gridField['field'],
				$classes['frwk']['gridfield']
			)
			: null;

		$classes['field']['gridgroup'] = $this->getClassArray($this->getFinalSetting('gridgroup'));
		$classes['field']['gridlabel'] = $this->getClassArray($this->getFinalSetting('gridlabel'));
		$classes['field']['gridfield'] = $this->getClassArray($this->getFinalSetting('gridfield'));

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
					$uploadIcon = $classes['field']['icon'];
				}
				else
				{
					$buttonIcon = $classes['field']['icon'];
				}
			}

			if (!empty($classes['field']['uploadicon']))
			{
				$uploadIcon = $classes['field']['uploadicon'];
			}

			if (!empty($classes['field']['buttonicon']))
			{
				$buttonIcon = $classes['field']['buttonicon'];
			}

			if (!empty($classes['field']['buttonclass']))
			{
				$buttonClass = array_merge(
					!empty($buttonClass)
						? $buttonClass
						: array(),
					!empty($classes['field']['buttonclass'])
						? $classes['field']['buttonclass']
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
			$form->setFieldAttribute($fieldName, 'icon', null);
		}

		$fieldClass = array_merge(
			!empty($classes['frwk']['class'])
				? $classes['frwk']['class']
				: array(),
			!empty($classes['field']['class'])
				? $classes['field']['class']
				: array()
		);

		$fieldClass = ArrayHelper::arrayUnique($fieldClass);

		$form->setFieldAttribute($fieldName, 'class', implode(' ', $fieldClass));

		$fieldLabelClass = array_merge(
			!empty($classes['frwk']['labelclass'])
				? $classes['frwk']['labelclass']
				: array(),
			!empty($classes['field']['labelclass'])
				? $classes['field']['labelclass']
				: array()
		);

		$fieldLabelClass = ArrayHelper::arrayUnique($fieldLabelClass);

		if (!empty($fieldLabelClass))
		{
			$form->setFieldAttribute($fieldName, 'labelclass', implode(' ', $fieldLabelClass));
		}

		$gridGroup = array();
		$gridLabel = array();
		$gridField = array();

		if (!empty($classes['field']['gridgroup']))
		{
			$gridGroup = ArrayHelper::arrayUnique($classes['field']['gridgroup']);
		}

		if (!empty($classes['field']['gridlabel']))
		{
			$gridLabel = ArrayHelper::arrayUnique($classes['field']['gridlabel']);
		}

		if (!empty($classes['field']['gridfield']))
		{
			$gridField = ArrayHelper::arrayUnique($classes['field']['gridfield']);
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
