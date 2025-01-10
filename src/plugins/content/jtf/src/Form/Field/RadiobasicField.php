<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2025 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\Field\RadiobasicField as JoomlaRadiobasicField;
use JoomTools\Plugin\Content\Jtf\Form\FormFieldExtension;

/**
 * Form Field class for the Joomla Platform.
 * Provides radio button inputs
 *
 * @since  4.0.0
 */
class RadiobasicField extends JoomlaRadiobasicField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $type = 'Radiobasic';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  4.0.0
     */
    protected $layout = 'joomla.form.field.radio.basic';

    use FormFieldExtension;
}
