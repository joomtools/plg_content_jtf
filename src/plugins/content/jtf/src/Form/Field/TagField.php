<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace JoomTools\Plugin\Content\Jtf\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\Field\TagField as JoomlaTagField;
use JoomTools\Plugin\Content\Jtf\Form\FormFieldExtension;

/**
 * List of Tags field.
 *
 * @since  4.0.0
 */
class TagField extends JoomlaTagField
{
    use FormFieldExtension;
}
