<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2025 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace JoomTools\Plugin\Content\Jtf\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\Field\TemplatestyleField as JoomlaTemplatestyleField;
use JoomTools\Plugin\Content\Jtf\Form\FormFieldExtension;

/**
 * Supports a select grouped list of template styles
 *
 * @since  4.0.0
 */
class TemplatestyleField extends JoomlaTemplatestyleField
{
    use FormFieldExtension;
}
