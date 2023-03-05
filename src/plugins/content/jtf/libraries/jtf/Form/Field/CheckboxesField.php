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

if (version_compare(JVERSION, '4', 'lt'))
{
	FormHelper::loadFieldClass('checkboxes');
}

/**
 * Form Field class for the Joomla Platform.
 * Displays options as a list of checkboxes.
 * Multiselect may be forced to be true.
 *
 * @since  4.0.0
 */
class CheckboxesField extends \JFormFieldCheckboxes
{
	use FormFieldExtension;
}
