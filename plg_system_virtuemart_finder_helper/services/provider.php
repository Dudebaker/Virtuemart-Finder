<?php
	/**
	 * @package         Virtuemart.Finder
	 * @subpackage      System.virtuemart_finder_helper
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	use Joomla\Plugin\System\VirtuemartFinderHelper\Extension\VirtuemartFinderHelper;
	use Joomla\CMS\Extension\PluginInterface;
	use Joomla\CMS\Factory;
	use Joomla\CMS\Plugin\PluginHelper;
	use Joomla\DI\Container;
	use Joomla\DI\ServiceProviderInterface;
	use Joomla\Event\DispatcherInterface;
	
	defined('_JEXEC') or die;
	
	return new class () implements ServiceProviderInterface
	{
		/**
		 * {@inheritdoc}
		 * @since version
		 */
		public function register(Container $container) : void
		{
			$container->set(
				PluginInterface::class,
				function (Container $container)
				{
					$dispatcher = $container->get(DispatcherInterface::class);
					$plugin     = new VirtuemartFinderHelper($dispatcher, (array) PluginHelper::getPlugin('system', 'virtuemart_finder_helper'));
					
					$plugin->setApplication(Factory::getApplication());
					
					return $plugin;
				}
			);
		}
	};
