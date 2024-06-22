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

use Joomla\CMS\Form\Field\MediaField as JoomlaMediaField;
use JoomTools\Plugin\Content\Jtf\Form\FormFieldExtension;

/**
 * Provides a modal media selector including upload mechanism
 *
 * @since  4.0.0
 */
class MediaField extends JoomlaMediaField
{
    use FormFieldExtension;
}
