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

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Jtf\Form\Field\SubformField;
use Jtf\Form\Form;

/**
 * Form rule to validate subforms field-wise.
 *
 * @since  4.0.0
 */
class SubformRule extends FormRule
{
    /**
     * Method to test given values for a subform.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form
     *                                       field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as as an array container for
     *                                       the field. For example if the field has name="foo" and the group value is
     *                                       set to "bar" then the full field name would end up being "bar[foo]".
     * @param   Registry|null      $input    An optional Registry object with the entire data set to validate against
     *                                       the entire form.
     * @param   Form|null          $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since  4.0.0
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, $input = null, $form = null)
    {
        // If the field is empty and not required, the field is valid.
        $disabled = ((string) $element['disabled'] == 'true' || (string) $element['disabled'] == 'disabled' || (string) $element['disabled'] == '1');

        if ($disabled) {
            return true;
        }

        $return = true;
        $name   = (string) $element['name'];
        $key    = $group ? $group . '.' . $name : $name;

        // Get the form field object.
        $field = $form->getField($name, $group);

        if (!($field instanceof SubformField)) {
            throw new \UnexpectedValueException(sprintf('%s is no subform field.', $name));
        }

        if ($value === null) {
            return true;
        }

        $subForm = $field->loadSubForm();

        // Multiple values: Validate every row.
        if ($field->multiple) {
            foreach ($value as $row) {
                if ($subForm->validate($row) === false) {
                    // Pass the first error that occurred on the subform validation.
                    $form->setErrors($subForm->getErrors(), $key);

                    $return = false;
                }
            }
        } // Single value.
        else {
            if ($subForm->validate($value) === false) {
                // Pass the first error that occurred on the subform validation.
                $form->setErrors($subForm->getErrors(), $key);

                $return = false;
            }
        }

        return $return;
    }
}
