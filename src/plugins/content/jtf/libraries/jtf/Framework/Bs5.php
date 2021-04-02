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

defined('_JEXEC') or die('Restricted access');

/**
 * Class FrameworkBs4 set basic css for used framework
 *
 * Pattern for basic field classes
 *
 * Define basic classes for field type 'muster'
 *              $classes['muster'] = array(
 *
 *                  Set a default class for the field, addition to the manifest attribute 'class'.
 *                  For fields such as radio or checkboxes, the class is set to the enclosing tag.
 *                  'field' => array('defaultclass'),
 *
 *                  Set this to define defaults for options for fields such as radio or checkboxes
 *                  'options' => array(
 *                      'labelclass' => array('labelclass'),
 *                      'class'      => array('optionclass'),
 *                  ),
 *
 *                  Set this to define defaults for the button of fields such as calendar
 *                  'buttons' => array(
 *                      'class' => 'uk-button uk-button-small',
 *                      'icon'  => 'uk-icon-calendar',
 *                   ),
 *              );
 *
 * @since  __DEPLOY_VERSION__
 **/
class Bs5
{
	public static $name = 'Bootstrap v5 (Joomla 4 core)';

	private $_classes;

	private $_orientation;

	public function __construct($orientation = null)
	{
		$this->init();
		$this->setOrientation($orientation);
	}

	public function setOrientation($orientation)
	{
		$this->_orientation = $orientation;
	}

	private function init()
	{
		$classes = array();

		$classes['form'][]           = 'form-validate';
		$classes['default'][]        = 'form-control';
		$classes['gridgroup'][]      = 'form-group';
		$classes['gridlabel'][]      = 'col-form-label';
		$classes['descriptionclass'] = array(
			'form-text',
			'text-muted',
		);

		$classes['note'] = array(
			'buttonclass' => array('close'),
			'buttonicon'  => array('&times;'),
		);

		$classes['calendar'] = array(
			'buttonclass' => array('btn'),
			'buttonicon'  => array('icon-calendar'),
		);

		$classes['list'] = array(
			'class'   => array('custom-select'),
		);

		$classes['checkboxes'] = array(
			'class'   => array('form-check'),
			'inline' => array(
				'class'      => array('form-check-inline'),
			),
			'options' => array(
				'class'      => array('form-check-input'),
				'labelclass' => array('form-check-label'),
			),
		);

		$classes['radio'] = array(
			'class'   => array('form-check'),
			'inline' => array(
				'class'      => array('form-check-inline'),
			),
			'options' => array(
				'class'      => array('form-check-input'),
				'labelclass' => array('form-check-label'),
			),
		);

		$classes['textarea'] = array(
			'class' => array('form-control'),
		);

		$classes['file'] = array(
			'class'       => array('form-control-file'),
			'uploadicon'  => array('icon-upload'),
			'buttonclass' => array('btn btn-success'),
			'buttonicon'  => array('icon-copy'),
		);

		$classes['submit'] = array(
			'buttonclass' => array(
				'btn',
				'btn-primary',
			),
		);

		$this->_classes = $classes;
	}

	public function getClasses($type)
	{
		$classes     = $this->_classes;
		$orientation = $this->_orientation;

		if ($orientation == 'horizontal')
		{
			$classes['note']['gridfield'][] = 'col-sm-12';
		}

		if ($orientation == 'inline')
		{
			$classes['checkboxes']['class'][] = 'form-check-inline';
			$classes['radio']['class'][]      = 'form-check-inline';
		}

		if (!empty($orientationFieldsetClasses = $this->getOrientationFieldsetClasses()))
		{
			$classes['gridgroup'][] = $orientationFieldsetClasses;
		}

		if (empty($classes[$type]))
		{
			return array();
		}

		return $classes[$type];
	}

	public function getCss()
	{
		return '';
	}

	private function getOrientationFieldsetClasses()
	{
		if ($this->_orientation == 'horizontal')
		{
			return 'row';
		}

		return null;
	}

	public function getOrientationGridGroupClasses()
	{
		return array();
	}

	public function getOrientationGridLabelClasses()
	{
		switch ($this->_orientation)
		{
			case 'horizontal':
				return array(
					'col-sm-3',
				);

			case 'stacked':
				return array(
					'col-sm-12',
				);

			default:
				return array();
		}
	}

	public function getOrientationGridFieldClasses()
	{
		switch ($this->_orientation)
		{
			case 'horizontal':
				return array(
					'col-sm-9',
				);

			case 'stacked':
				return array(
					'col-sm-12',
				);

			default:
				return array();
		}
	}
}
