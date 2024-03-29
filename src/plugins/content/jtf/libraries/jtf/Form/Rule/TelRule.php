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
use Jtf\Form\Form;

/**
 * Form Rule class for the Joomla Platform
 *
 * @since   4.0.0
 */
class TelRule extends FormRule
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
        $required = ((string) $element['required'] === 'true' || (string) $element['required'] === 'required');

        if (!$required && empty($value)) {
            return true;
        }

        /*
         * @link http://www.nanpa.com/
         * @link http://tools.ietf.org/html/rfc4933
         * @link http://www.itu.int/rec/T-REC-E.164/en
         *
         * Regex by Steve Levithan
         * @link http://blog.stevenlevithan.com/archives/validate-phone-number
         * @note that valid ITU-T and EPP must begin with +.
         */
        $regexArray = array(
            'NANP'  => '/^(?:\+?1[-. ]?)?\(?([2-9][0-8][0-9])\)?[-. ]?([2-9][0-9]{2})[-. ]?([0-9]{4})$/',
            'ITU-T' => '/^\+(?:[0-9] ?){6,14}[0-9]$/',
            'EPP'   => '/^\+[0-9]{1,3}\.[0-9]{4,14}(?:x.+)?$/',
        );

        if (isset($element['plan'])) {
            $plan = (string) $element['plan'];

            if ($plan === 'northamerica' || $plan === 'us') {
                $plan = 'NANP';
            } else if ($plan === 'International' || $plan === 'int' || $plan === 'missdn' || !$plan) {
                $plan = 'ITU-T';
            } else if ($plan === 'IETF') {
                $plan = 'EPP';
            }

            $regex = $regexArray[$plan];

            // Test the value against the regular expression.
            if (preg_match($regex, $value) == false) {
                return false;
            }
        } else {
            /*
             * If the rule is set but no plan is selected just check that there are between
             * 7 and 15 digits inclusive and no illegal characters (but common number separators
             * are allowed).
             */
            $cleanValue = preg_replace('/[+. \-(\)\/]/', '', $value);
            $regex      = '/^[0-9]{7,15}?$/';

            if (preg_match($regex, $cleanValue) == true) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
}
