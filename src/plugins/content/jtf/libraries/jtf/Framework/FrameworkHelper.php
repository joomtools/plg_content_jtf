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
use Joomla\CMS\HTML\HTMLHelper;
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
		'hiddenlabel',
		'buttonclass',
		'icon',
		'buttonicon',
		'uploadicon',
		'optionclass',
		'optionlabelclass',
		'descriptionclass',
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

		$framework = 'bs2';

		if (version_compare(JVERSION, '4', 'ge'))
		{
			$framework = 'bs4';
		}

		if (!empty($form->framework[0]))
		{
			$framework = $form->framework[0];
		}

		if (in_array($framework, array('uikit')))
		{
			$this->setFelxboxCssFix();
		}

		$frwkClassName     = 'Jtf\\Framework\\' . ucfirst($framework);
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

				$this->hiddenLabel['fieldset'] = empty($this->hiddenLabel['fieldset']) && !empty((string) $fieldset['hiddenlabel'])
					? filter_var((string) $fieldset['hiddenlabel'], FILTER_VALIDATE_BOOLEAN)
					: null;

				$fieldsetClasses['class'] = null;
				$fieldsetClasses['labelclass'] = null;
				$fieldsetClasses['descriptionclass'] = null;

				if (!empty($this->frwkClasses['fieldset']['class']))
				{
					$fieldsetClasses['class'] = $this->getClassArray($this->frwkClasses['fieldset']['class']);
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
					// Recursion on subformfield
					if (strtolower($field->type) == 'subform')
					{
						FrameworkHelper::setFrameworkClasses($field->loadSubForm());

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

	private function setFieldClass($field)
	{
		$form        =& $this->form;
		$frwkClasses = $this->frwkClasses;
		$classes     = $this->classes;
		$type        = $field->getAttribute('type');
		$fieldname   = $field->getAttribute('name');

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
			case ($this->hiddenLabel['field']):
				$fieldHiddenLabel = true;
				break;

			case ($this->hiddenLabel['field'] === false):
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
			$form->setFieldAttribute($fieldname, 'hiddenlabel', true);
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
			$form->setFieldAttribute($fieldname, 'icon', null);
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

			$form->setFieldAttribute($fieldname, 'optionclass', implode(' ', $optionClass));
			$form->setFieldAttribute($fieldname, 'optionlabelclass', implode(' ', $optionLabelClass));
		}

		if (in_array($type, $buttonWithIconFields))
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
				$buttonclass = array_merge(
					!empty($buttonclass)
						? $buttonclass
						: array(),
					!empty($classes['field']['buttonclass'])
						? $this->getClassArray($classes['field']['buttonclass'])
						: array()
				);

				$buttonclass = ArrayHelper::arrayUnique($buttonclass);
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

		if (empty($classes['field']['descriptionclass']) && !empty($frwkClasses['descriptionclass']))
		{
			$form->setFieldAttribute($fieldname, 'descriptionclass', implode(' ', $frwkClasses['descriptionclass']));
		}

		$form->setFieldAttribute($fieldname, 'gridgroup', implode(' ', $gridgroup));
		$form->setFieldAttribute($fieldname, 'gridlabel', implode(' ', $gridlabel));
		$form->setFieldAttribute($fieldname, 'gridfield', implode(' ', $gridfield));

		return;
	}
}
