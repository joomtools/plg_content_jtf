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
	 * @var   boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $isSubform = false;

	/**
	 * @var   Form
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $_form;

	/**
	 * @var   object
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $_frwk;

	/**
	 * @var   string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $_orientation;

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
	 * @param   Form     $form       Form to manipulate
	 * @param   boolean  $isSubform
	 *
	 * @return  Form
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function setFrameworkClasses(Form $form, $isSubform = false): Form
	{
		$self            = new static;
		$self->_form     = $form;
		$self->isSubform = $isSubform;

		$self->getFramework();
		$self->setFormAttributes();
		$self->setFieldsetAttributes();

		return $self->_form;
	}

	/**
	 * Get framework specific css classes from defined framework class
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function getFramework()
	{
		$form        =& $this->_form;
		$orientation = null;

		if (!empty($form->getAttribute('orientation', '')))
		{
			$orientation = $form->getAttribute('orientation', '');
		}

		$this->_orientation = $orientation;

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

		$frwkClassName = 'Jtf\\Framework\\' . ucfirst($framework);
		$frwk          = new $frwkClassName($orientation);
		$this->_frwk   = $frwk;

		$this->setFrameworkCss($frwk->getCss());

		if (!empty($gridGroup = $frwk->getClasses('gridgroup')))
		{
			$this->gridGroup['default'] = $gridGroup;
		}

		if (!empty($gridLabel =$frwk->getClasses('gridlabel')))
		{
			$this->gridLabel['default'] = $gridLabel;
		}

		if (!empty($gridField = $frwk->getClasses('gridfield')))
		{
			$this->gridField['default'] = $gridField;
		}
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
					$gridClasses = $this->_frwk->$classWrapper();
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
	private function setFormAttributes()
	{
		$form           =& $this->_form;
		$frwk           = $this->_frwk;
		$formClassArray = $frwk->getClasses('form');

		if ($this->isSubform)
		{
			$formClass = $frwk->getClasses('fieldset')['class'];
			$formClass[] = $form->getAttribute('class', '');

			if (!empty($formClass = ArrayHelper::arrayUnique($formClass)))
			{
				$form->setAttribute('class', implode(' ', $formClass));
			}
		}

		if (!empty($formClass = $form->getAttribute('class', '')))
		{
			$formClassArray = ArrayHelper::arrayUnique(
				array_merge(
					$formClassArray,
					$this->getClassArray($formClass)
				)
			);
		}

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

		if (!empty($formClassArray))
		{
			$form->setAttribute('class', implode(' ', $formClassArray));
		}

		$this->gridGroup['form'] = !empty($gridGroup = $form->getAttribute('gridgroup', ''))
			? $this->getClassArray($gridGroup)
			: array();

		$this->gridLabel['form'] = !empty($gridLabel = $form->getAttribute('gridlabel', ''))
			? $this->getClassArray($gridLabel)
			: array();

		$this->gridField['form'] = !empty($gridField = $form->getAttribute('gridfield', ''))
			? $this->getClassArray($gridField)
			: array();
	}

	/**
	 * Get special attributes from fieldset element
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function setFieldsetAttributes()
	{
		$form            =& $this->_form;
		$frwk            = $this->_frwk;
		$fieldsets       = $form->getXml();

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
					: $this->_orientation;

				$this->_orientation = $orientation;

				$frwk->setOrientation($orientation);

				$this->gridGroup['fieldset'] = !empty($gridGroup = (string) $fieldset['gridgroup'])
					? $this->getClassArray($gridGroup)
					: array();

				$this->gridLabel['fieldset'] = !empty($gridLabel = (string) $fieldset['gridlabel'])
					? $this->getClassArray($gridLabel)
					: array();

				$this->gridField['fieldset'] = !empty($gridField = (string) $fieldset['gridfield'])
					? $this->getClassArray($gridField)
					: array();

				$frwkFieldsetClasses = $frwk->getClasses('fieldset');

				if (!empty($class = (string) $fieldset['class']))
				{
					$frwkFieldsetClasses['class'] = array_merge(
						$frwkFieldsetClasses['class'],
						$this->getClassArray($class)
					);
				}

				if (!empty($labelClass = (string) $fieldset['labelclass']))
				{
					$frwkFieldsetClasses['labelclass'] = array_merge(
						$frwkFieldsetClasses['labelclass'],
						$this->getClassArray($labelClass)
					);
				}

				if (!empty($labelClass = (string) $fieldset['labelClass']))
				{
					$frwkFieldsetClasses['labelclass'] = array_merge(
						$frwkFieldsetClasses['labelclass'],
						$this->getClassArray($labelClass)
					);

					$form->setFieldAttribute($fieldsetName, 'labelClass', null);
				}

				if (!empty($descriptionClass = (string) $fieldset['descriptionclass']))
				{
					$frwkFieldsetClasses['descriptionclass'] = array_merge(
						$frwkFieldsetClasses['descriptionclass'],
						$this->getClassArray($descriptionClass)
					);
				}

				if (!empty($descriptionClass = (string) $fieldset['descClass']))
				{
					$frwkFieldsetClasses['descriptionclass'] = array_merge(
						$frwkFieldsetClasses['descriptionclass'],
						$this->getClassArray($descriptionClass)
					);

					$form->setFieldAttribute($fieldsetName, 'descClass', null);
				}

				foreach ($frwkFieldsetClasses as $classKey => $classValue)
				{
					if (!empty($classValue))
					{
						$classValue          = ArrayHelper::arrayUnique($classValue);
						$fieldset[$classKey] = implode(' ', $classValue);
					}
				}

				$fields = $form->getFieldset($fieldsetName);

				foreach ($fields as $field)
				{
					// Recursion on subform field
					if (strtolower($field->type) == 'subform')
					{
						//$subform = self::setFrameworkClasses($field->loadSubForm());

						if ($field->loadSubForm()->setEnctype)
						{
							$this->_form->setEnctype = true;
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
		$form      =& $this->_form;
		$frwk      = $this->_frwk;
		$classes   = $this->classes;
		$type      = $field->getAttribute('type');
		$fieldName = $field->getAttribute('name');

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
			if (!empty($defaultClasses = $frwk->getClasses('default')))
			{
				$classes['frwk']['class'] = array_merge_recursive(
					$defaultClasses,
					!empty($classes['frwk']['class'])
						? $classes['frwk']['class']
						: array()
				);

				$classes['frwk']['class'] = ArrayHelper::arrayUnique($classes['frwk']['class']);
			}
		}

		if (!empty($frwkFieldClasses = $frwk->getClasses($type)))
		{
			$classes['frwk'] = array_merge_recursive(
				$classes['frwk'],
				$frwkFieldClasses
			);
		}

		foreach ($this->fieldAttributes as $attribute)
		{
			if (!empty($fieldAttribute = $field->getAttribute($attribute, '')))
			{
				$classes['field'][$attribute] = $this->getClassArray($fieldAttribute);
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
			if (empty($classes['frwk']['class']))
			{
				$classes['frwk']['class'] = array();
			}

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

		if (in_array($type, $frwkClassesOptionsFields, true))
		{
			$optionClass = array_merge(
				!empty($classes['frwk']['options']['class'])
					? $classes['frwk']['options']['class']
					: array(),
				!empty($classes['field']['optionclass'])
					? $classes['field']['optionclass']
					: array()
			);

			$optionLabelClass = array_merge(
				!empty($classes['frwk']['options']['labelclass'])
					? $classes['frwk']['options']['labelclass']
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
			$uploadIcon  = array();
			$buttonIcon  = array();
			$buttonClass = array();

			if (!empty($classes['frwk']['uploadicon']))
			{
				$uploadIcon  = $classes['frwk']['uploadicon'];
			}

			if (!empty($classes['field']['uploadicon']))
			{
				$uploadIcon = $classes['field']['uploadicon'];
			}

			if (!empty($classes['frwk']['buttonicon']))
			{
				$buttonIcon  = $classes['frwk']['buttonicon'];
			}

			if (!empty($classes['field']['buttonicon']))
			{
				$buttonIcon = $classes['field']['buttonicon'];
			}

			if (!empty($classes['frwk']['buttonclass']))
			{
				$buttonClass = $classes['frwk']['buttonclass'];
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

			if (!empty($classes['field']['buttonclass']))
			{
				$buttonClass = array_merge(
					$buttonClass,
					$classes['field']['buttonclass']
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
