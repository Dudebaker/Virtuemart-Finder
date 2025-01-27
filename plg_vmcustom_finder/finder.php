<?php
	/**
	 * @package         Virtuemart.Finder
	 * @subpackage      VMCustom.finder
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpUnused */
	/** @noinspection PhpMultipleClassDeclarationsInspection */
	
	/** @noinspection AutoloadingIssuesInspection */
	
	use Joomla\CMS\Application\CMSApplicationInterface;
	use Joomla\CMS\Factory;
	use Joomla\CMS\Plugin\PluginHelper;
	use Joomla\CMS\Event\Finder as FinderEvent;
	use Joomla\Event\DispatcherInterface;
	
	defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
	
	class plgVmCustomFinder extends vmCustomPlugin
	{
		protected CMSApplicationInterface $app;
		protected DispatcherInterface $dispatcher;
		protected array $activeLanguages = [];
		
		/**
		 * @throws \Exception
		 */
		public function __construct(&$subject, $config)
		{
			$this->app        = Factory::getApplication();
			$this->dispatcher = $this->app->getDispatcher();
			
			VmConfig::loadConfig();
			
			$this->activeLanguages = (array)VmConfig::get('active_languages', [VmConfig::$jDefLangTag]);
			
			if (empty($this->activeLanguages))
			{
				$this->activeLanguages = [VmConfig::$jDefLangTag];
			}
			
			parent::__construct($subject, $config);
		}
		
		#region Virtuemart Product Events
		public function plgVmAfterStoreProduct($data, $table) : void
		{
			PluginHelper::importPlugin('finder');
			
			foreach ($this->activeLanguages as $activeLanguage)
			{
				$obj     = new stdClass();
				$obj->id = $table->virtuemart_product_id . '_' . $activeLanguage;
				
				$this->dispatcher->dispatch('onFinderAfterSave', new FinderEvent\AfterSaveEvent('onFinderAfterSave', [
					'context' => 'com_virtuemart.product',
					'subject' => $obj,
					'isNew'   => (bool)$data['new'],
				]));
			}
		}
		
		public function plgVmOnDeleteProduct($id) : void
		{
			PluginHelper::importPlugin('finder');
			
			foreach ($this->activeLanguages as $activeLanguage)
			{
				$obj     = new stdClass();
				$obj->id = $id . '_' . $activeLanguage;
				
				$this->dispatcher->dispatch('onFinderAfterDelete', new FinderEvent\AfterDeleteEvent('onFinderAfterDelete', [
					'context' => 'com_virtuemart.product',
					'subject' => $obj
				]));
			}
		}
		#endregion
		
		#region Virtuemart Category Events
		public function plgVmAfterStoreCategory($data, $table) : void
		{
			PluginHelper::importPlugin('finder');
			
			foreach ($this->activeLanguages as $activeLanguage)
			{
				$obj     = new stdClass();
				$obj->id = $table->virtuemart_category_id . '_' . $activeLanguage;
				
				$this->dispatcher->dispatch('onFinderAfterSave', new FinderEvent\AfterSaveEvent('onFinderAfterSave', [
					'context' => 'com_virtuemart.category',
					'subject' => $obj,
					'isNew'   => (bool)$data['new'],
				]));
			}
		}
		
		public function plgVmOnDeleteCategory($id) : void
		{
			PluginHelper::importPlugin('finder');
			
			foreach ($this->activeLanguages as $activeLanguage)
			{
				$obj     = new stdClass();
				$obj->id = $id . '_' . $activeLanguage;
				
				$this->dispatcher->dispatch('onFinderAfterDelete', new FinderEvent\AfterDeleteEvent('onFinderAfterDelete', [
					'context' => 'com_virtuemart.category',
					'subject' => $obj
				]));
			}
		}
		#endregion
		
		#region Virtuemart Manufacturer Events
		public function plgVmAfterStoreManufacturer($data, $table) : void
		{
			// TODO: Event plgVmAfterStoreManufacturer does not exist in Virtuemart Core - realized with plg_system_virtuemart_finder_helper!
			PluginHelper::importPlugin('finder');
			
			foreach ($this->activeLanguages as $activeLanguage)
			{
				$obj     = new stdClass();
				$obj->id = $table->virtuemart_manufacturer_id . '_' . $activeLanguage;
				
				$this->dispatcher->dispatch('onFinderAfterSave', new FinderEvent\AfterSaveEvent('onFinderAfterSave', [
					'context' => 'com_virtuemart.manufacturer',
					'subject' => $obj,
					'isNew'   => (bool)$data['new'],
				]));
			}
		}
		
		public function plgVmOnDeleteManufacturer($id) : void
		{
			// TODO: Event plgVmOnDeleteManufacturer does not exist in Virtuemart Core - realized with plg_system_virtuemart_finder_helper!
			PluginHelper::importPlugin('finder');
			
			foreach ($this->activeLanguages as $activeLanguage)
			{
				$obj     = new stdClass();
				$obj->id = $id . '_' . $activeLanguage;
				
				$this->dispatcher->dispatch('onFinderAfterDelete', new FinderEvent\AfterDeleteEvent('onFinderAfterDelete', [
					'context' => 'com_virtuemart.manufacturer',
					'subject' => $obj
				]));
			}
		}
		#endregion
		
		#region Virtuemart Publish Change Events
		public function plgVmOnPublishChange($ids, $view, $task) : void
		{
			// TODO: Event plgVmOnPublishChange does not exist in Virtuemart Core - realized with plg_system_virtuemart_finder_helper!
			PluginHelper::importPlugin('finder');
			
			foreach ($this->activeLanguages as $activeLanguage)
			{
				$this->dispatcher->dispatch('onFinderChangeState', new FinderEvent\AfterChangeStateEvent('onFinderChangeState', [
					'context' => 'com_virtuemart.' . strtolower($view),
					'subject' => $ids . '_' . $activeLanguage,
					'value'   => $task === 'publish' ? 1 : 0
				]));
			}
		}
		#endregion
	}