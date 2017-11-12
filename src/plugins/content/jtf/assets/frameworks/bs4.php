<?php
/**
 * @package          Joomla.Plugin
 * @subpackage       Content.Jtf
 *
 * @author           Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license          GNU General Public License version 3 or later
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class JTFFrameworkJoomla set basic css for used framework
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
class JTFFrameworkBs4
{
	public static $name = 'Bootsrap v4';

	private $classes;

	public function __construct($orientation = array())
	{
		$inline         = false;
		$classes        = array();
		$classes['css'] = '';

		$classes['class']['form'] = array('form-validate');

		switch ($orientation)
		{
			case 'inline':
				$inline                     = true;
				$classes['class']['form'][] = 'form-inline';
				break;

			case 'horizontal':
				$classes['class']['form'][]      = 'form-horizontal';
				$classes['class']['gridgroup'][] = 'row';
				$classes['class']['gridlabel'][] = 'col-form-label';

			case 'stacked':
			default:
				break;
		}

		$classes['css'] = '';

		$classes['class']['default'][]   = 'input';
		$classes['class']['gridgroup'][] = 'form-group';
		$classes['class']['gridfield'][] = 'form-control';

		$classes['class']['calendar'] = array(
			'buttons' => array(
				'class' => 'btn',
				'icon'  => 'icon-calendar',
			),
		);

		$classes['class']['checkbox'] = array(
			'field'   => array('form-check'),
			'options' => array(
				'class'      => array('form-check-input'),
				'labelclass' => array('form-check-label'),
			),
		);

		$classes['class']['checkboxes'] = array(
			'field'   => array('form-check'),
			'options' => array(
				'class'      => array('form-check-input'),
				'labelclass' => array('form-check-label'),
			),
		);

		$classes['class']['radio'] = array(
			'field'   => array('form-check'),
			'options' => array(
				'class'      => array('form-check-input'),
				'labelclass' => array('form-check-label'),
			),
		);

		$classes['class']['file'] = array(
			'field'      => array('form-control-file'),
			'uploadicon' => 'icon-upload',
			'buttons'    => array(
				'class' => 'btn btn-success',
				'icon'  => 'icon-copy',
			),
		);

		$classes['class']['submit'] = array(
			'buttons' => array(
				'class' => 'btn',
			),
		);

		if ($inline)
		{
			$classes['class']['checkboxes']['field'][] = 'form-check-inline';
			$classes['class']['radio']['field'][]      = 'form-check-inline';
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
