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

use Joomla\CMS\Form\FormField;
use Jtf\Form\FormFieldExtension;

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @since  3.0.0
 */
class PlzField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $type = 'Plz';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var   string
	 *
	 * @since  3.0.0
	 */
	protected $layout = 'joomla.form.field.plz';

	use FormFieldExtension;
}
