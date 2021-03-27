<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('JPATH_PLATFORM') or die;

JLoader::registerNamespace('Jtf', JPATH_PLUGINS . '/content/jtf/libraries/jtf', false, false, 'psr4');

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

/**
 * List of supported frameworks
 *
 * @since   __DEPLOY_VERSION__
 */
class JFormFieldFrwk extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   __DEPLOY_VERSION__
	 */
	protected $type = 'Frwk';

	/**
	 * @var     array
	 * @since   __DEPLOY_VERSION__
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

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		parent::setup($element, $value, $group);

		if (empty($this->value))
		{
			$this->value = 'bs2';

			if (version_compare(JVERSION, '4', 'ge'))
			{
				$this->value = 'bs4';
			}
		}

		return true;
	}
}
