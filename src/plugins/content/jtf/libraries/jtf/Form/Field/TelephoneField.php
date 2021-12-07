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

use Jtf\Form\FormFieldExtension;

/**
 * Form Field class for the Joomla Platform.
 * Supports a text field telephone numbers.
 *
 * @since  __DEPLOY_VERSION__
 */
class TelephoneField extends TelField
{
	use FormFieldExtension;
}