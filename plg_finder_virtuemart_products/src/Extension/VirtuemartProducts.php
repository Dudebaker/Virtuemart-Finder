<?php
	/**
	 * @package         Virtuemart.Finder
	 * @subpackage      Finder.virtuemart_products
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpUndefinedFieldInspection */
	/** @noinspection PhpMissingReturnTypeInspection */
	/** @noinspection PhpMissingParamTypeInspection */
	/** @noinspection PhpUnusedParameterInspection */
	/** @noinspection PhpUnused */
	/** @noinspection DuplicatedCode */
	/** @noinspection PhpMultipleClassDeclarationsInspection */
	
	namespace Joomla\Plugin\Finder\VirtuemartProducts\Extension;
	
	use DateMalformedStringException;
	use DateTime;
	use Joomla\CMS\Component\ComponentHelper;
	use Joomla\CMS\Factory;
	use Joomla\CMS\Table\Table;
	use Joomla\Component\Finder\Administrator\Indexer\Adapter;
	use Joomla\Component\Finder\Administrator\Indexer\Helper;
	use Joomla\Component\Finder\Administrator\Indexer\Indexer;
	use Joomla\Component\Finder\Administrator\Indexer\Result;
	use Joomla\Database\DatabaseAwareTrait;
	use Joomla\Database\DatabaseQuery;
	use Joomla\Database\QueryInterface;
	use Joomla\Event\DispatcherInterface;
	use Joomla\Plugin\Finder\VirtuemartProducts\Helper\VirtuemartCategoryNode;
	use Joomla\Registry\Registry;
	use Joomla\Utilities\ArrayHelper;
	use VirtueMartModelCustomfields;
	use VirtueMartModelProduct;
	use VmConfig;
	use vmLanguage;
	use VmMediaHandler;
	use VmModel;
	use vmText;
	
	defined('_JEXEC') or die;
	
	/**
	 * Smart Search adapter for com_content.
	 *
	 * @since  2.5
	 */
	final class VirtuemartProducts extends Adapter
	{
		use DatabaseAwareTrait;
		
		#region Properties
		/**
		 * The plugin identifier.
		 *
		 * @var    string
		 *
		 * @since  2.5
		 */
		protected $context = 'Virtuemart Product';
		
		/**
		 * The extension name.
		 *
		 * @var    string
		 *
		 * @since  2.5
		 */
		protected $extension = 'com_virtuemart';
		
		/**
		 * The sublayout to use when rendering the results.
		 *
		 * @var    string
		 *
		 * @since  2.5
		 */
		protected $layout = 'productdetails';
		
		/**
		 * The type of content that the adapter indexes.
		 *
		 * @var    string
		 *
		 * @since  2.5
		 */
		protected $type_title = 'Virtuemart Product';
		
		/**
		 * The table name.
		 *
		 * @var    string
		 *
		 * @since  2.5
		 */
		protected $table = '#__virtuemart_products';
		
		/**
		 * The field the published state is stored in.
		 *
		 * @var    string
		 *
		 * @since  2.5
		 */
		protected $state_field = 'published';
		
		/**
		 * Load the language file on instantiation.
		 *
		 * @var    boolean
		 *
		 * @since  3.1
		 */
		protected $autoloadLanguage = true;
		
		/**
		 * Saves the default virtuemart language
		 *
		 * @var string
		 *
		 * @since 1.2
		 */
		protected static string $defaultLanguage = 'en-GB';
		
		/**
		 * Saves the active virtuemart languages
		 *
		 * @var array
		 *
		 * @since 1.2
		 */
		protected static array $activeLanguages;
		
		/**
		 * Image which should be used, if nothing is assigned
		 *
		 * @var string
		 *
		 * @since 1.2
		 */
		protected static string $noImageUrl;
		
		#endregion
		
		public function __construct(DispatcherInterface $dispatcher, array $config)
		{
			if (!class_exists('VmConfig'))
			{
				require(JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php');
			}
			
			VmConfig::loadConfig();
			vmLanguage::loadJLang('com_virtuemart', true);
			
			self::$defaultLanguage = (string) VmConfig::get('vmDefLang', VmConfig::$jDefLangTag);
			self::setActiveLanguages();
			self::setVirtuemartNoImageUrl();
			
			parent::__construct($dispatcher, $config);
		}
		
		#region Joomla Events
		
		/**
		 * Method to set up the indexer to be run.
		 *
		 * @return  boolean  True on success.
		 *
		 * @since   2.5
		 */
		protected function setup() : bool
		{
			return true;
		}
		
		/**
		 * Method to remove the link information for items that have been deleted.
		 *
		 * @param   string  $context  The context of the action being performed.
		 * @param   Table   $table    A Table object containing the record to be deleted
		 *
		 * @return  void
		 *
		 * @throws  \Exception on database error.
		 *
		 * @since        2.5
		 *
		 * @noinspection PhpMissingParamTypeInspection
		 * @noinspection PhpPossiblePolymorphicInvocationInspection
		 */
		public function onFinderAfterDelete($context, $table) : void
		{
			switch ($context)
			{
				case 'com_virtuemart.product':
					$id = $table->id;
					break;
				case 'com_finder.index':
					$id = $table->link_id;
					break;
				default:
					return;
			}
			
			if (empty(self::$activeLanguages))
			{
				$this->remove($id);
				
				return;
			}
			
			if (str_contains($id, '_'))
			{
				$idWithoutLanguage = explode('_', $id)[0];
			}
			else
			{
				$idWithoutLanguage = $id;
			}
			
			foreach (self::$activeLanguages as $activeLanguage)
			{
				$idWithLanguage = $idWithoutLanguage . '_' . $activeLanguage;
				
				// Remove item from the index.
				$this->remove($idWithLanguage);
			}
		}
		
		/**
		 * Smart Search after save content method.
		 * Reindex the link information for an article that has been saved.
		 * It also makes adjustments if the access level of an item or the
		 * category to which it belongs has changed.
		 *
		 * @param   string   $context  The context of the content passed to the plugin.
		 * @param   Table    $row      A Table object.
		 * @param   boolean  $isNew    True if the content has just been created.
		 *
		 * @return  void
		 *
		 * @throws  \Exception on database error.
		 *
		 * @since        2.5
		 *
		 * @noinspection PhpPossiblePolymorphicInvocationInspection
		 */
		public function onFinderAfterSave($context, $row, $isNew) : void
		{
			if ($context !== 'com_virtuemart.product')
			{
				return;
			}
			
			if (empty(self::$activeLanguages))
			{
				$this->reindex($row->id);
				
				return;
			}
			
			if (str_contains($row->id, '_'))
			{
				$idWithoutLanguage = explode('_', $row->id)[0];
			}
			else
			{
				$idWithoutLanguage = $row->id;
			}
			
			foreach (self::$activeLanguages as $activeLanguage)
			{
				$idWithLanguage = $idWithoutLanguage . '_' . $activeLanguage;
				
				// Remove item from the index.
				$this->reindex($idWithLanguage);
			}
		}
		
		/**
		 * Method to update the link information for items that have been changed
		 * from outside the edit screen. This is fired when the item is published,
		 * unpublished, archived, or unarchived from the list view.
		 *
		 * @param   string   $context  The context for the content passed to the plugin.
		 * @param   array    $pks      An array of primary key ids of the content that has changed state.
		 * @param   integer  $value    The value of the state that the content has been changed to.
		 *
		 * @return  void
		 *
		 * @throws \Exception
		 *
		 * @since   2.5
		 */
		public function onFinderChangeState($context, $pks, $value) : void
		{
			// We only want to handle articles here.
			if ($context === 'com_virtuemart.product')
			{
				$this->itemStateChange($pks, $value);
			}
			
			// Handle when the plugin is disabled.
			if ($context === 'com_plugins.plugin' && $value === 0)
			{
				$this->pluginDisable($pks);
			}
		}
		
		/**
		 * Method to remove outdated index entries
		 *
		 * @return  integer
		 *
		 * @throws \Exception
		 *
		 * @since   4.2.0
		 */
		public function onFinderGarbageCollection() : int
		{
			$db = $this->getDatabase();
			
			/** @noinspection PhpUnhandledExceptionInspection */
			$typeId = $this->getTypeId();
			
			$query    = $db->getQuery(true);
			$subquery = $db->getQuery(true);
			
			$subquery->select($db->quoteName('virtuemart_product_id'))
			         ->from($db->quoteName($this->table))
			         ->where($db->quoteName('published') . ' = 1');
			
			// remove the front-part and the language tag (ex. &lang=en-gb) from the url to only get and compare the ID, comparing the whole string takes ages
			$query->select($db->quoteName('l.link_id'))
			      ->from($db->quoteName('#__finder_links', 'l'))
			      ->where($db->quoteName('l.type_id') . ' = ' . $typeId)
			      ->where('LEFT(REPLACE(' . $db->quoteName('l.url') . ', ' . $db->quote($this->getUrl('', $this->extension, $this->layout)) . ', \'\'), LENGTH(' . $db->quoteName('l.url') . ') -11)' . ' NOT IN (' . $subquery . ')');
			
			$db->setQuery($query);
			
			$items = $db->loadColumn();
			
			foreach ($items as $item)
			{
				/** @noinspection PhpUnhandledExceptionInspection */
				$this->indexer->remove($item);
			}
			
			return count($items);
		}
		#endregion
		
		#region Method overrides
		/**
		 * Method to update index data on published state changes
		 *
		 * @param   array    $pks    A list of primary key ids of the content that has changed state.
		 * @param   integer  $value  The value of the state that the content has been changed to.
		 *
		 * @return  void
		 *
		 * @throws \Exception
		 *
		 * @since   2.5
		 */
		public function itemStateChange($pks, $value) : void
		{
			foreach ($pks as $pk)
			{
				// Update the item.
				$this->change($pk, 'state', $value);
				$this->change($pk, 'published', $value);
			}
		}
		
		/**
		 * Method to change the value of a content item's property in the links
		 * table. This is used to synchronize published and access states that
		 * are changed when not editing an item directly.
		 *
		 * @param   string   $id        The ID of the item to change.
		 * @param   string   $property  The property that is being changed.
		 * @param   integer  $value     The new value of that property.
		 *
		 * @return  boolean  True on success.
		 *
		 * @throws  \Exception on database error.
		 *
		 * @since   2.5
		 */
		public function change($id, $property, $value) : bool
		{
			// Check for a property we know how to handle.
			if ($property !== 'state' && $property !== 'published')
			{
				return true;
			}
			
			if (str_contains($id, '_'))
			{
				$idWithoutLanguage = explode('_', $id)[0];
			}
			else
			{
				$idWithoutLanguage = $id;
			}
			
			$db  = $this->getDatabase();
			$url = $db->quote($this->getUrl($idWithoutLanguage, $this->extension, $this->layout) . '%');
			
			// Check if the content item exists, otherwise index it
			$query = $db->getQuery(true);
			$query->select($db->quoteName('url'))
			      ->from($db->quoteName('#__finder_links'))
			      ->where($db->quoteName('url') . ' LIKE ' . $url);
			
			$db->setQuery($query);
			$existingItems = $db->loadColumn();
			
			if (!is_array($existingItems))
			{
				$existingItems = [$existingItems];
			}
			
			$existingLanguages = array_map(static function ($field)
			{
				return strtolower(substr(strstr($field, '&lang='), strlen('&lang=')));
			}, $existingItems);
			
			foreach (self::$activeLanguages as $activeLanguage)
			{
				if (!in_array(strtolower($activeLanguage), $existingLanguages, true))
				{
					$this->index($this->getItem($idWithoutLanguage . '_' . $activeLanguage));
				}
			}
			
			// Update the content items.
			$query = $db->getQuery(true)
			            ->update($db->quoteName('#__finder_links'))
			            ->set($db->quoteName($property) . ' = ' . (int) $value)
			            ->where($db->quoteName('url') . ' LIKE ' . $url);
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
		
		/**
		 * Method to index an item. The item must be a Result object.
		 *
		 * @param   Result  $item  The item to index as a Result object.
		 *
		 * @return  void
		 *
		 * @throws  \Exception on database error.
		 *
		 * @since   2.5
		 */
		protected function index(Result $item) : void
		{
			$item->setLanguage();
			
			// Check if the extension is enabled.
			if (ComponentHelper::isEnabled($this->extension) === false)
			{
				return;
			}
			
			$item->context = 'com_virtuemart.product';
			
			// Initialise the item parameters.
			$registry     = new Registry($item->params);
			$item->params = clone ComponentHelper::getParams('com_virtuemart', true);
			$item->params->merge($registry);
			
			$item->metadata = new Registry($item->metadata);
			
			if (empty($item->id))
			{
				$this->indexer->index($item);
				
				return;
			}
			
			// Get real Virtuemart product data
			$product = $this->getProductData($item->id, $item->language);
			
			// Create a URL as identifier to recognise items again.
			$item->url = $this->getUrl($item->id, $this->extension, $this->layout, $item->language);
			
			// Build the necessary route and path information.
			$item->route = $this->getRoute($item->id, $this->extension, $this->layout, $product->canonCatId, $item->language);
			
			// Add Virtuemart product data to the item
			$this->setProductData($item, $product);
			$this->setCategoryData($item, $product);
			$this->setManufacturerData($item, $product);
			$this->setCustomfieldsData($item, $product);
			
			// Add whole virtuemart object to access all other variables from triggered plugins
			$item->setElement('virtuemart_product', $product);
			
			// Trigger the onContentPrepare event.
			$item->summary = Helper::prepareContent($item->summary, $item->params, $item);
			$item->body    = Helper::prepareContent($item->body, $item->params, $item);
			
			// Get content extras.
			Helper::getContentExtras($item);
			
			// Remove the virtuemart object, otherwise the serialization fails
			unset($item->virtuemart_product);
			
			// Index the item.
			$this->indexer->index($item);
		}
		
		/**
		 * Method to get the SQL query used to retrieve the list of content items.
		 *
		 * @param   mixed  $query  A DatabaseQuery object or null.
		 *
		 * @return  \Joomla\Database\QueryInterface  A database object.
		 *
		 * @since   2.5
		 */
		protected function getListQuery($query = null)
		{
			return $this->getListQueriesForLanguages($query);
		}
		
		/**
		 * Method to get a SQL query to load the published and access states for the given content.
		 *
		 * @return  \Joomla\Database\QueryInterface  A database object.
		 *
		 * @since   3.1
		 */
		protected function getStateQuery() : QueryInterface
		{
			$db = $this->getDatabase();
			
			$query = $db->getQuery(true);
			$query->select([$db->quoteName('p.virtuemart_product_id', 'id'),
			                $db->quoteName('p.published', 'state'),
			                '1 AS access'])
			      ->from($db->quoteName($this->table, 'p'));
			
			return $query;
		}
		
		/**
		 * Method to get a content item to index.
		 *
		 * @param   integer  $id  The id of the content item.
		 *
		 * @throws  \Exception on database error.
		 *
		 * @since   2.5
		 */
		public function getItem($id)
		{
			if (str_contains($id, '_'))
			{
				[$id, $language] = explode('_', $id);
			}
			
			if (empty($language) || strlen($language) !== 5)
			{
				$language = Factory::getApplication()?->getLanguage()->getTag();
			}
			
			// Get the list query and add the extra WHERE clause.
			$db    = $this->getDatabase();
			$query = $this->getListQuery();
			$query->where('id = ' . (int) $id);
			$query->where('language = ' . $db->quote($language));
			
			// Get the item to index.
			$db->setQuery($query);
			$item = $db->loadAssoc();
			
			// Convert the item to a result object.
			$item = ArrayHelper::toObject((array) $item, Result::class);
			
			// Set the item type.
			$item->type_id = $this->type_id;
			
			// Set the item layout.
			$item->layout = $this->layout;
			
			return $item;
		}
		
		/**
		 * Method to get the URL for the item. The URL is how we look up the link
		 * in the Finder index.
		 *
		 * @param   string  $id         The id of the item.
		 * @param   string  $extension  The extension the category is in.
		 * @param   string  $view       The view for the URL.
		 *
		 * @return  string  The URL of the item.
		 *
		 * @since   2.5
		 */
		public function getUrl($id, $extension, $view, $language = null) : string
		{
			if (str_contains($id, '_'))
			{
				[$id, $language] = explode('_', $id);
			}
			
			$url = "index.php?option=$extension&view=$view&virtuemart_product_id=$id";
			
			if (!empty($id))
			{
				$url .= '&';
				
				if ($language !== null)
				{
					$language = strtolower($language);
					$url      .= "lang=$language";
				}
			}
			
			return $url;
		}
		
		/**
		 * Method to get the URL for the item. The URL is how we look up the link
		 * in the Finder index.
		 *
		 * @param   integer  $id         The id of the item.
		 * @param   string   $extension  The extension the category is in.
		 * @param   string   $view       The view for the URL.
		 *
		 * @return  string  The URL of the item.
		 *
		 * @since   2.5
		 */
		public function getRoute($id, $extension, $view, $categoryId, $language) : string
		{
			if (str_contains($id, '_'))
			{
				[$id, $language] = explode('_', $id);
			}
			
			$language = strtolower($language);
			
			$route = "index.php?option=$extension&view=$view&virtuemart_product_id=$id";
			
			if (!empty($categoryId))
			{
				$route .= "&virtuemart_category_id=$categoryId";
			}
			
			$route .= "&lang=$language";
			
			return $route;
		}
		#endregion
		
		#region Virtuemart List queries
		/**
		 * Method to get a single or combined products query for all active languages
		 *
		 * @param $query
		 *
		 * @return \Joomla\Database\DatabaseQuery
		 *
		 * @since 4.3.0
		 */
		protected function getListQueriesForLanguages($query = null) : DatabaseQuery
		{
			$queries = [];
			
			foreach (self::$activeLanguages as $activeLanguage)
			{
				$queries[] = $this->getListQueryForLanguage($activeLanguage);
			}
			
			return $this->mergeLanguageListQueries($queries, $query);
		}
		
		/**
		 * Method to get a Virtuemart products query for a specific language
		 *
		 * @param   string  $language
		 * @param           $query
		 *
		 * @return \Joomla\Database\DatabaseQuery
		 *
		 * @since 4.3.0
		 */
		protected function getListQueryForLanguage(string $language, $query = null) : DatabaseQuery
		{
			$db = $this->getDatabase();
			
			$query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true);
			
			$query->select([$db->quoteName('p.virtuemart_product_id', 'id'),
			                $db->quote($language) . ' AS language'])
			      ->from($db->quoteName($this->table, 'p'));
			
			return $query;
		}
		
		/**
		 * Method to union language queries and return them as a sub query
		 * A sub-query has to be used if you use union since there will be a clear-select afterward to count the entries and this clear only applies to the first query, not all union queries
		 *
		 * @param   array  $queries
		 * @param          $query
		 *
		 * @return \Joomla\Database\DatabaseQuery
		 *
		 * @since 4.3.0
		 */
		protected function mergeLanguageListQueries(array $queries, $query = null) : DatabaseQuery
		{
			$db    = $this->getDatabase();
			$query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true);
			
			$query->select(['*']);
			
			$queryFrom = $db->getQuery(true);
			
			/** @var $languageQuery DatabaseQuery */
			foreach ($queries as $key => $languageQuery)
			{
				if ($key === 0)
				{
					$queryFrom = $languageQuery;
				}
				else
				{
					$queryFrom->union($languageQuery);
				}
			}
			
			$queryFrom->alias('languages');
			
			$query->from($queryFrom);
			
			return $query;
		}
		#endregion
		
		#region Virtuemart Data
		
		/**
		 * Gets the data for a virtuemart product directly from virtuemart based on the given language
		 * Adds customfields, images, categories and manufacturers
		 *
		 * @param   int     $virtuemartProductId
		 * @param   string  $language
		 *
		 * @return false|\JTable|mixed|object|null
		 *
		 * @since        1.2.0
		 *
		 * @noinspection MissingIssetImplementationInspection
		 */
		protected function getProductData(int $virtuemartProductId, string $language)
		{
			$useManufacturer       = (bool) $this->params->get('use_manufacturers', true);
			$useCategory           = (bool) $this->params->get('use_categories', true);
			$useParentImage        = (bool) $this->params->get('use_parent_image', false);
			$useParentCategory     = $useCategory && $this->params->get('use_parent_category', false);
			$useParentManufacturer = $useManufacturer && $this->params->get('use_parent_manufacturer', false);
			
			$shoppergroups = $this->params->get('shoppergroups');
			
			if (empty($shoppergroups))
			{
				$shoppergroups = [0, 1, 2];
			}
			else
			{
				$tmp = [];
				foreach ($shoppergroups as $entry)
				{
					$tmp[] = (int) $entry->shoppergroup_id;
				}
				$shoppergroups = $tmp;
			}
			
			
			// Changes the currently active backend language to the language which is currently indexed, needed for caches and correct description tables of virtuemart
			vmLanguage::setLanguageByTag($language);
			
			/** @var VirtueMartModelProduct $modelProduct */
			$modelProduct = VmModel::getModel('Product');
			$product      = $modelProduct->getProduct($virtuemartProductId, true, false, false, 1, $shoppergroups);
			
			if (empty($product->product_name) && $language !== self::$defaultLanguage)
			{
				vmLanguage::setLanguageByTag(self::$defaultLanguage);
				$product = $modelProduct->getProduct($virtuemartProductId, true, false, false, 1, $shoppergroups);
				vmLanguage::setLanguageByTag($language);
			}
			
			if (!empty($product->customfields))
			{
				/** @var VirtueMartModelCustomfields $modelCustomfields */
				$modelCustomfields = VmModel::getModel('Customfields');
				$modelCustomfields::displayProductCustomfieldFE($product, $product->customfields);
			}
			
			if (($useParentImage || $useParentCategory || $useParentManufacturer) && !empty($product->product_parent_id))
			{
				$productParent = $modelProduct->getProduct($product->product_parent_id, true, false, false, 1, $shoppergroups);
			}
			
			if ($useParentImage && empty($product->virtuemart_media_id) && !empty($productParent->virtuemart_media_id))
			{
				$product->virtuemart_media_id = $productParent->virtuemart_media_id;
			}
			
			$modelProduct->addImages($product, 1);
			
			if ($useCategory)
			{
				if (empty($product->categories) && $useParentCategory && !empty($productParent->categories))
				{
					$product->categories = $productParent->categories;
				}
				
				if (!empty($product->categories))
				{
					$modelCategory = VmModel::getModel('Category');
					
					foreach ($product->categories as $key => $category)
					{
						$category = $modelCategory->getCategory($category, false);
						
						if (empty($category->category_name) && $language !== self::$defaultLanguage)
						{
							vmLanguage::setLanguageByTag(self::$defaultLanguage);
							$category = $modelCategory->getCategory($category, false);
							vmLanguage::setLanguageByTag($language);
						}
						
						$product->categories[$key] = $category;
					}
				}
			}
			else
			{
				$product->categories = [];
			}
			
			if ($useManufacturer)
			{
				if (empty($product->virtuemart_manufacturer_id) && $useParentManufacturer && !empty($productParent->virtuemart_manufacturer_id))
				{
					$product->virtuemart_manufacturer_id = $productParent->virtuemart_manufacturer_id;
				}
				
				if (!empty($product->virtuemart_manufacturer_id[0]))
				{
					$modelManufacturer = VmModel::getModel('Manufacturer');
					
					$product->manufacturers = [];
					
					foreach ($product->virtuemart_manufacturer_id as $manufacturerId)
					{
						$manufacturer = $modelManufacturer->getManufacturer($manufacturerId);
						
						if (($manufacturer === null || empty($manufacturer->mf_name)) && $language !== self::$defaultLanguage)
						{
							vmLanguage::setLanguageByTag(self::$defaultLanguage);
							$manufacturer = $modelManufacturer->getManufacturer($manufacturerId);
							vmLanguage::setLanguageByTag($language);
						}
						
						$product->manufacturers[] = $manufacturer;
					}
				}
			}
			else
			{
				$product->manufacturers = [];
			}
			
			return $product;
		}
		
		/**
		 * Sets the virtuemart product data to the index-item
		 *
		 * @param $item
		 * @param $product
		 *
		 * @since        1.2.0
		 *
		 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
		 */
		protected function setProductData(&$item, $product) : void
		{
			$item->title      = $product->product_name;
			$item->alias      = $product->slug;
			$item->summary    = $product->product_s_desc;
			$item->body       = $product->product_desc;
			$item->metakey    = $product->metakey;
			$item->metadesc   = $product->metadesc;
			$item->state      = $product->published;
			$item->published  = $product->published;
			$item->access     = 1;
			$item->start_date = $product->created_on;
			$item->metarobot  = $product->metarobot;
			
			if (!empty($product->images) && !in_array(strtolower(trim($product->images[0]->file_url)), ['.jpeg', '.jpg', '.png', '.gif', '.bmp']))
			{
				$item->imageUrl = $product->images[0]->file_url;
				$item->imageAlt = $product->images[0]->file_title;
			}
			
			if (empty($item->imageUrl))
			{
				$item->imageUrl = self::$noImageUrl;
				$item->imageAlt = $item->title;
			}
			
			// Add non-standard fields from Virtuemart product data
			$item->setElement('product_sku', $product->product_sku);
			$item->setElement('product_gtin', $product->product_gtin);
			$item->setElement('product_mpn', $product->product_mpn);
			
			// Add the processing instructions.
			$item->addInstruction(Indexer::TEXT_CONTEXT, 'product_sku');
			$item->addInstruction(Indexer::TEXT_CONTEXT, 'product_gtin');
			$item->addInstruction(Indexer::TEXT_CONTEXT, 'product_mpn');
			$item->addInstruction(Indexer::META_CONTEXT, 'metakey');
			$item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
			
			// Add the type taxonomy data.
			$item->addTaxonomy('Type', 'Virtuemart Product');
			
			// Add the language taxonomy data.
			$item->addTaxonomy('Language', $item->language);
		}
		
		/**
		 * Sets the virtuemart product-category data for the index-item
		 *
		 * @param $item
		 * @param $product
		 *
		 * @since        1.2.0
		 *
		 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
		 */
		protected function setCategoryData(&$item, $product) : void
		{
			if (empty($product->categories))
			{
				return;
			}
			
			$allowProductsWithoutCategory = (bool) $this->params->get('allow_products_without_category_assignment', false);
			
			// Remove unpublished categories.
			$product->categories = array_filter($product->categories, static function ($category)
			{
				return $category->published === 1;
			});
			
			if (empty($product->categories))
			{
				if (!$allowProductsWithoutCategory)
				{
					$item->state     = 0;
					$item->published = 0;
				}
				
				return;
			}
			
			// Sort categories alphanumeric ascending.
			usort($product->categories, static function ($a, $b)
			{
				return $a->category_name <=> $b->category_name;
			});
			
			// Implode all category names and add it to instructions, so the item is findable by all category names.
			$item->setElement('category_names', implode(', ', array_map(static function ($category)
			{
				return $category->category_name;
			}, $product->categories)));
			
			$item->addInstruction(Indexer::META_CONTEXT, 'category_names');
			
			// Add the category taxonomy data.
			$nonPublishedCategoriesCount = 0;
			
			foreach ($product->categories as $category)
			{
				if (empty($category->virtuemart_category_id))
				{
					$nonPublishedCategoriesCount++;
					continue;
				}
				
				$categoryNode = VirtuemartCategoryNode::getCategory($category, $item->language);
				
				if (!$categoryNode->published)
				{
					$nonPublishedCategoriesCount++;
					continue;
				}
				
				$item->addNestedTaxonomy('Virtuemart Category', $categoryNode, $this->translateState($categoryNode->published), $categoryNode->access, $categoryNode->language);
			}
			
			if (!$allowProductsWithoutCategory && $nonPublishedCategoriesCount === count($product->categories))
			{
				$item->state     = 0;
				$item->published = 0;
			}
		}
		
		/**
		 * Sets the virtuemart product-manufacturer data for the index-item
		 *
		 * @param $item
		 * @param $product
		 *
		 * @since        1.2.0
		 *
		 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
		 */
		protected function setManufacturerData(&$item, $product) : void
		{
			if (empty($product->manufacturers))
			{
				return;
			}
			
			// Remove unpublished manufacturers.
			$product->manufacturers = array_filter($product->manufacturers, static function ($manufacturer)
			{
				return $manufacturer->published === 1;
			});
			
			if (empty($product->manufacturers))
			{
				return;
			}
			
			// Sort manufacturers alphanumeric ascending.
			usort($product->manufacturers, static function ($a, $b)
			{
				return $a->mf_name <=> $b->mf_name;
			});
			
			// Implode all manufacturer names and add it to instructions, so the item is findable by all manufacturer names.
			$item->setElement('manufacturer_names', implode(', ', array_map(static function ($manufacturer)
			{
				return $manufacturer->mf_name;
			}, $product->manufacturers)));
			
			$item->addInstruction(Indexer::META_CONTEXT, 'manufacturer_names');
			
			// Add the manufacturer taxonomy data.
			foreach ($product->manufacturers as $manufacturer)
			{
				$item->addTaxonomy('Virtuemart Manufacturer', $manufacturer->mf_name, $manufacturer->published, 1, $item->language);
			}
		}
		
		/**
		 * Sets the virtuemart product-customfields data for the index-item
		 *
		 * @param $item
		 * @param $product
		 *
		 * @since        1.2.0
		 *
		 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
		 */
		protected function setCustomfieldsData(&$item, $product) : void
		{
			$useTaxonomy = $this->params->get('use_customfields_as_taxonomy', true);
			
			foreach ($product->customfields as $customfield)
			{
				if (!$customfield->published)
				{
					continue;
				}
				
				if (!$customfield->searchable)
				{
					continue;
				}
				
				$title = vmText::_($customfield->custom_title);
				$value = null;
				
				switch ($customfield->field_type)
				{
					case 'S':
						if (empty($customfield->customfield_value))
						{
							break;
						}
						
						$value = vmText::_($customfield->customfield_value);
						$item->setElement($title, $value);
						$item->addInstruction(Indexer::META_CONTEXT, $value);
						
						if ($useTaxonomy)
						{
							$item->addTaxonomy($title, $value);
						}
						
						break;
					case 'B':
						if (!$customfield->customfield_value)
						{
							$item->addTaxonomy($title, 'no');
							break;
						}
						
						$item->setElement($title, $title);
						$item->addInstruction(Indexer::META_CONTEXT, $title);
						
						if ($useTaxonomy)
						{
							$item->addTaxonomy($title, 'yes');
						}
						break;
					case 'D':
						if (empty($customfield->customfield_value))
						{
							break;
						}
						
						try
						{
							$value = new DateTime($customfield->customfield_value);
							$value = $value->format('d.m.Y');
							$item->setElement($title, $value);
							$item->addInstruction(Indexer::META_CONTEXT, $title);
							
							if ($useTaxonomy)
							{
								$item->addTaxonomy($title, $value);
							}
						}
						catch (DateMalformedStringException)
						{
						
						}
						
						break;
					case 'E':
						if ($customfield->custom_element !== 'customfieldsforall')
						{
							// only Breakdesign CustomFilters is currently supported
							break;
						}
						
						$this->setBreakDesignCustomfieldData($item, $customfield, $useTaxonomy);
				}
			}
		}
		
		protected static array $yesNoParams = [];
		
		/**
		 * Sets the virtuemart product-customfields Breakdesign CustomFilter data for the index-item
		 *
		 * @param $item
		 * @param $customfield
		 * @param $useTaxonomy
		 *
		 * @since 1.2.0
		 */
		protected function setBreakDesignCustomfieldData($item, $customfield, $useTaxonomy) : void
		{
			if ($customfield->custom_element !== 'customfieldsforall')
			{
				return;
			}
			
			if (empty(self::$yesNoParams))
			{
				$checkboxTextYes = $this->params->get('checkbox_text_yes');
				$checkboxTextNo  = $this->params->get('checkbox_text_no');
				
				if (empty($checkboxTextYes))
				{
					$checkboxTextYes = explode('|', 'yes|oui|sí|ja|是|はい|да|oui|igen|sim|نعم|tak|ano|예|evet|כן|ใช่|haa|igen|igen|aye|yebo|bai|igen');
				}
				else
				{
					$tmp = [];
					foreach ($checkboxTextYes as $entry)
					{
						$tmp[] = $entry->checkbox_text_yes;
					}
					$checkboxTextYes = $tmp;
				}
				
				if (empty($checkboxTextNo))
				{
					$checkboxTextNo = explode('|', 'no|non|no|nein|不是|いいえ|нет|non|nem|não|لا|nie|ne|아니요|hayır|לא|ไม่|hapana|nem|nem|nay|cha|ez|ez');
				}
				else
				{
					$tmp = [];
					foreach ($checkboxTextNo as $entry)
					{
						$tmp[] = $entry->checkbox_text_no;
					}
					$checkboxTextNo = $tmp;
				}
				
				self::$yesNoParams['yes'] = array_map(static function ($element)
				{
					return strtolower(trim($element));
				}, $checkboxTextYes);
				
				self::$yesNoParams['no'] = array_map(static function ($element)
				{
					return strtolower(trim($element));
				}, $checkboxTextNo);
			}
			
			$title = vmText::_($customfield->custom_title);
			
			$values = '';
			foreach ($customfield->values as $customValue)
			{
				if (empty($customValue->customsforall_value_name))
				{
					continue;
				}
				
				// translate the customfield value to the given language
				$customValueTranslated = self::getCustomFieldsForAllLanguageHandlerInstance()->__($customValue, $item->language);
				
				if (empty($customValueTranslated) && $item->language !== self::$defaultLanguage)
				{
					$customValueTranslated = self::getCustomFieldsForAllLanguageHandlerInstance()->__($customValue, self::$defaultLanguage);
				}
				
				if ($useTaxonomy)
				{
					$item->addTaxonomy($title, $customValueTranslated);
				}
				
				$values .= $customValueTranslated . ', ';
			}
			
			if (empty($values))
			{
				return;
			}
			
			$values = substr($values, 0, -2);
			
			if (count($customfield->values) === 1)
			{
				if (in_array(strtolower(trim($values)), self::$yesNoParams['no'], true))
				{
					return;
				}
				
				if (in_array(strtolower(trim($values)), self::$yesNoParams['yes'], true))
				{
					$values = $title;
				}
			}
			
			$item->setElement($title, $values);
			$item->addInstruction(Indexer::META_CONTEXT, $title);
		}
		
		/**
		 * Get and sets the Virtuemart no image url
		 *
		 * @return mixed|string|null
		 *
		 * @since 1.2
		 */
		public static function setVirtuemartNoImageUrl()
		{
			if (empty(self::$noImageUrl))
			{
				$vmMediaHandler = new VmMediaHandler();
				$vmMediaHandler->setNoImageSet();
				self::$noImageUrl = $vmMediaHandler->file_url;
			}
			
			return self::$noImageUrl;
		}
		
		/**
		 * Gets and sets all active Virtuemart languages
		 *
		 * @return array
		 *
		 * @since 1.2.1
		 */
		public static function setActiveLanguages() : array
		{
			if (empty(self::$activeLanguages))
			{
				self::$activeLanguages = (array) VmConfig::get('active_languages', [self::$defaultLanguage]);
				
				if (empty(self::$activeLanguages))
				{
					self::$activeLanguages[] = self::$defaultLanguage;
				}
			}
			
			return self::$activeLanguages;
		}
		#endregion
		
		#region Breakdesign CustomFilters Language
		/**
		 * Returns an instance of LanguageHandler
		 *
		 * @return \Breakdesigns\Plugin\System\Customfieldsforallbase\Model\CustomFieldsForAllLanguageHandler
		 *
		 * @since        1.2
		 *
		 * @noinspection ReturnTypeCanBeDeclaredInspection
		 */
		public static function getCustomFieldsForAllLanguageHandlerInstance()
		{
			static $instancesCustomFieldsForAllLanguageHandler;
			
			if ($instancesCustomFieldsForAllLanguageHandler === null)
			{
				/** @noinspection PhpFullyQualifiedNameUsageInspection */
				$instancesCustomFieldsForAllLanguageHandler = new \Breakdesigns\Plugin\System\Customfieldsforallbase\Model\CustomFieldsForAllLanguageHandler();
			}
			
			return $instancesCustomFieldsForAllLanguageHandler;
		}
		#endregion
	}