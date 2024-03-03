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

use Joomla\CMS\Form\Field\PasswordField as JoomlaPasswordField;
use Jtf\Form\FormFieldExtension;

/**
 * Form Field class for the Joomla Platform.
 * Text field for passwords
 *
 * @note   Two password fields may be validated as matching using JFormRuleEquals
 * @since  4.0.0
 */
class PasswordField extends JoomlaPasswordField
{
    use FormFieldExtension;
}
