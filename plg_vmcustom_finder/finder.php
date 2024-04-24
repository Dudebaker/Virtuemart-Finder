<?php
	/**
	 * @package         Virtuemart.Finder
	 * @subpackage      VMCustom.finder
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpUnused */
	
	use Joomla\CMS\Application\CMSApplicationInterface;
	use Joomla\CMS\Factory;
	use Joomla\CMS\Plugin\PluginHelper;
	
	defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
	
	class plgVmCustomFinder extends vmCustomPlugin
	{
		protected CMSApplicationInterface $app;
		
		public function __construct(&$subject, $config)
		{
			/** @noinspection PhpUnhandledExceptionInspection */
			$this->app = Factory::getApplication();
			
			parent::__construct($subject, $config);
		}
		
		#region Virtuemart Events
		public function plgVmAfterStoreProduct($data, $table) : void
		{
			PluginHelper::importPlugin('finder');
			$this->app->triggerEvent('onFinderAfterSave', ['com_virtuemart.product', $table->virtuemart_product_id, (bool) $data['new']]);
		}
		
		public function plgVmOnDeleteProduct($id) : void
		{
			PluginHelper::importPlugin('finder');
			$this->app->triggerEvent('onFinderAfterDelete', ['com_virtuemart.product', $id]);
		}
		
		public function plgVmAfterStoreCategory($data, $table) : void
		{
			PluginHelper::importPlugin('finder');
			$this->app->triggerEvent('onFinderAfterSave', ['com_virtuemart.category', $table->virtuemart_category_id, (bool) $data['new']]);
		}
		
		public function plgVmOnDeleteCategory($id) : void
		{
			PluginHelper::importPlugin('finder');
			$this->app->triggerEvent('onFinderAfterDelete', ['com_virtuemart.category', $id]);
		}
		
		public function plgVmAfterStoreManufacturer($data, $table) : void
		{
			// TODO: Event plgVmAfterStoreManufacturer does not exist in Virtuemart Core - realized with plg_system_virtuemart_finder_helper!
			PluginHelper::importPlugin('finder');
			$this->app->triggerEvent('onFinderAfterSave', ['com_virtuemart.manufacturer', $table->virtuemart_manufacturer_id, (bool) $data['new']]);
		}
		
		public function plgVmOnDeleteManufacturer($id) : void
		{
			// TODO: Event plgVmOnDeleteManufacturer does not exist in Virtuemart Core - realized with plg_system_virtuemart_finder_helper!
			PluginHelper::importPlugin('finder');
			$this->app->triggerEvent('onFinderAfterDelete', ['com_virtuemart.manufacturer', $id]);
		}
		
		public function plgVmOnPublishChange($ids, $view, $task) : void
		{
			// TODO: Event plgVmOnPublishChange does not exist in Virtuemart Core - realized with plg_system_virtuemart_finder_helper!
			PluginHelper::importPlugin('finder');
			$this->getApplication()->triggerEvent('onFinderChangeState', ['com_virtuemart.' . strtolower($view), $ids, $task === 'publish' ? 1 : 0]);
		}
		#endregion
	}