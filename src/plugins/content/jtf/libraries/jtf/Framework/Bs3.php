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
 * @since  __DEPLOY_VERSION__
 **/
class Bs3
{
	public static $name = 'Bootstrap v3';

	private $classes;

	private $orientation;

	public function __construct($orientation = null)
	{
		$classes           = array();
		$inline            = $orientation == 'inline';
		$this->orientation = $orientation;

		$classes['css'] = '.jtf .form-stacked fieldset:not(.form-horizontal) .control-label{text-align:left;}';
		$classes['css'] .= '.jtf fieldset.radio :not(input){padding-top:0;}';
		$classes['css'] .= '.jtf .radio label.radio:not(.radio-inline),.jtf .checkboxes label.checkbox:not(.checkbox-inline){display:block;margin-top:0;}';
		$classes['css'] .= '.jtf .checkboxes label.checkbox:not(.checkbox-inline){padding-top:0;}';
		$classes['css'] .= '.jtf .minicolors-theme-bootstrap .hex{width:105%;height:auto;}';

		$classes['class']['form'][] = 'form-validate';

		if ($orientation == 'horizontal')
		{
				$classes['class']['gridgroup'][] = 'row';
		}

		$classes['class']['default'][]        = 'form-control';
		$classes['class']['gridgroup'][]      = 'form-group';
		$classes['class']['gridlabel'][]      = 'control-label';
		$classes['class']['descriptionclass'] = array('form-text', 'text-muted');

		$classes['class']['note'] = array(
			'buttonclass' => array('close'),
			'buttonicon'  => array('&times;'),
		);

		if ($orientation == 'horizontal')
		{
			$classes['class']['note']['gridfield'][] = 'col-sm-12';
		}

		$classes['class']['calendar'] = array(
			'buttonclass' => array('btn', 'btn-default'),
			'buttonicon'  => array('glyphicon glyphicon-calendar'),
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
				'labelclass' => array('radio'),
			),
		);

		$classes['class']['textarea'] = array(
			'class' => array('form-control'),
		);

		$classes['class']['file'] = array(
			'uploadicon'  => array('glyphicon glyphicon-upload'),
			'buttonclass' => array('btn btn-success'),
			'buttonicon'  => array('glyphicon glyphicon-copy'),
		);

		$classes['class']['submit'] = array(
			'buttonclass' => array('btn', 'btn-default'),
		);

		if ($inline)
		{
			$classes['class']['checkboxes']['class'] = array('checkbox-inline');
			$classes['class']['radio']['class']      = array('radio-inline');
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

			default:
				break;
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

	public function getOrientationGridFieldClasses($orientation = null)
	{
		$orientation = $orientation ?: $this->orientation;

		switch ($orientation)
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
