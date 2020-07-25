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
 * @since 3.0
 **/
class Bs4
{
	public static $name = 'Bootstrap v4 (Joomla 4 core)';

	private $classes;

	public function __construct($orientation = null)
	{
		$inline         = false;
		$classes        = array();
		$classes['css'] = '[data-showon][style="display: none;"] {display: none !important;}';

		$classes['class']['form'][] = 'form-validate';

		switch ($orientation)
		{
			case 'inline':
				$inline                     = true;
				$classes['class']['form'][] = 'form-inline';
				break;

			case 'horizontal':
				$classes['class']['form'][]      = 'form-horizontal';
				$classes['class']['gridgroup'][] = 'row';
				break;

			case 'stacked':
			default:
				break;
		}

		$classes['class']['default'][]        = 'form-control';
		$classes['class']['gridgroup'][]      = 'form-group';
		$classes['class']['gridlabel'][]      = 'col-form-label';
		$classes['class']['gridfield'][]      = '';
		$classes['class']['descriptionclass'] = array(
			'form-text',
			'text-muted',
		);

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
			'class'   => array(),
		);

		$classes['class']['checkboxes'] = array(
			'class'   => array('form-check'),
			'options' => array(
				'class'      => array('form-check-input'),
				'labelclass' => array('form-check-label'),
			),
		);

		$classes['class']['radio'] = array(
			'class'   => array('form-check'),
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
		return $this->classes['css'];
	}
}
