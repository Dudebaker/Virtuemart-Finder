<?php
	/**
	 * @package         Virtuemart.Finder
	 * @subpackage      System.virtuemart_finder_helper
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpUnused */
	/** @noinspection PhpMultipleClassDeclarationsInspection */
	
	namespace Joomla\Plugin\System\VirtuemartFinderHelper\Extension;
	
	use Joomla\CMS\Language\Text;
	use Joomla\CMS\Plugin\CMSPlugin;
	use Joomla\CMS\Plugin\PluginHelper;
	use Joomla\Event\SubscriberInterface;
	use Joomla\CMS\Event\Finder as FinderEvent;
	use stdClass;
	use VmConfig;
	
	defined('_JEXEC') or die;
	
	class VirtuemartFinderHelper extends CMSPlugin implements SubscriberInterface
	{
		#region Joomla Events
		/**
		 * {@inheritdoc}
		 * @since version
		 */
		public static function getSubscribedEvents() : array
		{
			return [
				'application.before_respond' => 'onApplicationBeforeRespond'
			];
		}
		
		/**
		 * The BEFORE_RESPOND event is an event triggered before the application response is sent.
		 *
		 * @since version
		 */
		public function onApplicationBeforeRespond() : void
		{
			$app = $this->getApplication();
			
			if ($app === null)
			{
				return;
			}
			
			if ($app->isClient('site'))
			{
				return;
			}
			
			$input  = $app->getInput();
			$option = $input->getCmd('option');
			
			if ($option !== 'com_virtuemart')
			{
				return;
			}
			
			$this->CheckTaskPublishUnpublish() ||
			$this->CheckTaskManufacturerSave() ||
			$this->CheckTaskManufacturerRemove();
		}
		#endregion
		
		#region Private Methods
		/**
		 * Method to check if the publishing task is active and trigger the onFinderChangeState event
		 * Needed since the Virtuemart-Core does not have an event for publishing changes
		 *
		 * @return bool
		 *
		 * @since version
		 */
		private function CheckTaskPublishUnpublish() : bool
		{
			$app = $this->getApplication();
			
			if ($app === null)
			{
				return false;
			}
			
			$input = $app->getInput();
			$task  = $input->getCmd('task', '');
			
			if (!in_array($task, ['publish', 'unpublish']))
			{
				return false;
			}
			
			$view = $input->getCmd('view', '');
			
			switch ($view)
			{
				case 'product':
					$ids = $input->getInt('virtuemart_product_id');
					break;
				case 'category':
					$ids = $input->getInt('cid');
					break;
				case 'manufacturer':
					$ids = $input->getInt('virtuemart_manufacturer_id');
					break;
				default:
					return false;
			}
			
			if (empty($ids))
			{
				return false;
			}
			
			if(!is_array($ids))
			{
				$ids = [$ids];
			}
			
			$dispatcher   = $app->getDispatcher();
			$messageQueue = $app->getMessageQueue();
			$resultString = $task === 'publish' ? 'COM_VIRTUEMART_STRING_PUBLISHED_SUCCESS' : 'COM_VIRTUEMART_STRING_UNPUBLISHED_SUCCESS';
			$resultText   = Text::sprintf($resultString, Text::_('COM_VIRTUEMART_' . strtoupper($view)));
			
			$activeLanguages = VmConfig::get('active_languages', [VmConfig::$jDefLangTag]);
			
			foreach ($messageQueue as $message)
			{
				if ($message['message'] === $resultText)
				{
					PluginHelper::importPlugin('finder');
					
					foreach ($activeLanguages as $activeLanguage)
					{
						$ids_language = array_map(static function($field) use ($activeLanguage) {
							return $field . '_' . $activeLanguage;
						}, $ids);
						
						$dispatcher->dispatch('onFinderChangeState', new FinderEvent\AfterChangeStateEvent('onFinderChangeState', [
							'context' => 'com_virtuemart.' . strtolower($view),
							'subject' => $ids_language,
							'value'   => $task === 'publish' ? 1 : 0
						]));
					}
					break;
				}
			}
			
			return true;
		}
		
		/**
		 * Method to check if the manufacturer save task is active and trigger the onFinderChangeState event
		 * Needed since the Virtuemart-Core does not have an event for manufacturer-save
		 *
		 * @return bool
		 *
		 * @since version
		 */
		private function CheckTaskManufacturerSave() : bool
		{
			$app = $this->getApplication();
			
			if ($app === null)
			{
				return false;
			}
			
			$input = $app->getInput();
			$view  = $input->getCmd('view', '');
			
			if ($view !== 'manufacturer')
			{
				return false;
			}
			
			$task = $input->getCmd('task', '');
			
			if (!in_array($task, ['save', 'apply']))
			{
				return false;
			}
			
			$id = $input->getInt('virtuemart_manufacturer_id');
			if (is_array($id))
			{
				$id = $id[0];
			}
			
			if (empty($id))
			{
				return false;
			}
			
			$dispatcher   = $app->getDispatcher();
			$messageQueue = $app->getMessageQueue();
			$resultText   = Text::sprintf('COM_VIRTUEMART_STRING_SAVED', Text::_('COM_VIRTUEMART_MANUFACTURER'));
			
			$activeLanguages = VmConfig::get('active_languages', [VmConfig::$jDefLangTag]);
			
			foreach ($messageQueue as $message)
			{
				if ($message['message'] === $resultText)
				{
					PluginHelper::importPlugin('finder');
					
					foreach ($activeLanguages as $activeLanguage)
					{
						$obj     = new stdClass();
						$obj->id = $id . '_' . $activeLanguage;
						
						$dispatcher->dispatch('onFinderAfterSave', new FinderEvent\AfterSaveEvent('onFinderAfterSave', [
							'context' => 'com_virtuemart.manufacturer',
							'subject' => $obj,
							'isNew'   => true,
						]));
					}
					break;
				}
			}
			
			return true;
		}
		
		/**
		 * Method to check if the manufacturer remove task is active and trigger the onFinderChangeState event
		 * Needed since the Virtuemart-Core does not have an event for manufacturer-remove
		 *
		 * @return bool
		 *
		 * @since version
		 */
		private function CheckTaskManufacturerRemove() : bool
		{
			$app = $this->getApplication();
			
			if ($app === null)
			{
				return false;
			}
			
			$input = $app->getInput();
			$view  = $input->getCmd('view', '');
			
			if ($view !== 'manufacturer')
			{
				return false;
			}
			
			$task = $input->getCmd('task', '');
			
			if ($task !== 'remove')
			{
				return false;
			}
			
			$ids = $input->request->getInt('virtuemart_manufacturer_id');
			
			if (empty($ids))
			{
				return false;
			}
			
			if (!is_array($ids))
			{
				$ids = [$ids];
			}
			
			$dispatcher   = $app->getDispatcher();
			$messageQueue = $app->getMessageQueue();
			$resultText   = Text::sprintf('COM_VIRTUEMART_STRING_DELETED', Text::_('COM_VIRTUEMART_MANUFACTURER'));
			
			$activeLanguages = VmConfig::get('active_languages', [VmConfig::$jDefLangTag]);
			
			foreach ($messageQueue as $message)
			{
				if ($message['message'] === $resultText)
				{
					PluginHelper::importPlugin('finder');
					
					foreach ($ids as $id)
					{
						foreach ($activeLanguages as $activeLanguage)
						{
							$obj     = new stdClass();
							$obj->id = $id . '_' . $activeLanguage;
							
							$dispatcher->dispatch('onFinderAfterDelete', new FinderEvent\AfterDeleteEvent('onFinderAfterDelete', [
								'context' => 'com_virtuemart.manufacturer',
								'subject' => $obj
							]));
						}
					}
					break;
				}
			}
			
			return true;
		}
		#endregion
	}