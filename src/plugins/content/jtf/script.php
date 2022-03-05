<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2021 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Script file of Joomla CMS
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgContentJtfInstallerScript
{
	/**
	 * Minimum Joomla version to install
	 *
	 * @var   string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $minimumJoomla = '3.10';

	/**
	 * Minimum PHP version to install
	 *
	 * @var   string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $minimumPhp = '5.6';

	/**
	 * Default params for the plugin to merge
	 * befor save in DB on update
	 *
	 * @var   string  Formated as JSON
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $pluginDefaultParams = array(
		'captcha'                         => '1',
		'show_field_description_as'       => 'tooltip',
		'field_marker'                    => 'required',
		'field_marker_place'              => 'label',
		'show_required_field_description' => '1',
		'filloutTime_onoff'               => '1',
		'filloutTime'                     => 10,
		'file_path'                       => 'uploads',
		'file_clear'                      => 60,
		'framework'                       => 'bs2',
		'debug'                           => '0',
	);

	/**
	 * Function to act prior the installation process begins
	 *
	 * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param   Installer  $installer  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since  __DEPLOY_VERSION__
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
			$deletes = array();

			$deletes['folder'] = array(
				// Before 3.0.0-rc31
				$pluginPath . '/assets/fields',
				$pluginPath . '/assets/frameworks',
				$pluginPath . '/assets/j3.7.x',
				$pluginPath . '/assets/j3.8.x',
				$pluginPath . '/assets/rules',
				$pluginPath . '/assets/js/system',
				$pluginPath . '/libraries/frameworks',
				// Since 4.0.0-rc7
				$pluginPath . '/layouts/joomla3',
			);

			$deletes['file'] = array(
				// Before 3.0.0-rc31
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
	 * Called after any type of action
	 *
	 * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param   Installer  $installer  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function postflight($action, $installer)
	{
		if ($action === 'update')
		{
			$app         = Factory::getApplication();
			$extensionId = $this->updatePlgConfig();

			if (false === $extensionId)
			{
				$app->enqueueMessage(Text::_('PLG_CONTENT_JTF_CFG_NOT_CHANGED'), 'warning');

				return;
			}

			$installer->refreshManifestCache($extensionId);
		}
	}
	/**
	 * @param   string  $type     Which type are orphans of (file or folder)
	 * @param   array   $orphans  Array of files or folders to delete
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
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

	/**
	 * Update plugin configuration
	 *
	 * @return  int|boolean  Extension id on success or false
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function updatePlgConfig()
	{
		$db    = Factory::getDbo();
		$where = array(
			$db->quoteName('name') . ' = ' . $db->quote('JTF_XML_NAME'),
			$db->quoteName('type') . ' = ' . $db->quote('plugin'),
			$db->quoteName('folder') . ' = ' . $db->quote('content'),
			$db->quoteName('element') . ' = ' . $db->quote('jtf'),
		);
		$select =  array(
			$db->quoteName('extension_id'),
			$db->quoteName('params'),
		);

		try
		{
			$result = $db->setQuery(
				$db->getQuery(true)
					->select($select)
					->from($db->quoteName('#__extensions'))
					->where($where)
			)->loadObject();
		}
		catch (Exception $e)
		{
			return false;
		}

		$params = json_decode($result->params);

		if ($params->framework == 'joomla')
		{
			$params->framework = 'bs2';

			if (version_compare(JVERSION, '4', 'ge'))
			{
				$params->framework = 'bs5';
			}
		}

		$captcha = '0';

		if (!empty($params->captcha))
		{
			$captcha = '1';
		}

		$params->captcha = $captcha;

		unset($params->component_exclusions);

		$params          = new Registry($params);
		$newPluginParams = new Registry($this->pluginDefaultParams);

		$newPluginParams->merge($params);

		$newPluginParams = $newPluginParams->toString();

		try
		{
			$db->setQuery(
				$db->getQuery(true)
					->update($db->quoteName('#__extensions'))
					->set($db->quoteName('params') . ' = ' . $db->quote($newPluginParams))
					->where(
						$db->quoteName('extension_id') . '=' . $db->quote((int) $result->extension_id)
					)
			)->execute();
		}
		catch (Exception $e)
		{
			return false;
		}

		return (int) $result->extension_id;
	}
}
