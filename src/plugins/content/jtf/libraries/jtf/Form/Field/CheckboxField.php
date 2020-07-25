<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormHelper;
use Jtf\Form\FormFieldExtension;

if (version_compare(JVERSION, '4', 'lt'))
{
	FormHelper::loadFieldClass('checkbox');
}

/**
 * Form Field class for the Joomla Platform.
 * Single checkbox field.
 * This is a boolean field with null for false and the specified option for true
 *
 * @link    http://www.w3.org/TR/html-markup/input.checkbox.html#input.checkbox
 * @since   3.0.0
 */
class CheckboxField extends \JFormFieldCheckbox
{
	use FormFieldExtension;
}
