<?php
	/**
	 * @package         Virtuemart.Finder
	 * @subpackage      System.virtuemart_finder_helper
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpUnused */
	
	namespace Joomla\Plugin\System\VirtuemartFinderHelper\Extension;
	
	use Joomla\CMS\Language\Text;
	use Joomla\CMS\Plugin\CMSPlugin;
	use Joomla\CMS\Plugin\PluginHelper;
	use Joomla\Event\SubscriberInterface;
	
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
			if ($this->getApplication()->isClient('site'))
			{
				return;
			}
			
			$input  = $this->getApplication()->getInput();
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
			$input = $this->getApplication()->getInput();
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
			
			$messageQueue = $this->getApplication()->getMessageQueue();
			$resultString = $task === 'publish' ? 'COM_VIRTUEMART_STRING_PUBLISHED_SUCCESS' : 'COM_VIRTUEMART_STRING_UNPUBLISHED_SUCCESS';
			$resultText   = Text::sprintf($resultString, Text::_('COM_VIRTUEMART_' . strtoupper($view)));
			
			foreach ($messageQueue as $message)
			{
				if ($message['message'] === $resultText)
				{
					PluginHelper::importPlugin('finder');
					$this->getApplication()->triggerEvent('onFinderChangeState', ['com_virtuemart.' . strtolower($view), $ids, $task === 'publish' ? 1 : 0]);
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
			$input = $this->getApplication()->getInput();
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
			
			$messageQueue = $this->getApplication()->getMessageQueue();
			$resultText   = Text::sprintf('COM_VIRTUEMART_STRING_SAVED', Text::_('COM_VIRTUEMART_MANUFACTURER'));
			
			foreach ($messageQueue as $message)
			{
				if ($message['message'] === $resultText)
				{
					PluginHelper::importPlugin('finder');
					$this->getApplication()->triggerEvent('onFinderAfterSave', ['com_virtuemart.manufacturer', $id, true]);
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
			$input = $this->getApplication()->getInput();
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
			
			$messageQueue = $this->getApplication()->getMessageQueue();
			$resultText   = Text::sprintf('COM_VIRTUEMART_STRING_DELETED', Text::_('COM_VIRTUEMART_MANUFACTURER'));
			
			foreach ($messageQueue as $message)
			{
				if ($message['message'] === $resultText)
				{
					PluginHelper::importPlugin('finder');
					
					foreach ($ids as $id)
					{
						$this->getApplication()->triggerEvent('onFinderAfterDelete', ['com_virtuemart.manufacturer', $id]);
					}
					break;
				}
			}
			
			return true;
		}
		#endregion
	}