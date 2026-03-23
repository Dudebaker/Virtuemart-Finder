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
	use Joomla\Component\Finder\Administrator\Indexer\Result;
	use Joomla\Event\SubscriberInterface;
	
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
			
			switch ($element->context)
			{
				case 'com_virtuemart.product':
					$this->extendFinderContentProduct($element, $element->virtuemart_product);
					break;
				case 'com_virtuemart.category':
					$this->extendFinderContentCategory($element, $element->virtuemart_category);
					break;
				case 'com_virtuemart.manufacturer':
					$this->extendFinderContentManufacturer($element, $element->virtuemart_manufacturer);
					break;
			}
		}
		
		public function extendFinderContentProduct($element, $product) : void
		{
			// Access Virtuemart data
			// More or less all values of virtuemart product are available which you already use in the templates
			
			# $product->virtuemart_product_id;
			# $product->product_name;
			# $product->product_desc;
			
			# $element->setElement('Title', 'value123');
			# $element->addInstruction(Indexer::TITLE_CONTEXT, 'Title');
			# $element->addInstruction(Indexer::TEXT_CONTEXT, 'Title');
			# $element->addInstruction(Indexer::META_CONTEXT, 'Title');
			# $element->addInstruction(Indexer::PATH_CONTEXT, 'Title');
			# $element->addInstruction(Indexer::MISC_CONTEXT, 'Title');
			
			# These are the default weight multipliers:
			#
			# TITLE_CONTEXT => round($data->options->get('title_multiplier', 1.7), 2)
			# TEXT_CONTEXT  => round($data->options->get('text_multiplier', 0.7), 2)
			# META_CONTEXT  => round($data->options->get('meta_multiplier', 1.2), 2)
			# PATH_CONTEXT  => round($data->options->get('path_multiplier', 2.0), 2)
			# MISC_CONTEXT  => round($data->options->get('misc_multiplier', 0.3), 2)
		}
		
		public function extendFinderContentCategory($element, $category) : void
		{
			// Access Virtuemart data
			// More or less all values of virtuemart category are available which you already use in the templates
			
			# $category->virtuemart_category_id;
			# $category->category_name;
			# $category->category_desc;
			
			# $element->setElement('Title', 'value123');
			# $element->addInstruction(Indexer::TITLE_CONTEXT, 'Title');
			# $element->addInstruction(Indexer::TEXT_CONTEXT, 'Title');
			# $element->addInstruction(Indexer::META_CONTEXT, 'Title');
			# $element->addInstruction(Indexer::PATH_CONTEXT, 'Title');
			# $element->addInstruction(Indexer::MISC_CONTEXT, 'Title');
			
			# These are the default weight multipliers:
			#
			# TITLE_CONTEXT => round($data->options->get('title_multiplier', 1.7), 2)
			# TEXT_CONTEXT  => round($data->options->get('text_multiplier', 0.7), 2)
			# META_CONTEXT  => round($data->options->get('meta_multiplier', 1.2), 2)
			# PATH_CONTEXT  => round($data->options->get('path_multiplier', 2.0), 2)
			# MISC_CONTEXT  => round($data->options->get('misc_multiplier', 0.3), 2)
		}
		
		public function extendFinderContentManufacturer($element, $manufacturer) : void
		{
			// Access Virtuemart data
			// More or less all values of virtuemart manufacturer are available which you already use in the templates
			
			# $manufacturer->virtuemart_manufacturer_id;
			# $manufacturer->mf_name;
			# $manufacturer->mf_desc;
			
			# $element->setElement('Title', 'value123');
			# $element->addInstruction(Indexer::TITLE_CONTEXT, 'Title');
			# $element->addInstruction(Indexer::TEXT_CONTEXT, 'Title');
			# $element->addInstruction(Indexer::META_CONTEXT, 'Title');
			# $element->addInstruction(Indexer::PATH_CONTEXT, 'Title');
			# $element->addInstruction(Indexer::MISC_CONTEXT, 'Title');
			
			# These are the default weight multipliers:
			#
			# TITLE_CONTEXT => round($data->options->get('title_multiplier', 1.7), 2)
			# TEXT_CONTEXT  => round($data->options->get('text_multiplier', 0.7), 2)
			# META_CONTEXT  => round($data->options->get('meta_multiplier', 1.2), 2)
			# PATH_CONTEXT  => round($data->options->get('path_multiplier', 2.0), 2)
			# MISC_CONTEXT  => round($data->options->get('misc_multiplier', 0.3), 2)
		}
	}