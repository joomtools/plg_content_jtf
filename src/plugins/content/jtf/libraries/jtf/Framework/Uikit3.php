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
 * Class FrameworkUikit3 set basic css for used framework
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
class Uikit3
{
	public static $name = 'UIKit v3 (Yootheme Pro)';

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
		$classes           = array();

		$classes['form']             = array(
			'uk-form',
			'form-validate',
			);

		$classes['legend'][]         = 'uk-legend';
		$classes['default'][]        = 'uk-input';

		$classes['gridgroup']        = array(
			'uk-form-row',
			'uk-margin',
			);

		$classes['descriptionclass'] = array('uk-text-light');

		$classes['fieldset'] = array(
			'class'            => array(
				'uk-fieldset',
				'uk-margin-bottom',
			),
			'labelclass'       => array('uk-legend'),
			'descriptionclass' => array('uk-fieldset-desc'),
		);

		$classes['calendar'] = array(
//			'class'       => array('uk-input'),
			'buttonclass' => array(
				'uk-form-icon',
				'uk-form-icon-flip',
				'uk-button-default',
				),
			'buttonicon'  => array('calendar'),
		);

		$classes['checkboxes'] = array(
			'inline'    => array(
				'class' => array('uk-display-inline'),
			),
			'options' => array(
				'class' => array('uk-checkbox'),
			),
		);

		$classes['radio'] = array(
			'inline'    => array(
				'class' => array('uk-display-inline'),
			),
			'options' => array(
				'class' => array('uk-radio'),
			),
		);

		$classes['textarea'] = array(
			'class' => array('uk-textarea'),
		);

		$classes['list'] = array(
			'class' => array('uk-select'),
		);

		$classes['category'] = array(
			'class' => array('uk-select'),
		);

		$classes['file'] = array(
			'uploadicon'  => array('upload;ratio:2'),
			'buttonclass' => array(
				'uk-button',
				'uk-button-success',
				),
			'buttonicon'  => array('copy'),
		);

		$classes['submit'] = array(
			'gridfield' => array(
				'uk-margin-remove-left',
			),
			'buttonclass' => array(
				'uk-button',
				'uk-button-default',
				),
		);

		$this->_classes = $classes;
	}

	public function getClasses($type)
	{
		$classes     = $this->_classes;
		$orientation = $this->_orientation;

		if ($orientation != 'inline')
		{
			$classes['gridlabel'][] = 'uk-form-label';
			$classes['gridfield'][] = 'uk-form-controls';
			$classes['gridgroup'][] = 'uk-width-1-1';
		}

		// $classes['fieldset']['class'] = array();

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
		$css[] = '.jtf .field-calendar input {margin-right: 40px;padding-right: 40px;}';
		$css[] = '.jtf .uk-form-stacked .uk-form-label {width: auto !important; float: none !important;}';
		$css[] = '.jtf .uk-form-stacked .uk-form-controls {width: 100% !important; margin-left: 0 !important;}';
		$css[] = '.jtf .checkbox input[type=checkbox], .radio input[type=radio] {margin-left: 0 !important;}';
		$css[] = '.jtf .checkbox, .radio {padding-left: 0 !important;}';
		$css[] = '.jtf .minicolors.minicolors-theme-bootstrap.minicolors-position-default input{padding-left: 40px;}';
		$css[] = '.jtf .minicolors-theme-bootstrap .hex{height:auto;}';

		return implode('', $css);
	}

	public function getOrientationFieldsetClasses()
	{
		switch ($this->_orientation)
		{
			case 'horizontal':
				return 'uk-form-horizontal';

			case 'stacked':
				return 'uk-form-stacked';
		}

		return null;
	}

	public function getOrientationGridGroupClasses()
	{
		return array();
	}

	public function getOrientationGridLabelClasses()
	{
		return array();
	}

	public function getOrientationGridFieldClasses()
	{
		return array();
	}
}
