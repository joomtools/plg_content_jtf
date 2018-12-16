<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2018 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;

/**
 * Script file of Joomla CMS
 *
 * @since   3.0.0
 */
class PlgContentJtfInstallerScript
{
	/**
	 * Extension script constructor.
	 *
	 * @since   3.0.0
	 */
	public function __construct()
	{
		// Define the minumum versions to be supported.
		$this->minimumJoomla = '3.9';
		$this->minimumPhp    = '7.1';
	}

	/**
	 * Function to act prior the installation process begins
	 *
	 * @param   string      $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param   JInstaller  $installer  The class calling this method
	 *
	 * @return   boolean  True on success
	 * @since    3.0.0
	 */
	public function preflight($action, $installer)
	{
		$app = Factory::getApplication();
		Factory::getLanguage()->load('plg_content_jtf', dirname(__FILE__));

		if (version_compare(PHP_VERSION, $this->minimumPhp, 'lt'))
		{
			$app->enqueueMessage(Text::_('PLG_CONTENT_JTF_MINPHPVERSION'), 'error');

			return false;
		}

		if (version_compare(JVERSION, $this->minimumJoomla, 'lt'))
		{
			$app->enqueueMessage(Text::_('PLG_CONTENT_JTF_MINJVERSION'), 'error');

			return false;
		}

		if ($action === 'update')
		{
			$pluginPath = JPATH_PLUGINS . '/content/jtf';
			$deletes    = [];

			$deletes['folder'] = array(
				// JTF 3.0.0-rc24 -> 3.0.0-rc25
				$pluginPath . '/layouts/subform/repeatable',
				$pluginPath . '/libraries/joomla/form/rules',
				$pluginPath . '/libraries/jtf/Frameworks',
				// JTF 3.0.0-rc22 -> 3.0.0-rc23
				$pluginPath . '/assets/js/system',
			);

			$deletes['file']   = array(
				// JTF 3.0.0-rc24 -> 3.0.0-rc25
				$pluginPath . '/layouts/note.bs3.php',
				$pluginPath . '/libraries/joomla/form/FormField.php',
				$pluginPath . '/libraries/joomla/form/fields/combo.php',
				$pluginPath . '/libraries/joomla/form/fields/file.php',
				$pluginPath . '/libraries/joomla/form/fields/list.php',
				$pluginPath . '/libraries/joomla/form/fields/note.php',
				$pluginPath . '/libraries/joomla/form/fields/plz.php',
				$pluginPath . '/libraries/joomla/form/fields/subform.php',
				$pluginPath . '/libraries/joomla/form/fields/submit.php',
			);

			foreach ($deletes as $key => $delete)
			{
				if ($key == 'folder')
				{
					if (is_dir($delete))
					{
						Folder::delete($delete);
					}
				}

				if ($key == 'file')
				{
					if (is_file($delete))
					{
						File::delete($delete);
					}
				}
			}
		}



		return true;
	}
}
