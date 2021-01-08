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
 * Class FrameworkUikit set basic css for used framework
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
class Uikit
{
	public static $name = 'UIKit v2';

	private $classes;

	private $orientation;

	public function __construct($orientation = null)
	{
		$classes           = array();
		$inline            = $orientation == 'inline';
		$this->orientation = $orientation;

		$classes['css'] = '.jtf .uk-form-icon:not(.uk-form-icon-flip)>select{padding-left:40px!important;}';
//		$classes['css'] .= '.jtf .uk-form-stacked .uk-form-label{width:auto!important;float:none!important;}';
		$classes['css'] .= '.jtf .uk-form-stacked .uk-form-controls{margin-left:0!important;}';
		$classes['css'] .= '.jtf .uk-checkbox,.jtf .uk-radio{margin-left:6px!important;}';
		$classes['css'] .= '.jtf .uk-radio{margin-top:4px!important;margin-right:4px!important;}';
		$classes['css'] .= '.jtf .uk-button-group {font-size:100.01%!important}';
		$classes['css'] .= '.jtf select{-moz-appearance:none;-webkit-appearance:none;appearance:none;background:url("data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2224%22%20height%3D%2216%22%20viewBox%3D%220%200%2024%2016%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%0A%20%20%20%20%3Cpolygon%20fill%3D%22%236C6D74%22%20points%3D%2212%201%209%206%2015%206%22%20%2F%3E%0A%20%20%20%20%3Cpolygon%20fill%3D%22%236C6D74%22%20points%3D%2212%2013%209%208%2015%208%22%20%2F%3E%0A%3C%2Fsvg%3E%0A") no-repeat 100% 50%!important;padding-right:20px!important;}';

		$classes['class']['form']             = array('uk-form', 'form-validate');
		$classes['class']['default'][]        = 'uk-input';
		$classes['class']['gridgroup']        = array('fix-flexbox', 'uk-form-row');
		$classes['class']['descriptionclass'] = array('uk-form-help-block');

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

		$classes['class']['note'] = array(
			'buttonclass' => array(
				'uk-alert-close',
				'uk-close',
			),
		);

		$classes['class']['calendar'] = array(
			'buttonclass' => array('uk-button'),
			'buttonicon'  => array('uk-icon-calendar'),
		);

		$classes['class']['checkbox'] = array(
			'gridfield' => array('uk-form-controls-text'),
			'class'     => array('uk-checkbox'),
		);

		$classes['class']['checkboxes'] = array(
			'gridfield'   => array('uk-form-controls-text'),
			'options' => array(
				'class' => array('uk-checkbox'),
			),
		);

		$classes['class']['radio'] = array(
			'gridfield'   => array('uk-form-controls-text'),
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

		$classes['class']['file'] = array(
			'uploadicon'  => array('uk-icon-upload'),
			'buttonclass' => array('uk-button uk-button-success'),
			'buttonicon'  => array('uk-icon-copy'),
		);

		$classes['class']['submit'] = array(
			'buttonclass' => array(
				'uk-button',
				'uk-button-default'
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
		$orientation = $orientation ?: $this->orientation;

		switch ($orientation)
		{
			case 'horizontal':
				return array(
					'uk-width-1-4',
				);

			case 'stacked':
				return array(
					'uk-width-1-1',
				);

			default:
				return array();
		}
	}

	public function getOrientationFieldsClasses($orientation = null)
	{
		$orientation = $orientation ?: $this->orientation;

		switch ($orientation)
		{
			case 'horizontal':
				return array(
					'uk-width-3-4',
				);

			case 'stacked':
				return array(
					'uk-width-1-1',
				);

			default:
				return array();
		}
	}
}
