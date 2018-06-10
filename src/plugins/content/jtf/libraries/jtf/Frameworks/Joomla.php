<?php
/**
 * @package          Joomla.Plugin
 * @subpackage       Content.Jtf
 *
 * @author           Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license          GNU General Public License version 3 or later
 */

namespace Jtf\Frameworks;

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
 * @since 3.0
 **/
class FrameworkJoomla
{
	public static $name = 'Joomla Core (Bootstrap v2)';

	private $classes;

	public function __construct($formclass = array(), $orientation = null)
	{
		$inline         = false;
		$classes        = array();
		$classes['css'] = '';

		$classes['class']['form'] = $formclass;
		array_unshift($classes['class']['form'], 'form-validate');

		switch ($orientation)
		{
			case 'inline':
				$inline                     = true;
				$classes['class']['form'][] = 'form-inline';
				$classes['class']['gridgroup'][] = 'inline';
				$classes['class']['gridlabel'][] = 'inline';
				$classes['class']['gridfield'][] = 'inline';
				break;

			case 'horizontal':
				$classes['class']['form'][] = 'form-horizontal';
				$classes['class']['gridgroup'][] = 'row';
//				$classes['class']['gridlabel'][] = 'span3';
//				$classes['class']['gridfield'][] = 'span9';

			case 'stacked':
			default:
				break;
		}

		$classes['class']['form']        = array_unique($classes['class']['form']);
		$classes['class']['default'][]   = 'input';
		$classes['class']['gridgroup'][] = 'control-group';

		if (!$inline)
		{
			$classes['class']['gridlabel'][] = 'control-label';
			$classes['class']['gridfield'][] = 'controls';
		}

		$classes['class']['note'] = array(
			'buttons' => array(
				'class' => 'close',
				'icon'  => '&times;',
			),
		);

		$classes['class']['calendar'] = array(
			'buttons' => array(
				'class' => 'btn btn-secondary',
				'icon'  => 'icon-calendar',
			),
		);

		$classes['class']['checkbox'] = array(
			'field' => array('checkbox'),
		);

		$classes['class']['checkboxes'] = array(
			'field' => array('checkboxes'),
			'options' => array(
				'labelclass' => array('checkbox'),
			),
		);

		$classes['class']['radio'] = array(
			'options' => array(
				'labelclass' => array('radio'),
			),
		);

		$classes['class']['file'] = array(
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
			$classes['class']['checkboxes']['field'][] = 'inline';
			$classes['class']['radio']['field'][]      = 'inline';
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
