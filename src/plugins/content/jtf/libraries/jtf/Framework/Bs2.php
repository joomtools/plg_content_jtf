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
 * Class FrameworkJoomla set basic css for used framework
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
class Bs2
{
	public static $name = 'Bootstrap v2 (Joomla 3 core)';

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
		$classes['default'][]        = 'input';
		$classes['gridgroup'][]      = 'control-group';
		$classes['gridlabel'][]      = 'control-label';
		$classes['gridfield'][]      = 'controls';
		$classes['descriptionclass'] = array('help-block');

		$classes['note'] = array(
			'gridfield' => array('span12'),
			'buttonclass' => array('close'),
			'buttonicon'  => array('&times;'),
		);

		$classes['calendar'] = array(
			'buttonclass' => array(
				'btn',
				'btn-default',
				),
			'buttonicon'  => array('icon-calendar'),
		);

		$classes['checkbox'] = array(
			'class' => array('checkbox'),
		);

		$classes['checkboxes'] = array(
			'class' => array('checkbox'),
			'inline' => array(
				'class' => array('inline')
			),
		);

		$classes['radio'] = array(
			'class' => array('radio'),
			'inline' => array(
				'class' => array('inline')
			),
		);

		$classes['file'] = array(
			'uploadicon'  => array('icon-upload'),
			'buttonclass' => array(
				'btn',
				'btn-success',
				),
			'buttonicon'  => array('icon-copy'),
		);

		$classes['submit'] = array(
			'buttonclass' => array(
				'btn',
				'btn-default',
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
			$classes['note']['gridfield'][] = 'span12';
		}

		if ($orientation == 'inline')
		{
			$classes['gridgroup'][] = 'inline';
			$classes['checkboxes']['class'][] = 'inline';
			$classes['radio']['class'][]      = 'inline';
		}

		/* Probably not needed
		if ($orientation != 'inline')
		{
			$classes['gridgroup'][] = 'row';
			$classes['gridlabel'][] = 'row';
			$classes['gridfield'][] = 'row';
		}
		*/

		$classes['fieldset']['class'] = array();

		if (!empty($orientationFieldsetClasses = $this->getOrientationFieldsetClasses()))
		{
			$classes['fieldset']['class'][] = $orientationFieldsetClasses;
		}

		if (empty($classes[$type]))
		{
			return array();
		}

		return $classes[$type];
	}


	public function getCss()
	{
		$css = array();
		$css[] = '.jtf .form-stacked fieldset:not(.form-horizontal) .control-label{width:auto;float:none;text-align:left;}';
//		$css[] = '.jtf fieldset:not(.form-horizontal) .controls{margin-left:0;}';
//		$css[] = '.jtf .form-stacked fieldset:not(.form-horizontal) .controls{margin-left:0;}';
		$css[] = '.jtf .form-horizontal .form-stacked .control-label{text-align:left;}';
		$css[] = '.jtf .form-horizontal .form-stacked .controls{margin-left:0;}';
		$css[] = '.jtf .field-calendar .input-append .btn{padding:7px 3px 0 7px;}';
		$css[] = '.jtf .combobox.input-append .btn{padding:4px 8px 3px;}';
		//$css[] = '.jtf form .row{margin-left:0;}';
		$css[] = '.jtf .control-label label{font-weight:bold;}';
		$css[] = '.jtf .radio-group{padding-left:0;}';
		$css[] = '.jtf .checkboxes-group input[type="checkbox"], .jtf .radio-group input[type="radio"]{margin-top:0;}';
		$css[] = '.jtf .control-group.inline{margin-left:0;min-height:50px}';
		$css[] = '.jtf .inline .marker{margin-bottom:-1%;}';


		return implode('', $css);
	}
	private function getOrientationFieldsetClasses()
	{
		switch ($this->_orientation)
		{
			case 'horizontal':
				return 'form-horizontal';

			case 'inline':
				return 'form-inline';

			default:
				return 'form-stacked';
		}
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
				return array();

			case 'inline':
				return array();

			default:
				return array(
					'span12',
				);
		}
	}

	public function getOrientationGridFieldClasses()
	{
		switch ($this->_orientation)
		{
			case 'horizontal':
				return array();

			case 'inline':
				return array();

			default:
			return array(
				'span12',
			);
		}
	}
}
