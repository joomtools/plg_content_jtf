<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormHelper;
use Jtf\Form\FormFieldExtension;

if (version_compare(JVERSION, '4', 'lt'))
{
	FormHelper::loadFieldClass('color');
}

/**
 * Color Form Field class for the Joomla Platform.
 * This implementation is designed to be compatible with HTML5's `<input type="color">`
 *
 * @since  3.0.0
 */
class ColorField extends \JFormFieldColor
{
	/**
	 * The control.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $control;

	use FormFieldExtension;
}
