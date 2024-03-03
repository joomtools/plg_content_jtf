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

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\Field\ImagelistField as JoomlaImagelistField;
use Jtf\Form\FormFieldExtension;

/**
 * Supports an HTML select list of image
 *
 * @since  4.0.0
 */
class ImageListField extends JoomlaImagelistField
{
	use FormFieldExtension;
}
