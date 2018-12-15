<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2018 JoomTools.de - All rights reserved.
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
 * @since 3.0
 **/
class Uikit3
{
	public static $name = 'UIKit v3';

	private $classes;

	public function __construct($orientation = null)
	{
		$inline         = false;
		$classes        = array();
		$classes['css'] = '.field-calendar input {margin-right: 40px;padding-right: 40px;}';
//		$classes['css'] .= 'input[type=checkbox]:not(:checked), input[type=radio]:not(:checked), .uk-input, .uk-textarea {background-color: white !important;}';

		$classes['class']['form'] = array('uk-form', 'form-validate');

		switch ($orientation)
		{
			case 'inline':
				$inline = true;
				break;

			case 'stacked':
				$classes['class']['form'][] = 'uk-form-stacked';
				break;

			case 'horizontal':
				$classes['class']['form'][] = 'uk-form-horizontal';

			default:
				break;
		}

		$classes['class']['legend'][]    = 'uk-legend';
		$classes['class']['default'][]   = 'uk-input';
		$classes['class']['gridgroup'][] = 'uk-form-row uk-margin';

		if (!$inline)
		{
			$classes['class']['gridlabel'][] = 'uk-form-label';
			$classes['class']['gridfield'][] = 'uk-form-controls';
		}

		$classes['class']['fieldset'] = array(
			'class'      => array(
				'uk-fieldset',
				'uk-margin-bottom',
				),
			'labelClass' => array('uk-legend'),
			'descClass'  => array('uk-fieldset-desc'),
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
			'class'   => array('checkboxes'),
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
}
