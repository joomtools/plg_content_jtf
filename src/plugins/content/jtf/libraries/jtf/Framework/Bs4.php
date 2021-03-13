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
 * Class FrameworkBs4 set basic css for used framework
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
class Bs4
{
	public static $name = 'Bootstrap v4';

	private $classes;

	private $orientation;

	public function __construct($orientation = null)
	{
		$classes           = array();
		$inline            = $orientation == 'inline';
		$this->orientation = $orientation;

		$classes['css'] = '.jtf .form-control,.jtf .inputbox{max-width:100%;}';
		$classes['css'] .= '.jtf .radio{padding-left:0;}';
		$classes['css'] .= '.jtf .minicolors-theme-bootstrap .hex{width:105%;height:auto;}';

		$classes['class']['form'][]      = 'form-validate';
		$classes['class']['default'][]   = 'form-control';
		$classes['class']['gridgroup'][] = 'form-group';

		if ($orientation == 'horizontal')
		{
			$classes['class']['gridgroup'][] = 'row';
		}

		$classes['class']['gridlabel'][]      = 'col-form-label';
		$classes['class']['gridfield'][]      = '';
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
			'buttonclass' => array('btn'),
			'buttonicon'  => array('icon-calendar'),
		);

		$classes['class']['list'] = array(
			'class'   => array('custom-select'),
		);

		$classes['class']['checkbox'] = array(
			'class'   => array('form-check'),
			'options' => array(
				'class'      => array('form-check-input'),
				'labelclass' => array('form-check-label'),
			),
		);

		$classes['class']['checkboxes'] = array(
			'class'   => array('form-check'),
			'inline' => array(
				'class'      => array('form-check-inline'),
			),
			'options' => array(
				'class'      => array('form-check-input'),
				'labelclass' => array('form-check-label'),
			),
		);

		$classes['class']['radio'] = array(
			'class'   => array('form-check'),
			'inline' => array(
				'class'      => array('form-check-inline'),
			),
			'options' => array(
				'class'      => array('form-check-input'),
				'labelclass' => array('form-check-label'),
			),
		);

		$classes['class']['textarea'] = array(
			'class' => array('form-control'),
		);

		$classes['class']['file'] = array(
			'class'       => array('form-control-file'),
			'uploadicon'  => array('icon-upload'),
			'buttonclass' => array('btn btn-success'),
			'buttonicon'  => array('icon-copy'),
		);

		$classes['class']['submit'] = array(
			'buttonclass' => array(
				'btn',
				'btn-primary',
			),
		);

		if ($inline)
		{
			$classes['class']['checkboxes']['class'][] = 'form-check-inline';
			$classes['class']['radio']['class'][]      = 'form-check-inline';
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
