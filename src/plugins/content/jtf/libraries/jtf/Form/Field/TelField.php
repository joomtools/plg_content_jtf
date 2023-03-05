<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormHelper;
use Jtf\Form\FormFieldExtension;

if (version_compare(JVERSION, '4', 'lt')) {
    FormHelper::loadFieldClass('tel');
}

/**
 * Form Field class for the Joomla Platform.
 * Supports a text field telephone numbers.
 *
 * @since  4.0.0
 */
class TelField extends \JFormFieldTel
{
    /**
     * The form field type.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $type = 'Tel';

    use FormFieldExtension;
}
