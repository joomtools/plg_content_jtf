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
	FormHelper::loadFieldClass('groupedlist');
}

/**
 * Form Field class for the Joomla Platform.
 * Provides a grouped list select field.
 *
 * @since  3.0.0
 */
class GroupedListField extends \JFormFieldGroupedList
{
	use FormFieldExtension;
}
