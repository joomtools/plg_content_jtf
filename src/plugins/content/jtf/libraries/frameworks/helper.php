<?php
/**
 * @package          Joomla.Plugin
 * @subpackage       Content.Jtf
 *
 * @author           Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license          GNU General Public License version 3 or later
 */

defined('JPATH_PLATFORM') or die;

class JTFFrameworkHelper
{
	protected static $form = null;
	protected static $classes = null;
	protected static $formHiddenLabel = null;

	public static function setFrameworkClasses($form)
	{
		self::$form = $form;
		self::getFrameworkClass();
		self::setFormClasses();
		self::setFieldsetClasses();

		return self::$form;
	}

	protected static function getFrameworkClass()
	{
		$form        =& self::$form;
		$formclass   = array();
		$orientation = null;

		if (!empty($form->getAttribute('class', '')))
		{
			$formclass = explode(' ', $form->getAttribute('class', ''));
		}

		if (!empty($form->getAttribute('orientation', '')))
		{
			$orientation = $form->getAttribute('orientation', '');
		}

		$framework = 'joomla';

		if (!empty($form->framework[0]))
		{
			$framework = $form->framework[0];
		}

		$frwkClassName = 'JTFFramework' . ucfirst($framework);
		$frwkClasses   = new $frwkClassName($formclass, $orientation);
		self::$classes = $frwkClasses->getClasses();

		self::$form->frwrkClasses = $frwkClasses;
	}

	protected static function setFormClasses()
	{
		$form    =& self::$form;
		$classes =& self::$classes;

		if (!empty($classes['form']))
		{
			$form->setAttribute('class', implode(' ', $classes['form']));
		}

		if (!empty($form->getAttribute('gridgroup')))
		{
			$classes['gridgroup'][] = $form->getAttribute('gridgroup');
		}

		if (!empty($form->getAttribute('gridlabel')))
		{
			$classes['gridlabel'][] = $form->getAttribute('gridlabel');
		}

		if (!empty($form->getAttribute('gridfield')))
		{
			$classes['gridfield'][] = $form->getAttribute('gridfield');
		}

		self::$formHiddenLabel = filter_var($form->getAttribute('hiddenLabel'), FILTER_VALIDATE_BOOLEAN);
	}

