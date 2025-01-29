<?php
	/**
	 * @package         Virtuemart.Finder
	 * @subpackage      Finder.virtuemart_manufacturers
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
	/** @noinspection RedundantSuppression */
	
	namespace Joomla\Plugin\Finder\VirtuemartManufacturers\Extension;
	
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
	use Joomla\Registry\Registry;
	use Joomla\Utilities\ArrayHelper;
	use VirtueMartModelManufacturer;
	use VmConfig;
	use vmLanguage;
	use VmMediaHandler;
	use VmModel;
	
	defined('_JEXEC') or die;
	
	/**
	 * Smart Search adapter for com_content.
	 *
	 * @since  2.5
	 */
	final class VirtuemartManufacturers extends Adapter
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
		protected $context = 'Virtuemart Manufacturer';
		
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
		protected $layout = 'manufacturers';
		
		/**
		 * The type of content that the adapter indexes.
		 *
		 * @var    string
		 *
		 * @since  2.5
		 */
		protected $type_title = 'Virtuemart Manufacturer';
		
		/**
		 * The table name.
		 *
		 * @var    string
		 *
		 * @since  2.5
		 */
		protected $table = '#__virtuemart_manufacturers';
		
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
				case 'com_virtuemart.manufacturer':
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
			if ($context !== 'com_virtuemart.manufacturer')
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
			if ($context === 'com_virtuemart.manufacturer')
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
			
			$subquery->select($db->quoteName('virtuemart_manufacturer_id'))
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
			
			$item->context = 'com_virtuemart.manufacturer';
			
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
			
			// Get real Virtuemart manufacturer data
			$manufacturer = $this->getManufacturerData($item->id, $item->language);
			
			// Create a URL as identifier to recognise items again.
			$item->url = $this->getUrl($item->id, $this->extension, $this->layout, $item->language);
			// Build the necessary route and path information.
			$item->route = $this->getRoute($item->id, $this->extension, $this->layout, $item->language);
			
			// Add Virtuemart category data to the item
			$this->setManufacturerData($item, $manufacturer);
			
			// Add whole virtuemart object to access all other variables from triggered plugins
			$item->setElement('virtuemart_manufacturer', $manufacturer);
			
			// Trigger the onContentPrepare event.
			$item->summary = Helper::prepareContent($item->summary, $item->params, $item);
			$item->body    = Helper::prepareContent($item->body, $item->params, $item);
			
			// Get content extras.
			Helper::getContentExtras($item);
			
			// Remove the virtuemart object, otherwise the serialization fails
			unset($item->virtuemart_manufacturer);
			
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
			$query->select([$db->quoteName('m.virtuemart_manufacturer_id AS id'),
			                $db->quoteName('m.published', 'state'),
			                '1 AS access'])
			      ->from($db->quoteName($this->table, 'm'));
			
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
			
			$url = "index.php?option=$extension&view=$view&virtuemart_manufacturer_id=$id";
			
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
		public function getRoute($id, $extension, $view, $language = null) : string
		{
			if (str_contains($id, '_'))
			{
				[$id, $language] = explode('_', $id);
			}
			
			$route = "index.php?option=$extension&view=category&virtuemart_category_id=0&virtuemart_manufacturer_id=$id";
			
			if ($language !== null && count((array) VmConfig::get('active_languages', [VmConfig::$jDefLangTag])) > 1)
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
			
			foreach (self::$activeLanguages as $activeLanguage)
			{
				$queries[] = $this->getListQueryForLanguage($activeLanguage);
			}
			
			return $this->mergeLanguageListQueries($queries, $query);
		}
		
		/**
		 * Method to get a Virtuemart manufacturer query for a specific language
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
			
			$query->select([$db->quoteName('m.virtuemart_manufacturer_id', 'id'),
			                $db->quote($language) . ' AS language'])
			      ->from($db->quoteName($this->table, 'm'));
			
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
		 * Gets the data for a virtuemart category directly from virtuemart based on the given language
		 *
		 * @param   int     $virtuemartManufacturerId
		 * @param   string  $language
		 *
		 * @return \JTable|object|null
		 *
		 * @since        1.2.0
		 *
		 * @noinspection MissingIssetImplementationInspection
		 */
		protected function getManufacturerData(int $virtuemartManufacturerId, string $language)
		{
			// Changes the currently active backend language to the language which is currently indexed, needed for caches and correct description tables of virtuemart
			vmLanguage::setLanguageByTag($language);
			
			/** @var VirtueMartModelManufacturer $modelManufacturer */
			$modelManufacturer = VmModel::getModel('Manufacturer');
			$manufacturer      = $modelManufacturer->getManufacturer($virtuemartManufacturerId);
			
			if (($manufacturer === null || empty($manufacturer->mf_name)) && $language !== self::$defaultLanguage)
			{
				vmLanguage::setLanguageByTag(self::$defaultLanguage);
				$manufacturer = $modelManufacturer->getManufacturer($virtuemartManufacturerId);
				vmLanguage::setLanguageByTag($language);
			}
			
			$modelManufacturer->addImages($manufacturer, 1);
			
			return $manufacturer;
		}
		
		/**
		 * Sets the virtuemart manufacturer data to the index-item
		 *
		 * @param $item
		 * @param $manufacturer
		 *
		 * @since        1.2.0
		 *
		 * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
		 */
		protected function setManufacturerData(&$item, $manufacturer) : void
		{
			$item->title      = $manufacturer->mf_name;
			$item->alias      = $manufacturer->slug;
			$item->summary    = $manufacturer->mf_desc;
			$item->metakey    = $manufacturer->metakey;
			$item->metadesc   = $manufacturer->metadesc;
			$item->state      = $manufacturer->published;
			$item->published  = $manufacturer->published;
			$item->access     = 1;
			$item->start_date = $manufacturer->created_on;
			$item->metarobot  = $manufacturer->metarobot;
			
			if (!empty($manufacturer->images) && !in_array(strtolower(trim($manufacturer->images[0]->file_url)), ['.jpeg', '.jpg', '.png', '.gif', '.bmp']))
			{
				$item->imageUrl = $manufacturer->images[0]->file_url;
				$item->imageAlt = $manufacturer->images[0]->file_title;
			}
			
			if (empty($item->imageUrl))
			{
				$item->imageUrl = self::$noImageUrl;
				$item->imageAlt = $item->title;
			}
			
			// Add the processing instructions.
			$item->addInstruction(Indexer::META_CONTEXT, 'metakey');
			$item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
			
			// Add the type taxonomy data.
			$item->addTaxonomy('Type', 'Virtuemart Manufacturer');
			
			// Add the language taxonomy data.
			$item->addTaxonomy('Language', $item->language);
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
	}