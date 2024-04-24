<?php
	/**
	 * @package         Virtuemart.Finder
	 * @subpackage      Finder.virtuemart_products
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpMultipleClassDeclarationsInspection */
	
	defined('_JEXEC') or die;
	
	use Joomla\CMS\Extension\PluginInterface;
	use Joomla\CMS\Factory;
	use Joomla\CMS\Plugin\PluginHelper;
	use Joomla\Database\DatabaseInterface;
	use Joomla\DI\Container;
	use Joomla\DI\ServiceProviderInterface;
	use Joomla\Event\DispatcherInterface;
	use Joomla\Plugin\Finder\VirtuemartProducts\Extension\VirtuemartProducts;
	
	return new class () implements ServiceProviderInterface
	{
		/**
		 * Registers the service provider with a DI container.
		 *
		 * @param   Container  $container  The DI container.
		 *
		 * @return  void
		 *
		 * @since   4.3.0
		 */
		public function register(Container $container) : void
		{
			$container->set(
				PluginInterface::class,
				function (Container $container)
				{
					$dispatcher = $container->get(DispatcherInterface::class);
					$plugin     = new VirtuemartProducts(
						$dispatcher,
						(array) PluginHelper::getPlugin('finder', 'virtuemart_products')
					);
					$plugin->setApplication(Factory::getApplication());
					$plugin->setDatabase($container->get(DatabaseInterface::class));
					
					return $plugin;
				}
			);
		}
	};
