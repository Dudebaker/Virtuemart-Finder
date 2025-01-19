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
	
	namespace Joomla\Plugin\System\VirtuemartFinderExtender\Extension;
	
	use Joomla\CMS\Event\Finder\PrepareContentEvent;
	use Joomla\CMS\Plugin\CMSPlugin;
	use Joomla\Component\Finder\Administrator\Indexer\Indexer;
	use Joomla\Event\SubscriberInterface;
	use Joomla\Component\Finder\Administrator\Indexer\Result;
	
	defined('_JEXEC') or die;
	
	class VirtuemartFinderExtender extends CMSPlugin implements SubscriberInterface
	{
		#region Joomla Events
		/**
		 * {@inheritdoc}
		 * @since version
		 */
		public static function getSubscribedEvents() : array
		{
			return [
				'onPrepareFinderContent' => 'onPrepareFinderContent'
			];
		}
		
		/** @noinspection PhpUndefinedFieldInspection */
		public function onPrepareFinderContent(PrepareContentEvent $event) : void
		{
			$elements = $event->getArguments();
			
			/** @var Result $element */
			$element = current($elements);
			
			// Access Virtuemart data
			// More or less all values of virtuemart product/category/manufacturer are available which you already use in the templates
			switch ($element->context)
			{
				case 'com_virtuemart.product':
					$a = $element->virtuemart_product->virtuemart_product_id;
					$b = $element->virtuemart_product->product_sku;
					$c = $element->virtuemart_product->product_name;
					//...
					break;
				case 'com_virtuemart.category':
					$a = $element->virtuemart_category->virtuemart_category_id;
					$b = $element->virtuemart_category->category_name;
					$c = $element->virtuemart_category->category_desc;
					//...
					break;
				case 'com_virtuemart.manufacturer':
					$a = $element->virtuemart_manufacturer->virtuemart_manufacturer_id;
					$b = $element->virtuemart_manufacturer->mf_name;
					$c = $element->virtuemart_manufacturer->mf_desc;
					//...
					break;
				default:
					return;
			}
			
			// Add additional information to the index-entry as seen below, choose one of the XXX_CONTEXT constants
			
			$element->setElement('Title', 'value123');
			$element->addInstruction(Indexer::TITLE_CONTEXT, 'Title');
			//$element->addInstruction(Indexer::TEXT_CONTEXT, 'Title');
			//$element->addInstruction(Indexer::META_CONTEXT, 'Title');
			//$element->addInstruction(Indexer::PATH_CONTEXT, 'Title');
			//$element->addInstruction(Indexer::MISC_CONTEXT, 'Title');
			
			
			//
			// These are the default weight multipliers:
			//
			//  TITLE_CONTEXT => round($data->options->get('title_multiplier', 1.7), 2)
			//  TEXT_CONTEXT  => round($data->options->get('text_multiplier', 0.7), 2)
			//  META_CONTEXT  => round($data->options->get('meta_multiplier', 1.2), 2)
			//  PATH_CONTEXT  => round($data->options->get('path_multiplier', 2.0), 2)
			//  MISC_CONTEXT  => round($data->options->get('misc_multiplier', 0.3), 2)
			//
		}
	}