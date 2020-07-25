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
	FormHelper::loadFieldClass('url');
}

/**
 * Form Field class for the Joomla Platform.
 * Supports a URL text field
 *
 * @link    http://www.w3.org/TR/html-markup/input.url.html#input.url
 * @see     JFormRuleUrl for validation of full urls
 * @since   3.0.0
 */
class UrlField extends \JFormFieldUrl
{
	use FormFieldExtension;
}
