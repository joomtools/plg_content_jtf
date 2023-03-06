<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   Content.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    2023 JoomTools.de - All rights reserved.
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
 * @since  4.0.0
 */
class PlgContentJtfInstallerScript
{
    /**
     * Minimum Joomla version to install
     *
     * @var    string
     *
     * @since  4.0.0
     */
    public $minimumJoomla = '3.10';

    /**
     * Minimum PHP version to install
     *
     * @var    string
     *
     * @since  4.0.0
     */
    public $minimumPhp = '5.6';

    /**
     * Extension ID, if installed
     *
     * @var    string|null
     *
     * @since  4.0.0
     */
    private $extensionId;

    /**
     * Previous version
     *
     * @var    string|null
     *
     * @since  4.0.0
     */
    private $presentVersion;

    /**
     * Previous version
     *
     * @var    Registry|null
     *
     * @since  4.0.0
     */
    private $pluginParams;

    /**
     * Default params for the plugin to merge
     * befor save in DB on update
     *
     * @var    string  Formated as JSON
     *
     * @since  4.0.0
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
     * @since   4.0.0
     */
    public function preflight($action, $installer)
    {
        $app        = Factory::getApplication();
        $pluginPath = JPATH_PLUGINS . '/content/jtf';

        Factory::getLanguage()->load('plg_content_jtf', dirname(__FILE__));

        if (version_compare(PHP_VERSION, $this->minimumPhp, 'lt')) {
            $app->enqueueMessage(Text::sprintf('PLG_CONTENT_JTF_MINPHPVERSION', $this->minimumPhp), 'error');

            return false;
        }

        if (version_compare(JVERSION, $this->minimumJoomla, 'lt')) {
            $app->enqueueMessage(Text::sprintf('PLG_CONTENT_JTF_MINJVERSION', $this->minimumJoomla), 'error');

            return false;
        }

        if ($action === 'update') {
            if (version_compare(JVERSION, '4', 'lt')) {
                $extensionId = $installer->get('currentExtensionId');
            } else {
                $extensionId = $installer->currentExtensionId;
            }

            if (!empty($extensionId)) {
                // Get the version we are updating from
                $this->extensionId = $extensionId;

                $this->getPluginDatas();
            }

            $deletes = array();

            $deletes['folder'] = array(
                // Before 3.0.0-rc31
                $pluginPath . '/assets',
                $pluginPath . '/libraries',
                // Since 4.0.0-rc7
                $pluginPath . '/layouts',
            );

            $deletes['file'] = array(
                // Before 3.0.0-rc31
                JPATH_ROOT . '/administrator/language/de-DE/de-DE.plg_content_jtf.ini',
                JPATH_ROOT . '/administrator/language/de-DE/de-DE.plg_content_jtf.sys.ini',
                JPATH_ROOT . '/administrator/language/en-GB/en-GB.plg_content_jtf.ini',
                JPATH_ROOT . '/administrator/language/en-GB/en-GB.plg_content_jtf.sys.ini',
            );

            foreach ($deletes as $key => $orphans) {
                $this->deleteOrphans($key, $orphans);
            }
        }

        sleep(1);

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
     * @since   4.0.0
     */
    public function postflight($action, $installer)
    {
        if ($action === 'update') {
            if (version_compare($this->presentVersion, '4.0.0', 'lt')) {
                $updatePlgConfig = $this->updatePlgConfig();

                if (false === $updatePlgConfig) {
                    Factory::getApplication()
                        ->enqueueMessage(Text::_('PLG_CONTENT_JTF_CFG_NOT_CHANGED'), 'warning');

                    return;
                }
            }
        }
    }

    /**
     * Delete files and folders
     *
     * @param   string  $type     Which type are orphans of (file or folder)
     * @param   array   $orphans  Array of files or folders to delete
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function deleteOrphans($type, array $orphans)
    {
        $app = Factory::getApplication();

        foreach ($orphans as $item) {
            if ($type == 'folder' && (is_dir($item) && Folder::delete($item) === false)) {
                $app->enqueueMessage(Text::sprintf('PLG_CONTENT_JTF_NOT_DELETED', $item), 'warning');
            }

            if ($type == 'file' && (is_file($item) && File::delete($item) === false)) {
                $app->enqueueMessage(Text::sprintf('PLG_CONTENT_JTF_NOT_DELETED', $item), 'warning');
            }
        }
    }

    /**
     * Get the plugin version and params from database
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function getPluginDatas()
    {
        $db     = Factory::getDbo();
        $where  = array(
            $db->quoteName('extension_id') . ' = ' . $db->quote((int) $this->extensionId),
        );
        $select = array(
            $db->quoteName('manifest_cache'),
            $db->quoteName('params'),
        );

        try {
            $result = $db->setQuery(
                $db->getQuery(true)
                    ->select($select)
                    ->from($db->quoteName('#__extensions'))
                    ->where($where)
            )->loadObject();
        } catch (Exception $e) {
            return;
        }

        $manifestCache        = new Registry($result->manifest_cache);
        $this->pluginParams   = new Registry($result->params);
        $this->presentVersion = $manifestCache->get('version');
    }

    /**
     * Update plugin configuration
     *
     * @return  int|boolean  Extension id on success or false
     *
     * @since   4.0.0
     */
    protected function updatePlgConfig()
    {
        if (empty($this->extensionId)) {
            return false;
        }

        $params          = $this->pluginParams;
        $newPluginParams = new Registry($this->pluginDefaultParams);

        if ($params->get('framework') == 'joomla') {
            $params->set('framework', 'bs2');

            if (version_compare(JVERSION, '4', 'ge')) {
                $params->set('framework', 'bs5');
            }
        }

        $captcha = '0';

        if (!empty($params->get('captcha'))) {
            $captcha = '1';
        }

        $params->set('captcha', $captcha);
        $params->remove('component_exclusions');

        $newPluginParams->merge($params);

        $db = Factory::getDbo();

        try {
            $db->setQuery(
                $db->getQuery(true)
                    ->update($db->quoteName('#__extensions'))
                    ->set($db->quoteName('params') . ' = ' . $db->quote($newPluginParams->toString()))
                    ->where(
                        $db->quoteName('extension_id') . '=' . $db->quote((int) $this->extensionId)
                    )
            )->execute();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
