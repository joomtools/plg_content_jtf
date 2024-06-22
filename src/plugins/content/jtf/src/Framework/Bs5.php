<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace JoomTools\Plugin\Content\Jtf\Framework;

defined('_JEXEC') or die('Restricted access');

/**
 * Class FrameworkBs4 set basic css for used framework
 *
 * Pattern for basic field classes
 *
 * Define basic classes for field type 'muster'
 *              $classes['muster'] = [
 *
 *                  Set a default class for the field, addition to the manifest attribute 'class'.
 *                  For fields such as radio or checkboxes, the class is set to the enclosing tag.
 *                  'field' => ['defaultclass'],
 *
 *                  Set this to define defaults for options for fields such as radio or checkboxes
 *                  'options' => [
 *                      'labelclass' => ['labelclass'],
 *                      'class'      => ['optionclass'],
 *                  ],
 *
 *                  Set this to define defaults for the button of fields such as calendar
 *                  'buttons' => [
 *                      'class' => 'uk-button uk-button-small',
 *                      'icon'  => 'uk-icon-calendar',
 *                   ],
 *              ];
 *
 * @since  4.0.0
 **/
class Bs5
{
    public static $name = 'Bootstrap v5 (Joomla 4/5 core)';

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
        $classes = [];

        // Define base classes to render on all fields
        $classes['form'][]           = 'form-validate';
        $classes['default'][]        = 'form-control';
        $classes['gridgroup']        = [
            'form-group',
            'd-flex',
            'align-items-center',
        ];
        $classes['gridlabel'][]      = 'col-form-label';
        $classes['fieldset']         = [
            'labelclass' => [
                'col-form-label-test',
            ],
            'descriptionclass' => [
                'text-muted',
                'col-form-text',
            ],
        ];
        $classes['descriptionclass'] = [
            'form-text',
            'text-muted',
        ];

        //
        $classes['note'] = [
            'buttonclass' => [
                'close',
            ],
            'buttonicon'  => [
                '&times;',
            ],
        ];

        $classes['calendar'] = [
            'buttonclass' => [
                'btn',
                'btn-primary',
            ],
            'buttonicon'  => [
                'icon-calendar',
            ],
        ];

        $classes['list'] = [
            'class' => [
                'custom-select',
            ],
        ];

        $classes['checkboxes'] = [
            'class'   => [
                'form-check',
            ],
            'inline'  => [
                'class' => [
                    'form-check-inline',
                ],
            ],
            'options' => [
                'class'      => [
                    'form-check-input',
                ],
                'labelclass' => [
                    'form-check-label',
                ],
            ],
        ];

        $classes['radio'] = [
            'class'   => [
                'form-check',
            ],
            'inline'  => [
                'class' => [
                    'form-check-inline',
                ],
            ],
            'options' => [
                'class'      => [
                    'form-control',
                    'form-check-input',
                ],
                'labelclass' => [
                    'form-check-label',
                ],
            ],
        ];

        $classes['buttongroup'] = [
            'options' => [
                'class'      => [
                    'btn-check',
                ],
                'labelclass' => [
                    'btn',
                ],
            ],
        ];

        $classes['switcher'] = [
            'class'   => [
                'form-check',
            ],
            'options' => [
                'class'      => [
                    'form-check-input',
                ],
                'labelclass' => [
                    'form-check-label',
                ],
            ],
        ];

        $classes['textarea'] = [
            'class' => [
                'form-control',
            ],
        ];

        $classes['file'] = [
            'class'       => [
                'form-control-file',
            ],
            'uploadicon'  => [
                'icon-upload',
            ],
            'buttonclass' => [
                'btn',
                'btn-success',
            ],
            'buttonicon'  => [
                'icon-copy',
            ],
        ];

        $classes['submit'] = [
            'buttonclass' => [
                'btn',
                'btn-primary',
            ],
        ];

        $this->_classes = $classes;
    }

    public function getClasses($type)
    {
        $classes     = $this->_classes;
        $orientation = $this->_orientation;

        if ($orientation == 'horizontal') {
            $classes['note']['gridfield'][] = 'col-sm-12';
        }

        if ($orientation == 'inline') {
            $classes['checkboxes']['class'][] = 'form-check-inline';
            $classes['radio']['class'][]      = 'form-check-inline';
        }

        $classes['fieldset']['class'] = [];

        if (!empty($orientationFieldsetClasses = $this->getOrientationFieldsetClasses())) {
            $classes['gridgroup'][] = $orientationFieldsetClasses;
        }

        if (empty($classes[$type])) {
            return [];
        }

        return $classes[$type];
    }

    public function getCss()
    {
        $css   = [];
        $css[] = '.switcher .toggle-outside {border: var(--border-width) solid var(--template-bg-dark-20];border-radius: var(--border-radius];}';

        return implode('', $css);
    }

    private function getOrientationFieldsetClasses()
    {
        if ($this->_orientation == 'horizontal') {
            return 'row';
        }

        return null;
    }

    public function getOrientationGridGroupClasses()
    {
        return [];
    }

    public function getOrientationGridLabelClasses()
    {
        switch ($this->_orientation) {
            case 'horizontal':
                return [
                    'col-sm-3',
                ];

            case 'stacked':
                return [
                    'col-sm-12',
                ];

            default:
                return [];
        }
    }

    public function getOrientationGridFieldClasses()
    {
        switch ($this->_orientation) {
            case 'horizontal':
                return [
                    'col-sm-9',
                ];

            case 'stacked':
                return [
                    'col-sm-12',
                ];

            default:
                return [];
        }
    }
}
