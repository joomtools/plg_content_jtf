<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    Copyright 2020 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Layout;

defined('JPATH_PLATFORM') or die;

/**
 * Base class for rendering a display layout
 * loaded from a layout file
 *
 * @since  3.0.0
 */
class FileLayout extends \Joomla\CMS\Layout\FileLayout
{
	/**
	 * Check if the layout file exists
	 *
	 * @return  boolean
	 *
	 * @since  3.0.0
	 */
	public function checkLayoutExists(): bool
	{
		$layoutPath = parent::getPath();

		return (!empty($layoutPath));
	}
}
