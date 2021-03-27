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
class Bs2
{
	public static $name = 'Bootstrap v2 (Joomla 3 core)';

	private $classes;

	private $orientation;

	public function __construct($orientation = null)
	{
		$classes           = array();
		$inline            = $orientation == 'inline';
		$this->orientation = $orientation;

		$classes['css'] = '.jtf .form-stacked fieldset:not(.form-horizontal) .control-label{width:auto;float:none;text-align:left;}';
//		$classes['css'] .= '.jtf fieldset:not(.form-horizontal) .controls{margin-left:0;}';
		$classes['css'] .= '.jtf .form-stacked fieldset:not(.form-horizontal) .controls{margin-left:0;}';
		$classes['css'] .= '.jtf .field-calendar .input-append .btn{padding:7px 3px 0 7px;}';
		$classes['css'] .= '.jtf .combobox.input-append .btn{padding:4px 8px 3px;}';
		$classes['css'] .= '.jtf form .row{margin-left:0;}';
		$classes['css'] .= '.jtf .control-label label{font-weight:bold;}';
		$classes['css'] .= '.jtf .radio-group{padding-left:0;}';

		$classes['class']['form'][] = 'form-validate';

		switch ($orientation)
		{
			case 'inline':
				$classes['class']['gridgroup'][] = 'inline';
				break;
			default:
				$classes['class']['gridgroup'][] = 'row';
				break;
		}

		$classes['class']['default'][]        = 'input';
		$classes['class']['gridgroup'][]      = 'control-group';
		$classes['class']['descriptionclass'] = array('help-block');

		if (!$inline)
		{
			$classes['class']['gridlabel'][] = 'control-label';
			$classes['class']['gridfield'][] = 'controls';
		}

		$classes['class']['note'] = array(
			'gridfield' => array('span12'),
			'buttonclass' => array('close'),
			'buttonicon'  => array('&times;'),
		);

		$classes['class']['calendar'] = array(
			'buttonclass' => array('btn', 'btn-default'),
			'buttonicon'  => array('icon-calendar'),
		);

		$classes['class']['checkbox'] = array(
			'class' => array('checkbox'),
		);

		$classes['class']['checkboxes'] = array(
			'class' => array('checkbox'),
			'inline' => array(
				'class' => array('inline')
			),
			'options' => array(
//				'labelclass' => array('checkbox'),
			),
		);

		$classes['class']['radio'] = array(
			'class' => array('radio'),
			'inline' => array(
				'class' => array('inline')
			),
			'options' => array(
//				'labelclass' => array('radio'),
			),
		);

		$classes['class']['file'] = array(
			'uploadicon'  => array('icon-upload'),
			'buttonclass' => array('btn', 'btn-success'),
			'buttonicon'  => array('icon-copy'),
		);

		$classes['class']['submit'] = array(
			'buttonclass' => array('btn', 'btn-default'),
		);

		if ($inline)
		{
			$classes['class']['checkboxes']['class'][] = 'inline';
			$classes['class']['radio']['class'][]      = 'inline';
		}

		$this->classes = $classes;
	}

	public function getClasses()
	{
		return $this->classes['class'];
	}

	public function getCss()
	{
		if (empty($this->classes['css']))
		{
			return '';
		}

		return $this->classes['css'];
	}

	public function getOrientationGridGroupClasses($orientation = null)
	{
		$orientation = $orientation ?: $this->orientation;

		switch ($orientation)
		{
			case 'horizontal':
				return 'form-horizontal';

			case 'inline':
				return 'form-inline';
		}

		return null;
	}

	public function getOrientationGridLabelClasses($orientation = null)
	{
		$orientation = $orientation ?: $this->orientation;

		switch ($orientation)
		{
			case 'horizontal':
				return array(
					'span3',
				);

			case 'inline':
				return array();

			default:
				return array(
					'span12',
				);
		}
	}

	public function getOrientationGridFieldClasses($orientation = null)
	{
		$orientation = $orientation ?: $this->orientation;

		switch ($orientation)
		{
			case 'horizontal':
				return array(
					'span9',
				);

			case 'inline':
				return array();

			default:
			return array(
				'span12',
			);
		}
	}
}
