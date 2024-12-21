<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.contact
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\WebAsset\WebAssetRegistry;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use JoomTools\Plugin\Content\Jtf\Extension\Jtf;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $app        = Factory::getApplication();

                $plugin = new Jtf(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('content', 'jtf'),
                    $app->getInput()
                );

                $plugin->setApplication($app);
                $plugin->setDatabase($container->get(DatabaseInterface::class));

                $wa = $container->get(WebAssetRegistry::class);
                $wa->addRegistryFile('media/plg_content_jtf/joomla.asset.json');

                return $plugin;
            }
        );
    }
};
