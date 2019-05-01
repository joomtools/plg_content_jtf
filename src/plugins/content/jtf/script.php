<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author      Guido De Gobbis <support@joomtools.de>
 * @copyright   2019 JoomTools.de - All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;

/**
 * Script file of Joomla CMS
 *
 * @since  3.0.0
 */
class PlgContentJtfInstallerScript
{
	/**
	 * Minimum Joomla version to install
	 *
	 * @var   string
	 *
	 * @since   3.0.0
	 */
	public $minimumJoomla = '3.9';
	/**
	 * Minimum PHP version to install
	 *
	 * @var   string
	 *
	 * @since   3.0.0
	 */
	public $minimumPhp = '7.0';

	/**
	 * Function to act prior to installation process begins
	 *
	 * @param   string      $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param   JInstaller  $installer  The class calling this method
	 *
	 * @return   boolean  True on success
	 * @throws   Exception
	 *
	 * @since   3.0.0
	 */
	public function preflight($action, $installer)
	{
		$notDeleted = '';
		$app        = Factory::getApplication();
		$pluginPath = JPATH_PLUGINS . '/content/jtf';

		Factory::getLanguage()->load('plg_content_jtf', dirname(__FILE__));

		if (version_compare(PHP_VERSION, $this->minimumPhp, 'lt'))
		{
			$app->enqueueMessage(Text::sprintf('PLG_CONTENT_JTF_MINPHPVERSION', $this->minimumPhp), 'error');

			return false;
		}

		if (version_compare(JVERSION, $this->minimumJoomla, 'lt'))
		{
			$app->enqueueMessage(Text::sprintf('PLG_CONTENT_JTF_MINJVERSION', $this->minimumJoomla), 'error');

			return false;
		}

		if ($action === 'update')
		{
			$deletes = [];

			$deletes['folder'] = array(
				// before 3.0.0-rc31
				$pluginPath . '/assets/fields',
				$pluginPath . '/assets/frameworks',
				$pluginPath . '/assets/j3.7.x',
				$pluginPath . '/assets/j3.8.x',
				$pluginPath . '/assets/rules',
				$pluginPath . '/assets/js/system',
				$pluginPath . '/libraries/frameworks',
			);

			$deletes['file'] = array(
				// before 3.0.0-rc31
				$pluginPath . '/assets/file.php',
				$pluginPath . '/layouts/joomla/form/renderfield.bs3.php',
				$pluginPath . '/layouts/joomla/form/renderfield.uikit.php',
				$pluginPath . '/layouts/joomla/form/renderfield.uikit3.php',
				$pluginPath . '/layouts/jtf/form.bs3.php',
				$pluginPath . '/layouts/jtf/form.uikit3.php',
				JPATH_ROOT . '/administrator/language/de-DE/de-DE.plg_content_jtf.ini',
				JPATH_ROOT . '/administrator/language/de-DE/de-DE.plg_content_jtf.sys.ini',
				JPATH_ROOT . '/administrator/language/en-GB/en-GB.plg_content_jtf.ini',
				JPATH_ROOT . '/administrator/language/en-GB/en-GB.plg_content_jtf.sys.ini',
			);

			foreach ($deletes as $key => $orphans)
			{
				$notDeleted .= $this->deleteOrphans($key, $orphans);
			}

			if (!empty($notDeleted))
			{
				$app->enqueueMessage($notDeleted, 'error');
			}
		}

		return true;
	}

	/**
	 * @param   string  $type     Wich type are orphans of (file or folder)
	 * @param   array   $orphans  Array of files or folders to delete
	 *
	 * @return   string
	 *
	 * @since   3.0.0
	 */
	private function deleteOrphans($type, array $orphans)
	{
		$notDeleted = '';

		foreach ($orphans as $item)
		{
			if ($type == 'folder')
			{
				if (is_dir($item))
				{
					if (Folder::delete($item) === false)
					{
						$notDeleted .= Text::sprintf('PLG_CONTENT_JTF_NOT_DELETED', $item);
					}
				}
			}
			if ($type == 'file')
			{
				if (is_file($item))
				{
					if (File::delete($item) === false)
					{
						$notDeleted .= Text::sprintf('PLG_CONTENT_JTF_NOT_DELETED', $item);
					}
				}
			}
		}

		return $notDeleted;
	}
}
