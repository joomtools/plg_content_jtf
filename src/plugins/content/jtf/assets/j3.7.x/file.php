<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2017 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
**/

defined('JPATH_PLATFORM') or die;

/**
 * Base class for rendering a display layout
 * loaded from from a layout file
 *
 * @see    https://docs.joomla.org/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since  3.0
 */
class JTLayoutFile extends JLayoutFile
{

	/**
	 * Method to instantiate the file-based layout.
	 *
	 * @param   string  $layoutId  Dot separated path to the layout file, relative to base path
	 * @param   string  $basePath  Base path to use when loading layout files
	 * @param   mixed   $options   Optional custom options to load. Registry or array format [@since 3.2]
	 *
	 * @since   3.0
	 */
	public function __construct($layoutId, $basePath = null, $options = null)
	{
		parent::__construct($layoutId, $basePath, $options);
	}

	/**
	 * Set suffixes to search layouts
	 *
	 * @param   mixed  $suffixes  String with a single suffix or 'auto' | 'none' or array of suffixes
	 *
	 * @return  self
	 *
	 * @since   3.5
	 */
	public function setSuffixes(array $suffixes)
	{
		$this->options->set('suffixes', $suffixes);

		return $this;
	}
}
