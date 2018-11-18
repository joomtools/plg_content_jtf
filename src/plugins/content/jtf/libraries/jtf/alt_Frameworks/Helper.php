<?php
/**
 * @package          Joomla.Plugin
 * @subpackage       Content.Jtf
 *
 * @author           Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license          GNU General Public License version 3 or later
 */

namespace Jtf\Frameworks;

defined('JPATH_PLATFORM') or die;
// Add form fields
\JFormHelper::addFieldPath(JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/fields');

// Add form rules
\JFormHelper::addRulePath(JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/rules');
\JLoader::registerNamespace('Joomla\\CMS\\Form\\Rule', JPATH_PLUGINS . '/content/jtf/libraries/joomla/form/rules', false, false, 'psr4');

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;


class FrameworkHelper
{
	private static $flexboxCssFix = false;
	private static $frameworkCssSet = false;
	private $form = null;
	private $classes = array(
		'frwk'     => array(),
		'form'     => array(),
		'fieldset' => array(),
		'field'    => array(),
	);
	private $fieldAttributes = array(
		'gridgroup',
		'gridglabel',
		'gridfield',
		'class',
		'labelClass',
		'buttonclass',
		'icon',
		'buttonicon',
		'uploadicon',
	);
	private $frwkClasses = null;
	private $hiddenLabel = array(
		'form' => null,
		'fieldset' => null,
		'field' => null,
	);
	private $hiddenLabelTypes = array('note', 'submit');

	public static function setFrameworkClasses($form)
	{
		$self       = new static;
		$self->form = $form;

//		$test = array('test teste  doppel', 'weiter', 'nocheins    test teste');
//		$test = $self->getClassArray($test);

		$self->getFrameworkClass();
		$self->getFormAttributes();
		$self->getFieldsetAttributes();

		return $self->form;
	}

	private function getFrameworkClass()
	{
		$form        =& $this->form;
		$orientation = null;

		if (!empty($form->getAttribute('orientation', '')))
		{
			$orientation = $form->getAttribute('orientation', '');
		}

		$framework = 'joomla';

		if (!empty($form->framework[0]))
		{
			$framework = $form->framework[0];
		}

		if (in_array($framework, array('uikit')))
		{
			$this->setFelxboxCssFix();
		}

		$frwkClassName     = 'Jtf\\Frameworks\\Framework' . ucfirst($framework);
		$frwkClasses       = new $frwkClassName($orientation);
		$this->frwkClasses = $frwkClasses->getClasses();

		$this->setFrameworkCss($frwkClasses->getCss());

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
		$form->frwkClasses = $frwkClasses;
	}

	private function setFelxboxCssFix()
	{
		if (self::$flexboxCssFix !== true)
		{
			$cssFix = '.fix-flexbox{max-width:100%;box-sizing: border-box;}';
			Factory::getDocument()->addStyleDeclaration($cssFix);
			self::$flexboxCssFix = true;
		}
	}

	private function setFrameworkCss($css)
	{
		if (self::$frameworkCssSet !== true)
		{
			Factory::getDocument()->addStyleDeclaration($css);
			self::$frameworkCssSet = true;
		}
	}

	private function getClassArray($classes, &$target = array())
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

		if (!empty($formClasses))
		{
			$form->setAttribute('class', implode(' ', $formClasses));
		}

		$this->classes['form']['gridgroup'] = $this->getClassArray($form->getAttribute('gridgroup'));
		$this->classes['form']['gridlabel'] = $this->getClassArray($form->getAttribute('gridlabel'));
		$this->classes['form']['gridfield'] = $this->getClassArray($form->getAttribute('gridfield'));
	}

	private function getFieldsetAttributes()
	{
		$form            =& $this->form;
		$fieldsets       = $form->getXml();
		$fieldsetClasses = array();

		if (!empty($fieldsets->fieldset))
		{
			foreach ($fieldsets->fieldset as $fieldset)
			{
				$this->hiddenLabel['fieldset'] = !empty((string) $fieldset['hiddenLabel'])
					? filter_var((string) $fieldset['hiddenLabel'], FILTER_VALIDATE_BOOLEAN)
					: null;

				$fieldsetClasses['class'] = null;
				$fieldsetClasses['labelClass'] = null;
				$fieldsetClasses['descClass'] = null;

				if (!empty($this->frwkClasses['fieldset']['class']))
				{
					$fieldsetClasses['class'] = $this->getClassArray($this->frwkClasses['fieldset']['class']);
				}

				if (!empty($this->frwkClasses['fieldset']['labelClass']))
				{
					$fieldsetClasses['labelClass'] = $this->getClassArray($this->frwkClasses['fieldset']['labelClass']);
				}

				if (!empty($this->frwkClasses['fieldset']['descClass']))
				{
					$fieldsetClasses['descClass'] = $this->getClassArray($this->frwkClasses['fieldset']['descClass']);
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

				if (!empty((string) $fieldset['labelClass']))
				{
					$fieldsetClasses['labelClass'] = ArrayHelper::arrayUnique(
						array_merge(
							$this->getClassArray($fieldsetClasses['labelClass']),
							$this->getClassArray((string) $fieldset['labelClass'])
						)
					);
				}

				if (!empty((string) $fieldset['descClass']))
				{
					$fieldsetClasses['descClass'] = ArrayHelper::arrayUnique(
						array_merge(
							$this->getClassArray($fieldsetClasses['descClass']),
							$this->getClassArray((string) $fieldset['descClass'])
						)
					);
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

				$fieldsetName = (string) $fieldset['name'];
				$fields       = $form->getFieldset($fieldsetName);

				foreach ($fields as $field)
				{
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

	private function setFieldClass($field)
	{
		$form        =& $this->form;
		$frwkClasses = $this->frwkClasses;
		$classes     = $this->classes;
		$type        = $field->getAttribute('type');
		$fieldname   = $field->getAttribute('name');

		$this->hiddenLabel['field'] = !empty($field->getAttribute('hiddenLabel'))
			? filter_var($field->getAttribute('hiddenLabel'), FILTER_VALIDATE_BOOLEAN)
			: null;

		switch (true)
		{
			case $this->hiddenLabel['field']:
				$fieldHiddenLabel = true;
				break;

			case $this->hiddenLabel['field'] === false:
				$fieldHiddenLabel = false;
				break;

			case ($this->hiddenLabel['fieldset']):
				$fieldHiddenLabel = true;
				break;

			case ($this->hiddenLabel['fieldset'] === false):
				$fieldHiddenLabel = false;
				break;

			case ($this->hiddenLabel['form']):
				$fieldHiddenLabel = true;
				break;

			default:
				$fieldHiddenLabel = false;
		}

		if ($fieldHiddenLabel || in_array($type, $this->hiddenLabelTypes))
		{
			$form->setFieldAttribute($fieldname, 'hiddenLabel', true);
		}

		if (in_array($type, array('text', 'email', 'plz', 'tel', 'list', 'combo', 'category')))
		{
			if (!empty($frwkClasses['default']))
			{
				$classes['frwk']['class'] = ArrayHelper::arrayUnique(
					array_merge(
						$this->getClassArray($this->frwkClasses['default']),
						!empty($classes['frwk']['class'])
							? $this->getClassArray($classes['frwk']['class'])
							: array()
					)
				);

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

		if (in_array($type, array('checkboxes', 'checkbox', 'radio', 'captcha', 'textarea')))
		{
			$form->setFieldAttribute($fieldname, 'icon', null);
		}

		if (in_array($type, array('checkboxes', 'checkbox', 'radio')))
		{
			$field->setOptionsClass($classes['frwk']['options']);
		}

		if (in_array($type, array('submit', 'calendar', 'color', 'file', 'note')))
		{
			$uploadicon  = null;
			$buttonicon  = null;
			$buttonclass = null;

			if (!empty($classes['frwk']['uploadicon']))
			{
				$uploadicon  = $this->getClassArray($classes['frwk']['uploadicon']);
			}

			if (!empty($classes['frwk']['buttonicon']))
			{
				$buttonicon  = $this->getClassArray($classes['frwk']['buttonicon']);
			}

			if (!empty($classes['frwk']['buttonclass']))
			{
				$buttonclass = $this->getClassArray($classes['frwk']['buttonclass']);
			}

			if (!empty($classes['field']['icon']))
			{
				if ($type == 'file')
				{
					$uploadicon = $this->getClassArray($classes['field']['icon']);
				}
				else
				{
					$buttonicon = $this->getClassArray($classes['field']['icon']);
				}
			}

			if (!empty($classes['field']['uploadicon']))
			{
				$uploadicon = $this->getClassArray($classes['field']['uploadicon']);
			}

			if (!empty($classes['field']['buttonicon']))
			{
				$buttonicon = $this->getClassArray($classes['field']['buttonicon']);
			}

			if (!empty($classes['field']['buttonclass']))
			{
				$buttonclass = ArrayHelper::arrayUnique(
					array_merge(
						!empty($buttonclass)
							? $buttonclass
							: array(),
						!empty($classes['field']['buttonclass'])
							? $this->getClassArray($classes['field']['buttonclass'])
							: array()
					)
				);
			}

			if ($type == 'file')
			{
				$form->setEnctype = true;
			}

			if (!empty($uploadicon))
			{
				$form->setFieldAttribute($fieldname, 'uploadicon', implode(' ', $uploadicon));
			}

			if (!empty($buttonicon))
			{
				$form->setFieldAttribute($fieldname, 'buttonicon', implode(' ', $buttonicon));
			}

			if (!empty($buttonclass))
			{
				$form->setFieldAttribute($fieldname, 'buttonclass', implode(' ', $buttonclass));
			}

			if (!empty($uploadicon) || !empty($buttonicon) || !empty($buttonclass))
			{
				$form->setFieldAttribute($fieldname, 'icon', null);
			}
		}

		if ($type == 'note')
		{
			unset($classes['form']['gridfield'],
				$classes['fieldset']['gridfield'],
				$classes['field']['gridfield'],
				$classes['form']['gridlabel'],
				$classes['fieldset']['gridlabel'],
				$classes['field']['gridlabel']
			);

			$form->setFieldAttribute($fieldname, 'icon', null);
			$form->setFieldAttribute($fieldname, 'buttonicon', null);
			$form->setFieldAttribute($fieldname, 'buttonclass', null);
		}

		$fieldClass = ArrayHelper::arrayUnique(
			array_merge(
				!empty($classes['frwk']['class'])
					? $this->getClassArray($classes['frwk']['class'])
					: array(),
				!empty($classes['field']['class'])
					? $this->getClassArray($classes['field']['class'])
					: array()
			)
		);

		$form->setFieldAttribute($fieldname, 'class', implode(' ', $fieldClass));

		$gridgroup = !empty($classes['frwk']['gridgroup'])
			? $classes['frwk']['gridgroup']
			: array();
		$gridlabel = !empty($classes['frwk']['gridlabel'])
			? $classes['frwk']['gridlabel']
			: array();
		$gridfield = !empty($classes['frwk']['gridfield'])
			? $classes['frwk']['gridfield']
			: array();

		if (!empty($classes['field']['gridgroup']))
		{
			$gridgroup = array_merge(
				$gridgroup,
				$classes['field']['gridgroup']
			);
		}
		elseif (!empty($classes['fieldset']['gridgroup']))
		{
			$gridgroup = array_merge(
				$gridgroup,
				$classes['fieldset']['gridgroup']
			);
		}
		elseif (!empty($classes['form']['gridgroup']))
		{
			$gridgroup = array_merge(
				$gridgroup,
				$classes['form']['gridgroup']
			);
		}

		if (!empty($classes['field']['gridlabel']))
		{
			$gridlabel = array_merge(
				$gridlabel,
				$classes['field']['gridlabel']
			);
		}
		elseif (!empty($classes['fieldset']['gridlabel']))
		{
			$gridlabel = array_merge(
				$gridlabel,
				$classes['fieldset']['gridlabel']
			);
		}
		elseif (!empty($classes['form']['gridlabel']))
		{
			$gridlabel = array_merge(
				$gridlabel,
				$classes['form']['gridlabel']
			);
		}

		if (!empty($classes['field']['gridfield']))
		{
			$gridfield = array_merge(
				$gridfield,
				$classes['field']['gridfield']
			);
		}
		elseif (!empty($classes['fieldset']['gridfield']))
		{
			$gridfield = array_merge(
				$gridfield,
				$classes['fieldset']['gridfield']
			);
		}
		elseif (!empty($classes['form']['gridfield']))
		{
			$gridfield = array_merge(
				$gridfield,
				$classes['form']['gridfield']
			);
		}

		$form->setFieldAttribute($fieldname, 'gridgroup', implode(' ', $gridgroup));
		$form->setFieldAttribute($fieldname, 'gridlabel', implode(' ', $gridlabel));
		$form->setFieldAttribute($fieldname, 'gridfield', implode(' ', $gridfield));

		return;
	}
}