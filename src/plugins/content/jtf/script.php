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
	 * Function to act prior to installation process begins
	 *
	 * @param   string     $action    Which action is happening (install|uninstall|discover_install|update)
	 * @param   JInstaller $installer The class calling this method
	 *
	 * @return  boolean  True on success
	 * @since   3.0.0
	 */
	public function preflight($action, $installer)
	{
		$app = JFactory::getApplication();
		JFactory::getLanguage()->load('plg_content_jtf', dirname(__FILE__));

		if (version_compare(PHP_VERSION, $this->minimumPhp, 'lt'))
		{
			$app->enqueueMessage(JText::_('PLG_CONTENT_JTF_MINPHPVERSION'), 'error');

			return false;
		}

		if (version_compare(JVERSION, $this->minimumJoomla, 'lt'))
		{
			$app->enqueueMessage(JText::_('PLG_CONTENT_JTF_MINJVERSION'), 'error');

			return false;
		}

		return true;
	}
}
