<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author      Guido De Gobbis <support@joomtools.de>
 * @copyright   2017 JoomTools.de - All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

defined('_JEXEC') or die;

/**
 * Script file of Joomla CMS
 *
 * @since  1.6.4
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
		$this->minimumJoomla = '3.8';
		$this->minimumPhp    = '7.0';
	}

	/**
	 * Function to act prior to installation process begins
	 *
	 * @param   string     $action    Which action is happening (install|uninstall|discover_install|update)
	 * @param   JInstaller $installer The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.0.0
	 */
	public function preflight($action, $installer)
	{
		/* Removed while sometimes folders will not be reinstalled
		if ($action === 'update')
		{
			jimport('joomla.filesystem.folder');

			$error      = false;
			$pluginPath = JPATH_PLUGINS . '/content/jtf';
			$deletes    = [];
			$deletes[]  = $pluginPath . '/assets';
			$deletes[]  = $pluginPath . '/layouts';
			$deletes[]  = $pluginPath . '/libraries';
			$deletes[]  = $pluginPath . '/tmpl';

			foreach ($deletes as $delete)
			{
				if (is_dir($delete))
				{
					JFolder::delete($delete);
				}
			}
		}

		return true;
		*/
	}
}
