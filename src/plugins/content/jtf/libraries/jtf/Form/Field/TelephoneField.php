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

use Jtf\Form\FormFieldExtension;

/**
 * Form Field class for the Joomla Platform.
 * Supports a text field telephone numbers.
 *
 * @link   http://www.w3.org/TR/html-markup/input.tel.html
 * @see    JFormRuleTel for telephone number validation
 * @see    JHtmlTel for rendering of telephone numbers
 * @since  1.7.0
 */
class TelephoneField extends TelField
{
	use FormFieldExtension;
}
