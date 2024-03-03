<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Rule;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Jtf\Form\Form;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  4.0.0
 */
class FileRule extends FormRule
{
    /**
     * Method to test the url for a valid parts.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form
     *                                       field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for
     *                                       the field. For example if the field has name="foo" and the group value is
     *                                       set to "bar" then the full field name would end up being "bar[foo]".
     * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against
     *                                       the entire form.
     * @param   ?Form              $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since  4.0.0
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, $form = null)
    {
        $return   = true;
        $required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');
        $value    = (array) $value;
        $sumSize  = 0;

        if (isset($value['max_file_size'])) {
            $maxFileSize = $value['max_file_size'];

            unset($value['max_file_size']);
        }

        if (!$required && empty($value)) {
            return true;
        }

        if ($required && empty($value)) {
            return false;
        }

        $accept = (string) $element['accept'];

        if (!$accept) {
            return true;
        }

        $acceptFileType = array();
        $acceptFileMime = array();
        $accept         = explode(',', (string) $element['accept']);

        foreach ($accept as $type) {
            if (strpos($type, '.') !== false) {
                $acceptFileType[] = ltrim($type, '.');
            } elseif (strpos($type, '/') !== false) {
                $acceptFileMime[] = trim(str_replace('*', '.*', $type));
            }
        }

        $regexAllowedType = '/\.(?:' . implode('|', $acceptFileType) . ')$/i';
        $regexAllowedMime = '@^(' . implode('|', $acceptFileMime) . ')$@i';

        foreach ($value as $file) {
            $test = false;

            if ($regexAllowedMime) {
                $test = preg_match($regexAllowedMime, $file->type);
            }

            if (!$test && $regexAllowedType) {
                $test = preg_match($regexAllowedType, $file->name);
            }

            if (!$test) {
                $return = false;
            }

            $sumSize += $file->size;
        }

        if ($sumSize > $maxFileSize) {
            $message = Text::_($element['label']);
            $message = Text::sprintf('JTF_FILE_FIELD_ERROR', $message);

            throw new \UnexpectedValueException($message);
        }

        return $return;
    }
}
