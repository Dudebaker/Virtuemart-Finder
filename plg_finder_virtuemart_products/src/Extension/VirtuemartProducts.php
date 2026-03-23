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
	/** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
	
	namespace Joomla\Plugin\Finder\VirtuemartProducts\Extension;
	
	use Breakdesigns\Plugin\System\Customfieldsforallbase\Model\Customfield;
	use DateMalformedStringException;
	use DateTime;
	use Exception;
	use Joomla\CMS\Application\ConsoleApplication;
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
	use Throwable;
	use VirtueMartModelCategory;
	use VirtueMartModelManufacturer;
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
		
		protected static string $currentLanguage = 'en-GB';
		
		/**
		 * Saves the active virtuemart languages
		 *
		 * @var array
		 *
		 * @since 1.2
		 */
		protected static array $activeLanguages;
		
		/**
		 * Image, which should be used if nothing is assigned
		 *
		 * @var string
		 *
		 * @since 1.2
		 */
		protected static string $noImageUrl;
		
		private static ?Registry $vmParams = null;
		
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
			self::$currentLanguage = self::$defaultLanguage;
			
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
			
			if (empty(self::getActiveLanguages()))
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
			
			foreach (self::getActiveLanguages() as $activeLanguage)
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
			
			if (empty(self::getActiveLanguages()))
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
			
			foreach (self::getActiveLanguages() as $activeLanguage)
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
		 * @throws Throwable
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
			
			$indexVariants = (bool) $this->params->get('index_variants', true);
			
			if (!$indexVariants)
			{
				$subquery->where(sprintf('%s = 0', $db->quoteName('product_parent_id')));
			}
			
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
		 * @throws Throwable
		 *
		 * @since   2.5
		 */
		public function itemStateChange($pks, $value) : void
		{
			foreach ($pks as $pk)
			{
				// Update the item.
				$this->change($pk, 'state', $value);
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
		 * @throws  Throwable
		 *
		 * @since   2.5
		 */
		public function change($id, $property, $value) : bool
		{
			// Check for a property we know how to handle.
			if ($property !== 'state')
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
			
			foreach (self::getActiveLanguages() as $activeLanguage)
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
		 * @throws  Throwable
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
			
			// Initialize the item parameters.
			$registry = new Registry($item->params);
			
			if (self::$vmParams === null)
			{
				self::$vmParams = ComponentHelper::getParams('com_virtuemart', true);
			}
			
			$item->params = clone self::$vmParams;
			
			$item->params->merge($registry);
			
			$item->metadata = new Registry($item->metadata);
			
			// Create a URL as an identifier to recognize items again.
			$item->url = $this->getUrl($item->id, $this->extension, $this->layout, $item->language);
			
			// Build the necessary route and path information.
			$item->route = $this->getRoute($item->id, $this->extension, $this->layout, 0, $item->language);
			
			if (empty($item->id))
			{
				$this->indexer->index($item);
				
				return;
			}
			
			// Get real Virtuemart product data
			self::$searchEquivalents = [];
			$product                 = $this->getProductData($item->id, $item->language);
			
			if (!$product)
			{
				$this->indexer->index($item);
				
				return;
			}
			
			// Add Virtuemart product data to the item
			
			// Build the necessary route and path information including canon category id
			$item->route = $this->getRoute($item->id, $this->extension, $this->layout, $product->canonCatId, $item->language);
			
			$this->setProductData($item, $product);
			$this->setCategoryData($item, $product);
			$this->setManufacturerData($item, $product);
			$this->setCustomfieldsData($item, $product);
			
			// Trigger the onContentPrepare event.
			if (trim($item->summary) === trim($item->body))
			{
				$item->summary = $item->body = $this->prepareContents($item, $item->body);
			}
			else
			{
				$item->summary = $this->prepareContents($item, $item->summary);
				$item->body    = $this->prepareContents($item, $item->body);
			}
			
			// Get content extras.
			if ($this->hasFinderContentListeners())
			{
				// Add a whole virtuemart object to access all other variables from triggered plugins
				$item->setElement('virtuemart_product', $product);
				Helper::getContentExtras($item);
				// Remove the virtuemart object, otherwise the serialization fails and memory increase
				unset($item->virtuemart_product);
			}
			
			// Add search equivalents
			if (!empty(self::$searchEquivalents))
			{
				$item->searchEquivalents = implode(' ', array_keys(self::$searchEquivalents));
				$item->addInstruction(Indexer::META_CONTEXT, 'searchEquivalents');
			}
			
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
			                $db->quoteName('p.published', 'access')])
			      ->from($db->quoteName($this->table, 'p'));
			
			$indexVariants = (bool) $this->params->get('index_variants', true);
			
			if (!$indexVariants)
			{
				$query->where(sprintf('%s = 0', $db->quoteName('p.product_parent_id')));
			}
			
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
			
			if ($language !== null)
			{
				$url .= "&lang=" . strtolower($language);
			}
			
			return $url;
		}
		
		private static ?int $activeLanguageCount = null;
		
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
			
			$route = "index.php?option=$extension&view=$view&virtuemart_product_id=$id";
			
			if (!empty($categoryId))
			{
				$route .= "&virtuemart_category_id=$categoryId";
			}
			
			if (self::$activeLanguageCount === null)
			{
				self::$activeLanguageCount = count((array) VmConfig::get('active_languages', [VmConfig::$jDefLangTag]));
			}
			
			if ($language !== null && self::$activeLanguageCount > 1)
			{
				$language = strtolower($language);
				$route    .= "&lang=$language";
			}
			
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
			
			foreach (self::getActiveLanguages() as $activeLanguage)
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
			
			$indexVariants = (bool) $this->params->get('index_variants', true);
			
			if (!$indexVariants)
			{
				$query->where(sprintf('%s = 0', $db->quoteName('p.product_parent_id')));
			}
			
			//$query->where(sprintf('%s = 45497 or %s = 45497', $db->quoteName('p.product_parent_id'), $db->quoteName('p.virtuemart_product_id')));
			
			
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
		
		#region Handle Parameters
		protected static array $shoppergroupsParams = [];
		
		protected function getShoppergroupsParams() : array
		{
			if (!empty(self::$shoppergroupsParams))
			{
				return self::$shoppergroupsParams;
			}
			
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
			
			self::$shoppergroupsParams = $shoppergroups;
			
			return $shoppergroups;
		}
		
		protected static array $yesNoParams = [];
		
		protected function getYesNoParams() : array
		{
			if (!empty(self::$yesNoParams))
			{
				return self::$yesNoParams;
			}
			
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
			
			return self::$yesNoParams;
		}
		#endregion
		
		#region Virtuemart Data
		private static ?VirtueMartModelProduct $modelProduct = null;
		
		protected function getProductModel() : VirtueMartModelProduct
		{
			if (self::$modelProduct === null)
			{
				self::$modelProduct = VmModel::getModel('Product');
			}
			
			return self::$modelProduct;
		}
		
		private static ?VirtueMartModelCategory $modelCategory = null;
		
		protected function getCategoryModel() : VirtueMartModelCategory
		{
			if (self::$modelCategory === null)
			{
				self::$modelCategory = VmModel::getModel('Category');
			}
			
			return self::$modelCategory;
		}
		
		private static ?VirtueMartModelManufacturer $modelManufacturer = null;
		
		protected function getManufacturerModel() : VirtueMartModelManufacturer
		{
			if (self::$modelManufacturer === null)
			{
				self::$modelManufacturer = VmModel::getModel('Manufacturer');
			}
			
			return self::$modelManufacturer;
		}
		
		protected static array $productsCache = [];
		
		/**
		 * Gets the virtuemart product with a fallback to the default language
		 *
		 * @param   int     $virtuemartProductId
		 * @param   string  $language
		 *
		 * @return mixed
		 *
		 * @since        version
		 *
		 * @noinspection MissingIssetImplementationInspection
		 */
		protected function getProduct(int $virtuemartProductId, string $language) : mixed
		{
			$key = $virtuemartProductId . '_' . $language;
			
			if (isset(self::$productsCache[$key]))
			{
				return self::$productsCache[$key];
			}
			
			$shoppergroups = $this->getShoppergroupsParams();
			
			// Changes the currently active backend language to the language which is currently indexed, needed for caches and correct description tables of virtuemart
			self::setCurrentLanguage($language);
			
			// Must use Frontend = false otherwise CLI would fail since it accesses $app->getMenu
			
			$modelProduct = $this->getProductModel();
			$product      = $modelProduct->getProduct($virtuemartProductId, false, false, false, 1, $shoppergroups);
			
			if (empty($product->product_name) && $language !== self::$defaultLanguage)
			{
				self::setCurrentLanguage(self::$defaultLanguage);
				$product = $modelProduct->getProduct($virtuemartProductId, false, false, false, 1, $shoppergroups);
				self::setCurrentLanguage($language);
			}
			
			if ($product && property_exists($product, 'shoppergroups') && is_array($product->shoppergroups))
			{
				$commonShoppergroups = array_intersect($shoppergroups, $product->shoppergroups);
				if (empty($commonShoppergroups))
				{
					$pr         = $this->fillVoidProduct($virtuemartProductId);
					$pr->slug   = $product->slug;
					$pr->access = false;
					$product    = $pr;
				}
			}
			
			self::$productsCache[$key] = $product;
			
			return $product;
		}
		
		/**
		 * Gets the data for a virtuemart product directly from virtuemart based on the given language
		 * Adds customfields, images, categories, and manufacturers
		 *
		 * @param   int     $virtuemartProductId
		 * @param   string  $language
		 * @param   bool    $withImages
		 *
		 * @return false|\JTable|mixed|object|null
		 *
		 * @since        1.2.0
		 *
		 */
		protected function getProductData(int $virtuemartProductId, string $language, bool $withImages = true)
		{
			$product = $this->getProduct($virtuemartProductId, $language);
			
			if (is_object($product))
			{
				$product = clone $product;
			}
			
			if ($withImages)
			{
				$this->getProductImageData($product, $language);
			}
			
			$this->getProductCategoriesData($product, $language);
			$this->getProductManufacturersData($product, $language);
			$this->getProductCustomfieldsData($product);
			$this->getProductVariantsData($product, $language);
			
			return $product;
		}
		
		protected function getProductImageData(mixed &$product, $language) : void
		{
			$useParentImage = (bool) $this->params->get('use_parent_image', false);
			
			if ($useParentImage && empty($product->virtuemart_media_id) && !empty($product->product_parent_id))
			{
				$productParent                = $this->getProduct($product->product_parent_id, $language);
				$product->virtuemart_media_id = $productParent->virtuemart_media_id;
			}
			
			$this->getProductModel()->addImages($product, 1);
		}
		
		private static array $categoryCache = [];
		
		protected function getProductCategoriesData(mixed &$product, string $language) : void
		{
			$useCategory       = (bool) $this->params->get('use_categories', true);
			$useParentCategory = $useCategory && $this->params->get('use_parent_category', false);
			
			if (!$useCategory)
			{
				$product->categories = [];
				
				return;
			}
			
			if ($useParentCategory && empty($product->categories) && !empty($product->product_parent_id))
			{
				$productParent       = $this->getProduct($product->product_parent_id, $language);
				$product->categories = $productParent->categories;
			}
			
			if (empty($product->categories))
			{
				return;
			}
			
			$modelCategory = $this->getCategoryModel();
			
			foreach ($product->categories as $key => $categoryId)
			{
				$cacheKey = $categoryId . '_' . $language;
				
				if (!isset(self::$categoryCache[$cacheKey]))
				{
					// Must use Frontend = false otherwise CLI would fail since it accesses $app->getMenu
					self::$categoryCache[$cacheKey] = $modelCategory->getCategory($categoryId, false, false);
				}
				
				if (empty(self::$categoryCache[$cacheKey]->category_name) && $language !== self::$defaultLanguage)
				{
					$cacheKey = $categoryId . '_' . self::$defaultLanguage;
					
					if (!isset(self::$categoryCache[$cacheKey]))
					{
						self::setCurrentLanguage(self::$defaultLanguage);
						// Must use Frontend = false otherwise CLI would fail since it accesses $app->getMenu
						self::$categoryCache[$cacheKey] = $modelCategory->getCategory($categoryId, false, false);
						self::setCurrentLanguage($language);
					}
				}
				
				$product->categories[$key] = self::$categoryCache[$cacheKey];
			}
		}
		
		private static array $manufacturerCache = [];
		
		protected function getProductManufacturersData(mixed &$product, string $language) : void
		{
			$useManufacturer       = (bool) $this->params->get('use_manufacturers', true);
			$useParentManufacturer = $useManufacturer && $this->params->get('use_parent_manufacturer', false);
			
			if (!$useManufacturer)
			{
				$product->manufacturers = [];
				
				return;
			}
			
			if ($useParentManufacturer && empty($product->virtuemart_manufacturer_id) && !empty($product->product_parent_id))
			{
				$productParent                       = $this->getProduct($product->product_parent_id, $language);
				$product->virtuemart_manufacturer_id = $productParent->virtuemart_manufacturer_id;
			}
			
			if (empty($product->virtuemart_manufacturer_id[0]))
			{
				return;
			}
			
			$modelManufacturer = $this->getManufacturerModel();
			
			$product->manufacturers = [];
			
			foreach ($product->virtuemart_manufacturer_id as $manufacturerId)
			{
				$cacheKey = $manufacturerId . '_' . $language;
				
				if (!isset(self::$manufacturerCache[$cacheKey]))
				{
					self::$manufacturerCache[$cacheKey] = $modelManufacturer->getManufacturer($manufacturerId);
				}
				
				if ((self::$manufacturerCache[$cacheKey] === null || self::$manufacturerCache[$cacheKey]->mf_name === '') && $language !== self::$defaultLanguage)
				{
					$cacheKey = $manufacturerId . '_' . self::$defaultLanguage;
					
					if (!isset(self::$manufacturerCache[$cacheKey]))
					{
						self::setCurrentLanguage(self::$defaultLanguage);
						self::$manufacturerCache[$cacheKey] = $modelManufacturer->getManufacturer($manufacturerId);
						self::setCurrentLanguage($language);
					}
				}
				
				$product->manufacturers[] = self::$manufacturerCache[$cacheKey];
			}
		}
		
		protected function getProductCustomfieldsData(mixed &$product) : void
		{
			if (!$product || !property_exists($product, 'customfields') || empty($product->customfields))
			{
				return;
			}
			
			// Cannot be used since displayProductCustomfieldFE would (maybe, template relatedly) try to access app->document which does not exist in CLI
			
			///** @var VirtueMartModelCustomfields $modelCustomfields */
			//$modelCustomfields = VmModel::getModel('Customfields');
			//$modelCustomfields::displayProductCustomfieldFE($product, $product->customfields);
			
			// Get required data
			
			foreach ($product->customfields as $key => $customfield)
			{
				if ($customfield->field_type !== 'E')
				{
					// Only plugins need maybe additional data, integrated variants do already have all we need
					continue;
				}
				
				// only Breakdesign CustomFilters is currently supported
				if ($customfield->custom_element !== 'customfieldsforall')
				{
					continue;
				}
				
				$customfieldModel = Customfield::getInstance($customfield->virtuemart_custom_id, $customfield->custom_element);
				
				$customfield->customParams   = $customfieldModel->getCustomfieldParams($customfield->virtuemart_custom_id);
				$customfield->values         = $customfieldModel->getProductCustomValues($customfield->virtuemart_product_id);
				$product->customfields[$key] = $customfield;
			}
		}
		
		private static array $processedVariants = [];
		
		protected function getProductVariantsData(mixed &$product, string $language) : void
		{
			$indexVariants             = (bool) $this->params->get('index_variants', true);
			$addVariantsValuesToParent = (bool) $this->params->get('add_variants_values_to_parent', true);
			
			if ($indexVariants || !$addVariantsValuesToParent || !empty($product->product_parent_id))
			{
				return;
			}
			
			$variants = $this->getProductModel()->getProductChildIds($product->virtuemart_product_id);
			
			$existingCategoryIds = [];
			if ($product->categories !== null)
			{
				foreach ($product->categories as $c)
				{
					$existingCategoryIds[] = $c->virtuemart_category_id;
				}
			}
			
			$existingManufacturerIds = [];
			if ($product->manufacturers !== null)
			{
				foreach ($product->manufacturers as $m)
				{
					$existingManufacturerIds[] = $m->virtuemart_manufacturer_id;
				}
			}
			
			$existingTitles = [];
			if ($product && property_exists($product, 'customfields') && $product->customfields !== null)
			{
				foreach ($product->customfields as $c)
				{
					$existingTitles[] = $c->custom_title;
				}
			}
			
			foreach ($variants as $variant)
			{
				$parentId = $product->virtuemart_product_id;
				
				if (!isset(self::$processedVariants[$parentId]))
				{
					self::$processedVariants[$parentId] = [];
				}
				
				if (isset(self::$processedVariants[$parentId][$variant]))
				{
					continue;
				}
				
				self::$processedVariants[$parentId][$variant] = true;
				
				$variantObj = $this->getProductData((int) $variant, $language, false);
				
				if (!$variantObj->published)
				{
					continue;
				}
				
				$product->product_desc .= ' ' . implode(' ',
				                                        [
					                                        $variantObj->product_name,
					                                        $variantObj->product_s_desc,
					                                        $variantObj->product_desc,
					                                        $variantObj->product_sku,
					                                        $variantObj->product_gtin,
					                                        $variantObj->product_mpn,
				                                        ]);
				
				$product->metakey  .= ' ' . $variantObj->metakey;
				$product->metadesc .= ' ' . $variantObj->metadesc;
				
				if ($variantObj->categories !== null)
				{
					foreach ($variantObj->categories as $vCategory)
					{
						if (!in_array($vCategory->virtuemart_category_id, $existingCategoryIds, true))
						{
							$product->categories[] = $vCategory;
							$existingCategoryIds[] = $vCategory->virtuemart_category_id;
						}
					}
				}
				
				if ($variantObj->manufacturers !== null)
				{
					foreach ($variantObj->manufacturers as $vManufacturer)
					{
						if (!in_array($vManufacturer->virtuemart_manufacturer_id, $existingManufacturerIds, true))
						{
							$product->manufacturers[]  = $vManufacturer;
							$existingManufacturerIds[] = $vManufacturer->virtuemart_manufacturer_id;
						}
					}
				}
				
				if ($variantObj->customfields !== null)
				{
					foreach ($variantObj->customfields as $vCustomfield)
					{
						$titleIndex = array_search($vCustomfield->custom_title, $existingTitles, true);
						
						if ($titleIndex === false)
						{
							$product->customfields[] = $vCustomfield;
							$existingTitles[]        = $vCustomfield->custom_title;
							continue;
						}
						
						if ($product->customfields[$titleIndex]->field_type !== 'E')
						{
							if ($product->customfields[$titleIndex]->custom_value !== $vCustomfield->custom_value)
							{
								$product->customfields[$titleIndex]->custom_value .= ', ' . $vCustomfield->custom_value;
							}
							continue;
						}
						
						// Skip if not Breakdesign CustomFilters
						if ($product->customfields[$titleIndex]->custom_element !== 'customfieldsforall')
						{
							continue;
						}
						
						$existingValueNames = array_column($product->customfields[$titleIndex]->values, 'customsforall_value_name');
						
						foreach ($vCustomfield->values as $customValue)
						{
							if (!in_array($customValue->customsforall_value_name, $existingValueNames, true))
							{
								$product->customfields[$titleIndex]->values[] = $customValue;
								$existingValueNames[]                         = $customValue->customsforall_value_name;
							}
						}
					}
				}
			}
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
			$item->title              = $product->product_name;
			$item->alias              = $product->slug;
			$item->summary            = $product->product_s_desc;
			$item->body               = $product->product_desc;
			$item->metakey            = $product->metakey;
			$item->metadesc           = $product->metadesc;
			$item->state              = $product->published;
			$item->access             = $product->published;
			$item->start_date         = $product->created_on;
			$item->publish_start_date = $product->created_on;
			$item->publish_end_date   = $item->state ? null : $product->modified_on;
			$item->metarobot          = $product->metarobot;
			
			if (!empty($product->images) && !in_array(strtolower(trim($product->images[0]->file_url)), ['.jpeg', '.jpg', '.png', '.gif', '.bmp']))
			{
				$item->imageUrl = $product->images[0]->file_url;
				$item->imageAlt = $product->images[0]->file_title;
			}
			
			if (empty($item->imageUrl))
			{
				$item->imageUrl = self::getVirtuemartNoImageUrl();
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
			
			self::setSearchEquivalents($item, $product->product_name);
			self::setSearchEquivalents($item, $product->product_s_desc);
			self::setSearchEquivalents($item, $product->product_desc);
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
			if (!is_array($product->categories))
			{
				$product->categories = [];
			}
			
			// Remove unpublished categories.
			$product->categories = array_filter($product->categories, static function ($category)
			{
				return $category->published === 1;
			});
			
			$allowProductsWithoutCategory = (bool) $this->params->get('allow_products_without_category_assignment', false);
			
			if (empty($product->categories))
			{
				if (!$allowProductsWithoutCategory)
				{
					$item->state  = 0;
					$item->access = 0;
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
				self::setSearchEquivalents($item, $category->category_name);
				
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
				$item->state  = 0;
				$item->access = 0;
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
				self::setSearchEquivalents($item, $manufacturer->mf_name);
				
				$item->addTaxonomy('Virtuemart Manufacturer', $manufacturer->mf_name, $manufacturer->published, 1, $item->language);
			}
		}
		
		/**
		 * Sets the virtuemart product-customfields data for the index-item
		 *
		 * @param $item
		 * @param $product
		 *
		 * @throws \Exception
		 * @since        1.2.0
		 *
		 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
		 */
		protected function setCustomfieldsData(&$item, $product) : void
		{
			$useTaxonomy = $this->params->get('use_customfields_as_taxonomy', true);
			
			if (!$product || !property_exists($product, 'customfields') || $product->customfields === null)
			{
				return;
			}
			
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
						
						self::setSearchEquivalents($item, $value);
						
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
						
						self::setSearchEquivalents($item, $title);
						
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
		
		/**
		 * Sets the virtuemart product-customfields Breakdesign CustomFilter data for the index-item
		 *
		 * @param $item
		 * @param $customfield
		 * @param $useTaxonomy
		 *
		 * @since 1.2.0
		 */
		protected function setBreakDesignCustomfieldData(&$item, $customfield, $useTaxonomy) : void
		{
			if ($customfield->custom_element !== 'customfieldsforall')
			{
				return;
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
				
				self::setSearchEquivalents($item, $customValueTranslated);
				
				$values .= $customValueTranslated . ', ';
			}
			
			if (empty($values))
			{
				return;
			}
			
			$yesNoParams = $this->getYesNoParams();
			
			$values = substr($values, 0, -2);
			
			if (count($customfield->values) === 1)
			{
				if (in_array(strtolower(trim($values)), $yesNoParams['no'], true))
				{
					return;
				}
				
				if (in_array(strtolower(trim($values)), $yesNoParams['yes'], true))
				{
					$values = $title;
					
					self::setSearchEquivalents($item, $title);
				}
			}
			
			$item->setElement($title, $values);
			$item->addInstruction(Indexer::META_CONTEXT, $title);
		}
		
		protected static array $searchEquivalents = [];
		
		protected static function setSearchEquivalents(&$item, $text) : void
		{
			$text = self::removeHtmlAndStyles($text);
			
			if (strpbrk($text, ':/-+.x') === false)
			{
				return;
			}
			
			// Map of symbols → words
			$map = [
				':' => 'colon',
				'/' => 'slash',
				'-' => 'dash',
				'+' => 'plus',
				'.' => 'dot',
				'x' => 'x',
			];
			
			foreach ($map as $symbol => $word)
			{
				if (!str_contains($text, $symbol))
				{
					continue;
				}
				
				if (!preg_match('/\S+' . preg_quote($symbol, '/') . '\S+/', $text))
				{
					continue;
				}
				
				preg_match_all('/\b(\S+' . preg_quote($symbol, '/') . '\S+)\b/', $text, $matches);
				
				foreach ($matches[1] as $match)
				{
					$converted = str_replace($symbol, $word, $match);
					
					self::$searchEquivalents[$converted] = true;
				}
			}
		}
		
		/**
		 * Get and sets the Virtuemart no image url
		 *
		 * @return mixed|string|null
		 *
		 * @since 1.2
		 */
		public static function getVirtuemartNoImageUrl() : mixed
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
		public static function getActiveLanguages() : array
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
		
		public static function setCurrentLanguage(string $language) : void
		{
			if (self::$currentLanguage !== $language)
			{
				vmLanguage::setLanguageByTag($language);
				self::$currentLanguage = $language;
			}
		}
		
		/** @noinspection PhpMixedReturnTypeCanBeReducedInspection */
		private function fillVoidProduct($productId = 0) : mixed
		{
			$modelProduct = $this->getProductModel();
			
			/* Load an empty product */
			$product = $modelProduct->getTable('products');
			$product->reset();
			$product->load();
			
			$product->virtuemart_product_id = $productId;
			/* Add optional fields */
			$product->virtuemart_manufacturer_id  = null;
			$product->virtuemart_product_price_id = null;
			$product->virtuemart_category_id      = 0;
			$product->allPrices[0]                = $modelProduct->fillVoidPrice();
			$product->categories                  = [];
			$product->canonCatId                  = '';
			$product->allIds                      = [];
			$product->link                        = '';
			$product->virtuemart_shoppergroup_id  = 0;
			$product->mf_name                     = '';
			$product->packaging                   = '';
			$product->related                     = '';
			$product->box                         = '';
			$product->addToCartButton             = false;
			$product->virtuemart_vendor_id        = 1;
			
			return $product;
		}
		
		private static ?bool $isCli = null;
		
		/**
		 * @throws Exception
		 */
		private function isCli() : bool
		{
			if (self::$isCli === null)
			{
				$app         = Factory::getApplication();
				self::$isCli = $app instanceof ConsoleApplication;
			}
			
			return self::$isCli;
		}
		
		/**
		 * @throws Exception|Throwable
		 */
		private function prepareContents(&$item, $content) : ?string
		{
			if (!str_contains($content, '{'))
			{
				return $content;
			}
			if (!preg_match('/\{[a-zA-Z]/', $content))
			{
				return $content;
			}
			
			try
			{
				return Helper::prepareContent($content, $item->params, $item);
			}
			catch (Throwable $e)
			{
				if (!$this->isCli())
				{
					throw $e;
				}
				
				return $content;
			}
		}
		
		private static ?bool $hasFinderContentListeners = null;
		
		/**
		 * @throws Exception
		 */
		private function hasFinderContentListeners() : bool
		{
			if (self::$hasFinderContentListeners === null)
			{
				$dispatcher = Factory::getApplication()->getDispatcher();
				
				self::$hasFinderContentListeners = !empty(
				$dispatcher->getListeners('onPrepareFinderContent')
				);
			}
			
			return self::$hasFinderContentListeners;
		}
		
		private static function removeHtmlAndStyles(?string $html) : string
		{
			if (!$html)
			{
				return '';
			}
			
			$html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html);
			$html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
			
			$text = strip_tags($html);
			
			$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
			
			// Replace non-breaking space (actual UTF-8 char) with normal space
			$text = str_replace("\xC2\xA0", ' ', $text);
			
			// Normalize whitespace
			return trim(preg_replace('/\s+/', ' ', $text));
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