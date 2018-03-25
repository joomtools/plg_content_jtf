<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
	 * Function to act prior to installation process begins
	 *
	 * @param   string      $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param   JInstaller  $installer  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.7.0
	 */
	public function preflight($action, $installer)
	{
		if ($action === 'update')
		{
			jimport('joomla.filesystem.folder');

			$path = JPATH_PLUGINS . '/content/jtf';
			$delete = JFolder::delete($path);

			if (!$delete)
			{
				throw new \RuntimeException(
					\JText::_('Can\'t delete old Files, please uninstall older version before.') . '<br />'
				);
				return false;
			}
		}

		return true;
	}
}
