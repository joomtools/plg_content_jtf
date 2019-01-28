<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2018 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Form\Field;

defined('JPATH_PLATFORM') or die;

/**
 * Supports an HTML select list of image
 *
 * @since   3.0.0
 */
class ImageListField extends FileListField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.0.0
	 */
	protected $type = 'ImageList';

	/**
	 * Method to get the list of images field options.
	 * Use the filter attribute to specify allowable file extensions.
	 *
	 * @return   array  The field option objects.
	 * @since    3.0.0
	 */
	protected function getOptions()
	{
		// Define the image file type filter.
		$this->filter = '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$|\.jpeg$|\.psd$|\.eps$';

		// Get the field options.
		return parent::getOptions();
	}
}
