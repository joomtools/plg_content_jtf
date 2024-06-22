<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace JoomTools\Plugin\Content\Jtf\Layout;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Layout\FileLayout as JoomlaFileLayout;

/**
 * Base class for rendering a display layout
 * loaded from a layout file
 *
 * @since  4.0.0
 */
class FileLayout extends JoomlaFileLayout
{
    /**
     * Check if the layout file exists
     *
     * @return  boolean
     *
     * @since  4.0.0
     */
    public function checkLayoutExists()
    {
        $layoutPath = parent::getPath();

        return !empty($layoutPath);
    }
}
