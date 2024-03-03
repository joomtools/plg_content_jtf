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

use Joomla\CMS\Form\Field\SqlField as JoomlaSqlField;
use Jtf\Form\FormFieldExtension;

/**
 * Supports a custom SQL select list
 *
 * @since  4.0.0
 */
class SQLField extends JoomlaSqlField
{
    use FormFieldExtension;
}
