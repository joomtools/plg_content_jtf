<?php
/**
 * @package      Joomla.Plugin
 * @subpackage   System.Jtf
 *
 * @author       Guido De Gobbis <support@joomtools.de>
 * @copyright    (c) 2018 JoomTools.de - All rights reserved.
 * @license      GNU General Public License version 3 or later
 */

namespace Jtf\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Jtf\Form\FormFactory;
use Jtf\Form\FormFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the form dependency
 *
 * @since  4.0
 */
class Form implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function register(Container $container)
	{
		$container->alias('form.factory', FormFactoryInterface::class)
//			->alias('Joomla\\CMS\\Form\\FormFactory', FormFactoryInterface::class)
//			->alias('Joomla\\CMS\\Form\\FormFactoryInterface', FormFactoryInterface::class)
			->alias(FormFactory::class, FormFactoryInterface::class)
			->share(
				FormFactoryInterface::class,
				function (Container $container)
				{
					return new FormFactory;
				},
				true
			);
	}
}
