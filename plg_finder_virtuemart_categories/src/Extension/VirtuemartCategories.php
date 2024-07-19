<?php
	/**
	 * @package         Virtuemart.Finder
	 * @subpackage      Finder.virtuemart_categories
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
	
	namespace Joomla\Plugin\Finder\VirtuemartCategories\Extension;
	
	use Joomla\CMS\Component\ComponentHelper;
	use Joomla\CMS\Table\Table;
	use Joomla\Component\Finder\Administrator\Indexer\Adapter;
	use Joomla\Component\Finder\Administrator\Indexer\Helper;
	use Joomla\Component\Finder\Administrator\Indexer\Indexer;
	use Joomla\Component\Finder\Administrator\Indexer\Result;
	use Joomla\Database\DatabaseAwareTrait;
	use Joomla\Database\DatabaseQuery;
	use Joomla\Database\QueryInterface;
	use Joomla\Registry\Registry;
	use Joomla\Utilities\ArrayHelper;
	
	defined('_JEXEC') or die;
	
	/**
	 * Smart Search adapter for com_content.
	 *
	 * @since  2.5
	 */
	final class VirtuemartCategories extends Adapter
	{
		use DatabaseAwareTrait;
		
		#region Properties
		/**
		 * The plugin identifier.
		 *
		 * @var    string
		 * @since  2.5
		 */
		protected $context = 'Virtuemart Category';
		
		/**
		 * The extension name.
		 *
		 * @var    string
		 * @since  2.5
		 */
		protected $extension = 'com_virtuemart';
		
		/**
		 * The sublayout to use when rendering the results.
		 *
		 * @var    string
		 * @since  2.5
		 */
		protected $layout = 'category';
		
		/**
		 * The type of content that the adapter indexes.
		 *
		 * @var    string
		 * @since  2.5
		 */
		protected $type_title = 'Virtuemart Category';
		
		/**
		 * The table name.
		 *
		 * @var    string
		 * @since  2.5
		 */
		protected $table = '#__virtuemart_categories';
		
		/**
		 * The field the published state is stored in.
		 *
		 * @var    string
		 * @since  2.5
		 */
		protected $state_field = 'published';
		
		/**
		 * Load the language file on instantiation.
		 *
		 * @var    boolean
		 * @since  3.1
		 */
		protected $autoloadLanguage = true;
		#endregion
		
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
		 * @since        2.5
		 * @noinspection PhpMissingParamTypeInspection
		 */
		public function onFinderAfterDelete($context, $table) : void
		{
			if ($context === 'com_virtuemart.category')
			{
				/** @noinspection PhpPossiblePolymorphicInvocationInspection */
				$id = $table->id;
			}
			else
			{
				if ($context === 'com_finder.index')
				{
					/** @noinspection PhpUndefinedFieldInspection */
					$id = $table->link_id;
				}
				else
				{
					return;
				}
			}
			
			// Remove item from the index.
			$this->remove($id);
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
		 * @since   2.5
		 */
		public function onFinderAfterSave($context, $row, $isNew) : void
		{
			if ($context === 'com_virtuemart.category')
			{
				/** @noinspection PhpPossiblePolymorphicInvocationInspection */
				$this->reindex($row->id);
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
		 * @since   2.5
		 */
		public function onFinderChangeState($context, $pks, $value) : void
		{
			// We only want to handle articles here.
			if ($context === 'com_virtuemart.category')
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
		 * @since   4.2.0
		 */
		public function onFinderGarbageCollection() : int
		{
			$db = $this->getDatabase();
			
			/** @noinspection PhpUnhandledExceptionInspection */
			$typeId = $this->getTypeId();
			
			$query    = $db->getQuery(true);
			$subquery = $db->getQuery(true);
			
			$subquery->select($db->quoteName('virtuemart_category_id'))
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
		 * @since   2.5
		 */
		public function change($id, $property, $value) : bool
		{
			// Check for a property we know how to handle.
			if ($property !== 'state' && $property !== 'published')
			{
				return true;
			}
			
			$db   = $this->getDatabase();
			$item = $db->quote($this->getUrl($id, $this->extension, $this->layout) . '%');
			
			// Check if the content item exists, otherwise index it
			$query = $db->getQuery(true);
			$query->select($db->quoteName('link_id'))
			      ->from($db->quoteName('#__finder_links'))
			      ->where($db->quoteName('url') . ' LIKE ' . $item);
			
			$db->setQuery($query);
			$existingItem = $db->loadResult();
			
			if (empty($existingItem))
			{
				// Does not exist, index it
				$this->index($this->getItem($id));
			}
			
			// Update the content items.
			$query = $db->getQuery(true)
			            ->update($db->quoteName('#__finder_links'))
			            ->set($db->quoteName($property) . ' = ' . (int) $value)
			            ->where($db->quoteName('url') . ' LIKE ' . $db->quote($this->getUrl($id, $this->extension, $this->layout) . '%'));
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
			
			$item->context = 'com_virtuemart.category';
			
			// Initialise the item parameters.
			$registry     = new Registry($item->params);
			$item->params = clone ComponentHelper::getParams('com_virtuemart', true);
			$item->params->merge($registry);
			
			$item->metadata = new Registry($item->metadata);
			
			// Trigger the onContentPrepare event.
			$item->summary = Helper::prepareContent($item->summary, $item->params, $item);
			$item->body    = Helper::prepareContent($item->body, $item->params, $item);
			
			// Create a URL as identifier to recognise items again.
			$item->url = $this->getUrl($item->id, $this->extension, $this->layout, $item->language);
			// Build the necessary route and path information.
			$item->route = $this->getRoute($item->id, $this->extension, $this->layout, $item->language);
			
			// Add the processing instructions.
			$item->addInstruction(Indexer::META_CONTEXT, 'metakey');
			$item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
			
			$item->published = $item->state;
			$item->access    = 1;
			
			// Add the type taxonomy data.
			$item->addTaxonomy('Type', 'Virtuemart Category');
			
			// Add the language taxonomy data.
			$item->addTaxonomy('Language', $item->language);
			
			// Get content extras.
			Helper::getContentExtras($item);
			
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
			$query->select([$db->quoteName('c.virtuemart_category_id AS id'),
			                $db->quoteName('c.published', 'state'),
			                '1 AS access'])
			      ->from($db->quoteName($this->table, 'c'));
			
			return $query;
		}
		
		/**
		 * Method to get a content item to index.
		 *
		 * @param   integer  $id  The id of the content item.
		 *
		 * @throws  \Exception on database error.
		 * @since   2.5
		 */
		public function getItem($id)
		{
			// Get the list query and add the extra WHERE clause.
			$query = $this->getListQuery();
			$query->where('id = ' . (int) $id);
			
			// Get the item to index.
			$db = $this->getDatabase();
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
		 * @param   integer  $id         The id of the item.
		 * @param   string   $extension  The extension the category is in.
		 * @param   string   $view       The view for the URL.
		 *
		 * @return  string  The URL of the item.
		 *
		 * @since   2.5
		 */
		public function getUrl($id, $extension, $view, $language = null) : string
		{
			$url = "index.php?option=$extension&view=$view&virtuemart_category_id=$id";
			
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
		public function getRoute($id, $extension, $view, $language) : string
		{
			$language = strtolower($language);
			
			return "index.php?option=$extension&view=$view&virtuemart_category_id=$id&lang=$language";
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
			foreach ($this->getActiveVirtuemartLanguages() as $activeLanguage)
			{
				$queries[] = $this->getListQueryForLanguage($activeLanguage);
			}
			
			return $this->mergeLanguageListQueries($queries, $query);
		}
		
		/**
		 * Method to get a Virtuemart category query for a specific language
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
			$defaultLanguage   = $this->getDefaultVirtuemartLanguage();
			$defaultLanguageDb = str_replace('-', '_', strtolower($defaultLanguage));
			$languageDb        = str_replace('-', '_', strtolower($language));
			
			$db = $this->getDatabase();
			
			$queryFirstImage = $db->getQuery(true);
			$queryFirstImage->select(['MIN(ordering) AS min_ordering',
			                          $db->quoteName('virtuemart_category_id')])
			                ->from($db->quoteName('#__virtuemart_category_medias', 'cmo'))
			                ->group($db->quoteName('virtuemart_category_id'))
			                ->alias('cmo');
			
			$query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true);
			
			$query->select([$db->quoteName('c.virtuemart_category_id', 'id'),
			                $db->quoteName('c.published', 'state'),
			                $db->quoteName('c.created_on', 'start_date'),
			                $db->quoteName('c.metarobot'),
			                $db->quoteName('m.file_url', 'imageUrl'),
			                $db->quoteName('m.file_title', 'imageAlt'),
			                $db->quote($language) . ' AS language'])
			      ->from($db->quoteName($this->table, 'c'))
			      ->innerJoin($db->quoteName('#__virtuemart_categories_' . $defaultLanguageDb, 'cl'), 'cl.virtuemart_category_id = c.virtuemart_category_id')
			      ->leftJoin($db->quoteName('#__virtuemart_category_medias', 'cm'), 'cm.virtuemart_category_id = c.virtuemart_category_id')
			      ->leftJoin($queryFirstImage, 'cmo.virtuemart_category_id = c.virtuemart_category_id AND cmo.min_ordering = cm.ordering')
			      ->leftJoin($db->quoteName('#__virtuemart_medias', 'm'), 'm.virtuemart_media_id = cm.virtuemart_media_id')
			      ->where('(' . $db->quoteName('cm.virtuemart_media_id') . ' IS NULL OR (' . $db->quoteName('cmo.min_ordering') . ' IS NOT NULL))');
			
			if ($language !== $defaultLanguage)
			{
				$query->select(['IFNULL(' . $db->quoteName('additional_cl.category_name') . ', ' . $db->quoteName('cl.category_name') . ') AS title',
				                'IFNULL(' . $db->quoteName('additional_cl.slug') . ', ' . $db->quoteName('cl.slug') . ') AS alias',
				                'IFNULL(' . $db->quoteName('additional_cl.category_description') . ', ' . $db->quoteName('cl.category_description') . ') AS summary',
				                'IFNULL(' . $db->quoteName('additional_cl.metakey') . ', ' . $db->quoteName('cl.metakey') . ') AS metakey',
				                'IFNULL(' . $db->quoteName('additional_cl.metadesc') . ', ' . $db->quoteName('cl.metadesc') . ') AS metadesc'])
				      ->leftJoin($db->quoteName('#__virtuemart_categories_' . $languageDb, 'additional_cl'), 'additional_cl.virtuemart_category_id = c.virtuemart_category_id');
			}
			else
			{
				$query->select([$db->quoteName('cl.category_name', 'title'),
				                $db->quoteName('cl.slug', 'alias'),
				                $db->quoteName('cl.category_description', 'summary'),
				                $db->quoteName('cl.metakey'),
				                $db->quoteName('cl.metadesc')]);
			}
			
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
		
		#region Virtuemart Config
		/**
		 * Method to get the Virtuemart config
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		protected function getVirtuemartConfig() : string
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true);
			
			$query->select($db->quoteName('config'));
			$query->from($db->quoteName('#__virtuemart_configs'));
			$query->where($db->quoteName('virtuemart_config_id') . ' = 1');
			$db->setQuery($query);
			
			return $db->loadResult();
		}
		
		/**
		 * Method to get the default language from Virtuemart config
		 *
		 * @return string
		 *
		 * @since 4.3.0
		 */
		protected function getDefaultVirtuemartLanguage() : string
		{
			$config = $this->getVirtuemartConfig();
			
			$defaultLanguage = substr($config, strpos($config, 'vmDefLang="') + strlen('vmDefLang="'));
			$defaultLanguage = substr($defaultLanguage, 0, strpos($defaultLanguage, '"'));
			$defaultLanguage = (string) str_replace(['"'], '', $defaultLanguage);
			
			if (empty($defaultLanguage))
			{
				$defaultLanguage = 'en-GB';
			}
			
			return $defaultLanguage;
		}
		
		/**
		 * Method to get all active languages from Virtuemart config
		 *
		 * @return array
		 *
		 * @since 4.3.0
		 */
		protected function getActiveVirtuemartLanguages() : array
		{
			$config = $this->getVirtuemartConfig();
			
			$activeLanguages = substr($config, strpos($config, 'active_languages=') + strlen('active_languages='));
			$activeLanguages = substr($activeLanguages, 0, strpos($activeLanguages, ']'));
			$activeLanguages = str_replace(['[', ']', '"'], '', $activeLanguages);
			$activeLanguages = explode(',', $activeLanguages);
			
			if (empty($activeLanguages))
			{
				$activeLanguages[] = $this->getDefaultVirtuemartLanguage() ?? 'en-GB';
			}
			
			if (!is_array($activeLanguages))
			{
				$activeLanguages[] = $activeLanguages;
			}
			
			return $activeLanguages;
		}
		#endregion
	}
