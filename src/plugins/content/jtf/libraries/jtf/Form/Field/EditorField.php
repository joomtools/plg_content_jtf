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
 * A textarea field for content creation
 *
 * @since  __DEPLOY_VERSION__
 */
class EditorField extends \Joomla\CMS\Form\Field\EditorField
{
	use FormFieldExtension;
}
