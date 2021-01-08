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

defined('_JEXEC') or die('Restricted access');

/**
 * Class FrameworkUikit3 set basic css for used framework
 *
 * Pattern for basic field classes
 *
 * Define basic classes for field type 'muster'
 *              $classes['class']['muster'] = array(
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
 * @since  3.0.0
 **/
class Uikit3
{
	public static $name = 'UIKit v3';

	private $classes;

	private $orientation;

	public function __construct($orientation = null)
	{
		$classes           = array();
		$inline            = $orientation == 'inline';
		$this->orientation = $orientation;

		$classes['css'] = '.field-calendar input {margin-right: 40px;padding-right: 40px;}';
		$classes['css'] .= '.uk-form-stacked .uk-form-label {width: auto !important; float: none !important;}';
		$classes['css'] .= '.uk-form-stacked .uk-form-controls {width: 100% !important; margin-left: 0 !important;}';
		$classes['css'] .= '.checkbox input[type=checkbox], .radio input[type=radio] {margin-left: 0 !important;}';
		$classes['css'] .= '.checkbox , .radio {padding-left: 0 !important;}';
//		$classes['css'] .= 'input[type=checkbox]:not(:checked), input[type=radio]:not(:checked), .uk-input, .uk-textarea {background-color: white !important;}';

		$classes['class']['form']             = array('uk-form', 'form-validate');
		$classes['class']['legend'][]         = 'uk-legend';
		$classes['class']['default'][]        = 'uk-input';
		$classes['class']['gridgroup']        = array('uk-form-row', 'uk-margin');
		$classes['class']['descriptionclass'] = array('uk-text-light');

		if (!$inline)
		{
			$classes['class']['gridlabel'][] = 'uk-form-label';
			$classes['class']['gridfield'][] = 'uk-form-controls';
			$classes['class']['gridgroup'][] = 'uk-width-1-1';
		}

		$classes['class']['fieldset'] = array(
			'class'            => array(
				'uk-fieldset',
				'uk-margin-bottom',
			),
			'labelclass'       => array('uk-legend'),
			'descriptionclass' => array('uk-fieldset-desc'),
		);

		$classes['class']['calendar'] = array(
			'class'       => array('uk-input'),
			'buttonclass' => array(
				'uk-form-icon',
				'uk-form-icon-flip',
				'uk-button-default',
				),
			'buttonicon'  => array('calendar'),
		);

		$classes['class']['checkboxes'] = array(
			'options' => array(
				'class' => array('uk-checkbox'),
			),
		);

		$classes['class']['radio'] = array(
			'options' => array(
				'class' => array('uk-radio'),
			),
		);

		$classes['class']['textarea'] = array(
			'class' => array('uk-textarea'),
		);

		$classes['class']['list'] = array(
			'class' => array('uk-select'),
		);

		$classes['class']['category'] = array(
			'class' => array('uk-select'),
		);

		$classes['class']['file'] = array(
			'uploadicon'  => array('upload;ratio:2'),
			'buttonclass' => array(
				'uk-button',
				'uk-button-success',
				),
			'buttonicon'  => array('copy'),
		);

		$classes['class']['submit'] = array(
			'gridlabel' => array(
				'jtfhp',
			),
			'gridfield' => array(
				'uk-margin-remove-left',
			),
			'buttonclass' => array(
				'uk-button',
				'uk-button-default',
				),
		);

		if ($inline)
		{
			$classes['class']['checkboxes']['class'][] = 'uk-display-inline';
			$classes['class']['radio']['class'][]      = 'uk-display-inline';
		}

		$this->classes = $classes;
	}

	public function getClasses()
	{
		return $this->classes['class'];
	}

	public function getCss()
	{
		return $this->classes['css'];
	}

	public function getOrientationClass($orientation = null)
	{
		$orientation = $orientation ?: $this->orientation;

		switch ($orientation)
		{
			case 'horizontal':
				return 'uk-form-horizontal';

			case 'stacked':
				return 'uk-form-stacked';
		}

		return null;
	}

	public function getOrientationLabelsClasses($orientation = null)
	{
		return array();
	}

	public function getOrientationFieldsClasses($orientation = null)
	{
		return array();
	}
}
