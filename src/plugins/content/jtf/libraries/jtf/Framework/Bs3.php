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
 * Class FrameworkBs3 set basic css for used framework
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
class Bs3
{
	public static $name = 'Bootstrap v3';

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
		$classes     = array();

		$classes['form'][] = 'form-validate';

		$classes['default'][]        = 'form-control';
		$classes['gridgroup'][]      = 'form-group';
		$classes['gridlabel'][]      = 'control-label';
		$classes['descriptionclass'] = array(
			'form-text',
			'text-muted',
		);

		$classes['note'] = array(
			'buttonclass' => array('close'),
			'buttonicon'  => array('&times;'),
		);

		$classes['calendar'] = array(
			'buttonclass' => array(
				'btn',
				'btn-default',
			),
			'buttonicon'  => array(
				'glyphicon',
				'glyphicon-calendar',
			),
		);

		$classes['checkbox'] = array(
			'class' => array('checkbox'),
		);

		$classes['checkboxes'] = array(
			'class' => array('checkbox'),
			'inline' => array(
				'class' => array('inline'),
			),
		);

		$classes['radio'] = array(
			'class' => array('radio'),
			'inline' => array(
				'class' => array('inline'),
			),
			'options' => array(
				'labelclass' => array('radio'),
			),
		);

		$classes['textarea'] = array(
			'class' => array('form-control'),
		);

		$classes['file'] = array(
			'uploadicon'  => array(
				'glyphicon',
				'glyphicon-upload',
			),
			'buttonclass' => array('btn btn-success'),
			'buttonicon'  => array(
				'glyphicon',
				'glyphicon-copy',
			),
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
			$classes['gridgroup'][]         = 'row';
			$classes['note']['gridfield'][] = 'col-sm-12';
		}

		if ($orientation == 'inline')
		{
			$classes['checkboxes']['class'][] = 'checkbox-inline';
			$classes['radio']['class'][]      = 'radio-inline';
		}

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
		$css[] = '.jtf .form-stacked fieldset:not(.form-horizontal) .control-label{text-align:left;}';
		$css[] = '.jtf .form-horizontal .form-stacked .control-label{text-align:left;}';
		$css[] = '.jtf .form-horizontal .form-stacked .controls{margin-left:0;}';
		$css[] = '.jtf fieldset.radio :not(input){padding-top:0;}';
		$css[] = '.jtf .radio label.radio:not(.radio-inline),.jtf .checkboxes label.checkbox:not(.checkbox-inline){display:block;margin-top:0;}';
		$css[] = '.jtf .checkboxes label.checkbox:not(.checkbox-inline){padding-top:0;}';
		$css[] = '.jtf .minicolors-theme-bootstrap .hex{width:105%;height:auto;}';


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
