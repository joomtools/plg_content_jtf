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

use Joomla\CMS\Form\Field\SpacerField as JoomlaSpacerField;
use Jtf\Form\FormFieldExtension;

/**
 * Form Field class for the Joomla Platform.
 * Provides spacer markup to be used in form layouts.
 *
 * @since  4.0.0
 */
class SpacerField extends JoomlaSpacerField
{
    use FormFieldExtension;
}