	protected static function setFieldsetClasses()
	{
		$form      =& self::$form;
		$classes   =& self::$classes;
		$fieldsets = $form->getXml();

		if (!empty($fieldsets->fieldset))
		{
			foreach ($fieldsets->fieldset as $fieldset)
			{
				$fieldsetHiddenLabel = !empty((string) $fieldset['hiddenLabel'])
					? filter_var((string) $fieldset['hiddenLabel'], FILTER_VALIDATE_BOOLEAN)
					: null;

				$fieldsetClasses['field'] = !empty($classes['fieldset']['field'])
					? array_flip($classes['fieldset']['field'])
					: '';

				$fieldsetClasses['label'] = !empty($classes['fieldset']['label'])
					? array_flip($classes['fieldset']['label'])
					: '';

				$fieldsetClasses['desc'] = !empty($classes['fieldset']['desc'])
					? array_flip($classes['fieldset']['desc'])
					: '';

				if (!empty($fieldset['class']))
				{
					$fieldsetClasses['field'] = array_merge(
						$fieldsetClasses['field'],
						array_flip(explode(' ', (string) $fieldset['class']))
					);
				}

				if (!empty($fieldset['label']))
				{
					if (!empty($fieldset['labelClass']))
					{
						$fieldsetClasses['label'] = array_merge(
							$fieldsetClasses['label'],
							array_flip(explode(' ', (string) $fieldset['labelClass']))
						);
					}
				}

				if (!empty($fieldset['description']))
				{
					if (!empty($fieldset['descClass']))
					{
						$fieldsetClasses['desc'] = array_merge(
							$fieldsetClasses['desc'],
							array_flip(explode(' ', (string) $fieldset['descClass']))
						);
					}
				}

				foreach ($fieldsetClasses as $classKey => $classValue)
				{
					if (!empty($classValue))
					{
						switch ($classKey)
						{
							case 'field':
								$attribute = 'class';
								break;

							case 'label':
								$attribute = 'labelClass';
								break;

							case 'desc':
								$attribute = 'descClass';
								break;

							default:
								$attribute = null;
								break;
						}

						if (!empty($attribute))
						{
							$fieldset[$attribute] = implode(' ', array_keys($classValue));
						}
					}
				}

				if (!empty($fieldset['gridgroup']))
				{
					$classes['gridgroup'][] = (string) $fieldset['gridgroup'];
				}

				if (!empty($fieldset['gridlabel']))
				{
					$classes['gridlabel'][] = (string) $fieldset['gridlabel'];
				}

				if (!empty($fieldset['gridfield']))
				{
					$classes['gridfield'][] = (string) $fieldset['gridfield'];
				}

				$fieldsetName = (string) $fieldset['name'];
				$fields       = $form->getFieldset($fieldsetName);

				foreach ($fields as $field)
				{
					$type = $field->getAttribute('type');

					$fieldHiddenLabel = ($fieldsetHiddenLabel !== null)
						? $fieldsetHiddenLabel
						: self::$formHiddenLabel;

					if ($fieldHiddenLabel || $type == 'note')
					{
						$form->setFieldAttribute($field->fieldname, 'hiddenLabel', true);
					}

					self::setFieldClass($field);
				}
			}
		}
		else
		{
			$fields = $form->getGroup('');

			foreach ($fields as $field)
			{
				$type = $field->getAttribute('type');

				$fieldHiddenLabel = (self::$formHiddenLabel !== null) ? self::$formHiddenLabel : false;

				if ($fieldHiddenLabel || $type == 'note')
				{
					$form->setFieldAttribute($field->fieldname, 'hiddenLabel', true);
				}

				self::setFieldClass($field);
			}
		}
	}

