<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/

defined('_JEXEC') or die('Restricted access');

/**
 * Class JTFFrameworkUikit set basic css for used framework
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

class JTFFrameworkUikit
{
	public static $name = 'UIKit v2';

	private $classes;

	public function __construct($formclass = array())
	{
		$classes = array();
		$inline  = in_array('uk-form-inline', $formclass);

		$classes['css'] = '.uk-form-icon:not(.uk-form-icon-flip)>select { padding-left: 40px !important; }';

		$classes['class']['form']        = array_unique(array_merge(array('uk-form', 'form-validate'), $formclass));
		$classes['class']['default'][]   = 'uk-input';
		$classes['class']['gridgroup'][] = 'uk-form-row';

		if (!$inline)
		{
			$classes['class']['gridlabel'][] = 'uk-form-label';
			$classes['class']['gridfield'][] = 'uk-form-controls';
		}

		$classes['class']['note'] = array(
			'buttons' => array(
				'class' => 'uk-alert-close uk-close',
				'icon'  => '',
			),
		);

		$classes['class']['calendar'] = array(
			'buttons' => array(
				'class' => 'uk-button uk-button-small',
				'icon'  => 'uk-icon-calendar',
			),
		);

		$classes['class']['checkboxes'] = array(
			'field'   => array('checkboxes'),
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
			'field' => array('uk-textarea'),
		);

		$classes['class']['list'] = array(
			'field' => array('uk-select'),
		);

		$classes['class']['file'] = array(
			'uploadicon' => 'uk-icon-upload',
			'buttons'    => array(
				'class' => 'uk-button uk-button-success',
				'icon'  => 'uk-icon-copy',
			),
		);

		$classes['class']['submit'] = array(
			'buttons' => array(
				'class' => 'uk-button uk-button-default',
			),
		);

		if ($inline)
		{
			$classes['class']['checkboxes']['field'][] = 'uk-display-inline';
			$classes['class']['radio']['field'][] = 'uk-display-inline';
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
