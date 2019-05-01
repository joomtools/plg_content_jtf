<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2018 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Field\ListField;

/**
 * List of supported frameworks
 *
 * @since   3.0.0
 */
class JFormFieldFrwk extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   3.0.0
	 */
	protected $type = 'Frwk';

	/**
	 * @var     array
	 * @since   3.0.0
	 */
	protected $exclude = array(
		'FrameworkHelper'
	);

	/**
	 * Method to get the field options.
	 *
	 * @return   array  The field option objects.
	 * @since    3.0.0
	 */
	protected function getOptions()
	{
		$frwkPath = JPATH_PLUGINS . '/content/jtf/libraries/jtf/Framework';
		$frwk = Folder::files($frwkPath);

		$options = array();

		$options[] = (object) array(
			'value' => 'joomla',
			'text'  => 'Joomla',
		);

		foreach ($frwk as $file)
		{
			$fileName = File::stripExt($file);

			if (in_array($fileName, $this->exclude))
			{
				continue;
			}

			$framework = 'Jtf\\Framework\\' . ucfirst($fileName);
			$fileRealName = $framework::$name;

			$tmp = array(
				'value'      => strtolower($fileName),
				'text'       => $fileRealName,
			);

			// Add the option object to the result set.
			$options[] = (object) $tmp;

		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
