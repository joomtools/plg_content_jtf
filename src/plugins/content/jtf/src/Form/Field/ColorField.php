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

use Joomla\CMS\Form\Field\ColorField as JoomlaColorField;
use JoomTools\Plugin\Content\Jtf\Form\FormFieldExtension;

/**
 * Color Form Field class for the Joomla Platform.
 * This implementation is designed to be compatible with HTML5's `<input type="color">`
 *
 * @since  4.0.0
 */
class ColorField extends JoomlaColorField
{
    use FormFieldExtension;

    /**
     * The control.
     *
     * @var   string
     *
     * @since  4.0.0
     */
    protected $control;
}
