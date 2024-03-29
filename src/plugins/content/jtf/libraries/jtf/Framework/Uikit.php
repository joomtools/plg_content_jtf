<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
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
 *              $classes['muster'] = array(
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
 * @since  4.0.0
 **/
class Uikit
{
    public static $name = 'UIKit v2 (Warp 7)';

    private $_classes;

    private $_orientation;

    public function __construct($orientation = null)
    {
        $this->init();
        $this->setOrientation($orientation);
    }

    public function setOrientation($orientation)
    {
        $this->_orientation = $orientation;
    }

    private function init()
    {
        $classes = array();

        $classes['form'] = array(
            'uk-form',
            'form-validate',
        );

        $classes['default'][] = 'uk-input';
        $classes['gridgroup'] = array(
            'fix-flexbox',
            'uk-form-row',
        );

        $classes['descriptionclass'][] = 'uk-form-help-block';

        $classes['fieldset'] = array(
            'class'            => array(
                'uk-fieldset',
                'uk-margin-bottom',
            ),
            'labelclass'       => array('uk-legend'),
            'descriptionclass' => array('uk-fieldset-desc'),
        );

        $classes['note'] = array(
            'buttonclass' => array(
                'uk-alert-close',
                'uk-close',
            ),
        );

        $classes['calendar'] = array(
            'buttonclass' => array('uk-button'),
            'buttonicon'  => array('uk-icon-calendar'),
        );

        $classes['checkbox'] = array(
            'gridfield' => array('uk-form-controls-text'),
            'class'     => array('uk-checkbox'),
        );

        $classes['checkboxes'] = array(
            'gridfield' => array('uk-form-controls-text'),
            'inline'    => array(
                'class' => array('uk-display-inline'),
            ),
            'options'   => array(
                'class' => array('uk-checkbox'),
            ),
        );

        $classes['radio'] = array(
            'gridfield' => array('uk-form-controls-text'),
            'inline'    => array(
                'class' => array('uk-display-inline'),
            ),
            'options'   => array(
                'class' => array('uk-radio'),
            ),
        );

        $classes['textarea'] = array(
            'class' => array('uk-textarea'),
        );

        $classes['list'] = array(
            'class' => array('uk-select'),
        );

        $classes['file'] = array(
            'uploadicon'  => array('uk-icon-upload'),
            'buttonclass' => array(
                'uk-button',
                'uk-button-success',
            ),
            'buttonicon'  => array('uk-icon-copy'),
        );

        $classes['submit'] = array(
            'buttonclass' => array(
                'uk-button',
                'uk-button-default',
            ),
        );

        $this->_classes = $classes;
    }

    public function getClasses($type)
    {
        $classes     = $this->_classes;
        $orientation = $this->_orientation;

        if ($orientation != 'inline') {
            $classes['gridlabel'][] = 'uk-form-label';
            $classes['gridfield'][] = 'uk-form-controls';
            $classes['gridgroup'][] = 'uk-width-1-1';
        }

        $classes['fieldset']['class'] = array();

        if (!empty($orientationFieldsetClasses = $this->getOrientationFieldsetClasses())) {
            $classes['fieldset']['class'][] = $orientationFieldsetClasses;
        }

        if (empty($classes[$type])) {
            return array();
        }

        return $classes[$type];
    }

    public function getCss()
    {
        $css   = array();
        $css[] = '.jtf .uk-form-icon:not(.uk-form-icon-flip)>select{padding-left:40px!important;}';
        $css[] = '.jtf .uk-form-stacked .uk-form-controls{margin-left:0!important;}';
        $css[] = '.jtf .uk-checkbox,.jtf .uk-radio{margin-left:6px!important;}';
        $css[] = '.jtf .uk-radio{margin-top:4px!important;margin-right:4px!important;}';
        $css[] = '.jtf .uk-button-group {font-size:100.01%!important;}';
        $css[] = '.jtf .minicolors.minicolors-theme-bootstrap.minicolors-position-default input{padding-left: 30px !important;}';
        $css[] = '.jtf .minicolors-theme-bootstrap .hex{max-width:105%!important;width:105%!important;height:auto;}';

        return implode('', $css);
    }

    public function getOrientationFieldsetClasses()
    {
        switch ($this->_orientation) {
            case 'horizontal':
                return 'uk-form-horizontal';

            case 'stacked':
                return 'uk-form-stacked';
        }

        return null;
    }

    public function getOrientationGridGroupClasses()
    {
        return array();
    }

    public function getOrientationGridLabelClasses()
    {
        switch ($this->_orientation) {
            case 'horizontal':
                return array(// 'uk-width-1-4',
                );

            case 'stacked':
                return array(
                    'uk-width-1-1',
                );

            default:
                return array();
        }
    }

    public function getOrientationGridFieldClasses()
    {
        switch ($this->_orientation) {
            case 'horizontal':
                return array(// 'uk-width-3-4',
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