	protected static function setFieldClass($field)
	{
		$form        =& self::$form;
		$frwkClasses = self::$classes;
		$type        = $field->getAttribute('type');
		$fieldname   = $field->getAttribute('name');
		$classes     = array(
			'frwkDefaultClass' => array(),
			'frwkFieldClass'   => array(),
			'fieldClass'       => array(),
		);

		if ($type == 'note')
		{
			$frwkClasses['gridfield'] = array();
		}

		if (in_array($type, array('file')))
		{
			$form->setEnctype = true;
		}

		if (in_array($type, array('text', 'email', 'textarea', 'plz', 'tel', 'list', 'combo', 'category')))
		{
			if (!empty($frwkClasses['default']))
			{
				$classes['frwkDefaultClass'] = array_flip($frwkClasses['default']);
			}
		}

		if (!empty($frwkClasses[$type]['field']))
		{
			$classes['frwkFieldClass'] = array_flip($frwkClasses[$type]['field']);
		}

		if (!empty($field->getAttribute('class')))
		{
			$classes['fieldClass'] = array_flip(
				explode(' ', $field->class)
			);
		}

		if (in_array($type, array('checkboxes', 'radio', 'textarea', 'captcha')))
		{
			$form->setFieldAttribute($fieldname, 'icon', null);

		}

		if (in_array($type, array('checkboxes', 'radio')))
		{
			$field->setOptionsClass($frwkClasses[$type]['options']);
		}

		if (in_array($type, array('submit', 'calendar', 'color', 'file', 'note')))
		{
			$uploadicon  = null;
			$buttonicon  = null;
			$buttonclass = null;

			if ($type == 'note')
			{
				$form->setFieldAttribute($fieldname, 'icon', null);
				$form->setFieldAttribute($fieldname, 'buttonicon', null);
				$form->setFieldAttribute($fieldname, 'buttonclass', null);
			}

			if (!empty($frwkClasses[$type]['uploadicon']))
			{
				$uploadicon = $frwkClasses[$type]['uploadicon'];
			}

			if (!empty($frwkClasses[$type]['buttons']['class']))
			{
				$buttonclass = $frwkClasses[$type]['buttons']['class'];
			}

			if (!empty($frwkClasses[$type]['buttons']['icon']))
			{
				$buttonicon = $frwkClasses[$type]['buttons']['icon'];
			}

			if (!empty($field->getAttribute('icon')))
			{
				if ($type == 'file')
				{
					$uploadicon = $field->getAttribute('icon');
				}
				else
				{
					$buttonicon = $field->getAttribute('icon');
				}
			}

			if (!empty($field->getAttribute('uploadicon')))
			{
				$uploadicon = $field->getAttribute('uploadicon');
			}

			if (!empty($field->getAttribute('buttonicon')))
			{
				$buttonicon = $field->getAttribute('buttonicon');
			}

			if (($type == 'submit' || $type == 'file') && !empty($classes['fieldClass']))
			{
				$buttonclass           = implode(' ', array_keys($classes['fieldClass']));
				$classes['fieldClass'] = array();
			}

			if (!empty($field->getAttribute('buttonclass')))
			{
				$buttonclass = $field->getAttribute('buttonclass');
			}

			if (!empty($uploadicon))
			{
				$form->setFieldAttribute($fieldname, 'uploadicon', $uploadicon);
			}

			if (!empty($buttonicon))
			{
				$form->setFieldAttribute($fieldname, 'buttonicon', $buttonicon);
			}

			if (!empty($buttonclass))
			{
				$form->setFieldAttribute($fieldname, 'buttonclass', $buttonclass);
			}

			if (!empty($uploadicon) || !empty($buttonicon) || !empty($buttonclass))
			{
				$form->setFieldAttribute($fieldname, 'icon', null);
			}

		}

		$class      = array_merge($classes['frwkDefaultClass'], $classes['frwkFieldClass'], $classes['fieldClass']);
		$fieldClass = array_keys($class);

		$form->setFieldAttribute($fieldname, 'class', implode(' ', $fieldClass));

		$grid['group']['frwk']  = !empty($frwkClasses['gridgroup']) ? array_flip($frwkClasses['gridgroup']) : array();
		$grid['label']['frwk']  = !empty($frwkClasses['gridlabel']) ? array_flip($frwkClasses['gridlabel']) : array();
		$grid['field']['frwk']  = !empty($frwkClasses['gridfield']) ? array_flip($frwkClasses['gridfield']) : array();
		$grid['group']['field'] = array();
		$grid['label']['field'] = array();
		$grid['field']['field'] = array();

		if (!empty($field->getAttribute('gridgroup')))
		{
			$grid['group']['field'] = array_flip(
				explode(' ', $field->getAttribute('gridgroup'))
			);
		}

		if (!empty($field->getAttribute('gridlabel')))
		{
			$grid['label']['field'] = array_flip(
				explode(' ', $field->getAttribute('gridlabel'))
			);
		}

		if (!empty($field->getAttribute('gridfield')))
		{
			$grid['field']['field'] = array_flip(
				explode(' ', $field->getAttribute('gridfield'))
			);
		}

		$gridgroup = array_keys(array_merge($grid['group']['frwk'], $grid['group']['field']));
		$gridlabel = array_keys(array_merge($grid['label']['frwk'], $grid['label']['field']));
		$gridfield = array_keys(array_merge($grid['field']['frwk'], $grid['field']['field']));

		$form->setFieldAttribute($fieldname, 'gridgroup', implode(' ', $gridgroup));
		$form->setFieldAttribute($fieldname, 'gridlabel', implode(' ', $gridlabel));
		$form->setFieldAttribute($fieldname, 'gridfield', implode(' ', $gridfield));

		return;
	}
}