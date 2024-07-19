<?php
	/**
	 * @package         Virtuemart.Finder
	 * @subpackage      Finder.virtuemart_products
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	/** @noinspection PhpMultipleClassDeclarationsInspection */
	
	namespace Joomla\Plugin\Finder\VirtuemartProducts\Helper;
	
	use Joomla\CMS\Factory;
	use Joomla\CMS\Tree\NodeInterface;
	use Joomla\CMS\Tree\NodeTrait;
	use Joomla\Database\DatabaseInterface;
	
	defined('JPATH_PLATFORM') or die;
	
	/**
	 * Helper class to load Category-tree
	 *
	 * @since  1.6
	 */
	class VirtuemartCategoryNode implements NodeInterface
	{
		use NodeTrait;
		
		/**
		 * Primary key
		 *
		 * @var    integer
		 * @since  1.6
		 */
		public int $id;
		
		/**
		 * The id of the parent of virtuemart-category in the asset table, 0 for virtuemart-category root
		 *
		 * @var    integer
		 * @since  1.6
		 */
		public int $parent_id;
		
		/**
		 * The title for the virtuemart-category
		 *
		 * @var    string
		 * @since  1.6
		 */
		public string $title;
		
		/**
		 * Description of the virtuemart-category.
		 *
		 * @var    string
		 * @since  1.6
		 */
		public string $description;
		
		/**
		 * The publication status of the virtuemart-category
		 *
		 * @var    boolean
		 * @since  1.6
		 */
		public bool $published;
		
		/**
		 * The language for the virtuemart-category in xx-XX format
		 *
		 * @var    string
		 * @since  1.6
		 */
		public string $language;
		
		/**
		 * Slug for the virtuemart-category (used in URL)
		 *
		 * @var    string
		 * @since  1.6
		 */
		public string $slug;
		
		/**
		 * Access level for the virtuemart-category
		 *
		 * @var    integer
		 * @since  1.6
		 */
		public int $access;
		
		/**
		 * Path from root to this virtuemart-category
		 *
		 * @var    array
		 * @since  1.6
		 */
		protected array $_path = [];
		
		/**
		 * Flag if all children have been loaded
		 *
		 * @var    boolean
		 * @since  1.6
		 */
		protected bool $_allChildrenloaded = false;
		
		/**
		 * @param $id
		 * @param $language
		 *
		 *
		 * @return \Joomla\Plugin\Finder\VirtuemartProducts\Helper\VirtuemartCategoryNode
		 * @since version
		 */
		public static function getCategory($id, $language) : VirtuemartCategoryNode
		{
			$languageDb = str_replace('-', '_', strtolower($language));
			
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = $db->getQuery(true);
			$query->select([$db->quoteName('c.virtuemart_category_id'),
			                $db->quoteName('c.published'),
			                $db->quoteName('cl.category_name'),
			                $db->quoteName('cl.slug'),
			                $db->quoteName('cl.category_description'),
			                $db->quoteName('cc.category_parent_id'),
			                $db->quote($language) . ' AS language'])
			      ->from($db->quoteName('#__virtuemart_categories', 'c'))
			      ->innerJoin($db->quoteName('#__virtuemart_categories_' . $languageDb, 'cl'), 'cl.virtuemart_category_id = c.virtuemart_category_id')
			      ->innerJoin($db->quoteName('#__virtuemart_category_categories', 'cc'), 'cc.category_child_id = c.virtuemart_category_id')
			      ->where($db->quoteName('c.virtuemart_category_id') . ' = ' . $db->quote($id));
			
			$result = $db->setQuery($query)->loadObject();
			
			$category              = new self();
			$category->id          = $result->virtuemart_category_id;
			$category->parent_id   = $result->category_parent_id;
			$category->title       = $result->category_name;
			$category->description = $result->category_description;
			$category->published   = $result->published;
			$category->language    = $result->language;
			$category->slug        = $result->slug;
			$category->access      = 1;
			
			if (!empty($category->parent_id))
			{
				$category->setParent(self::getCategory($category->parent_id, $language));
			}
			
			return $category;
		}
		
		
		/**
		 * Set the parent of this virtuemart-category
		 *
		 * If the virtuemart-category already has a parent, the link is unset
		 *
		 * @param   NodeInterface  $parent  VirtuemartCategoryNode for the parent to be set or null
		 *
		 * @return  void
		 *
		 * @since   1.6
		 */
		public function setParent(NodeInterface $parent) : void
		{
			if (!is_null($this->_parent))
			{
				$key = array_search($this, $this->_parent->_children);
				unset($this->_parent->_children[$key]);
			}
			
			$this->_parent = $parent;
			
			$this->_parent->_children[] = &$this;
			
			if (count($this->_parent->_children) > 1)
			{
				end($this->_parent->_children);
				$this->_leftSibling                = prev($this->_parent->_children);
				$this->_leftSibling->_rightsibling = &$this;
			}
			
			if ($this->parent_id !== 0)
			{
				/** @noinspection PhpPossiblePolymorphicInvocationInspection */
				$this->_path = $parent->getPath();
			}
			
			$this->_path[$this->id] = $this->id . ':' . $this->slug;
		}
		
		/**
		 * Get the children of this node
		 *
		 * @param   boolean  $recursive  False by default
		 *
		 * @return  VirtuemartCategoryNode[]  The children
		 *
		 * @since   1.6
		 */
		public function &getChildren($recursive = false) : array
		{
			if (!$this->_allChildrenloaded)
			{
				$temp = self::getCategory($this->id, $this->language);
				
				$this->_children     = $temp->getChildren();
				$this->_leftSibling  = $temp->getSibling(false);
				$this->_rightSibling = $temp->getSibling();
				$this->setAllLoaded();
			}
			
			if ($recursive)
			{
				$items = [];
				
				foreach ($this->_children as $child)
				{
					$items[] = $child;
					/** @noinspection SlowArrayOperationsInLoopInspection */
					$items = array_merge($items, $child->getChildren(true));
				}
				
				return $items;
			}
			
			return $this->_children;
		}
		
		/**
		 * Returns the right or left sibling of a virtuemart-category
		 *
		 * @param   boolean  $right  If set to false, returns the left sibling
		 *
		 * @return  VirtuemartCategoryNode|null  VirtuemartCategoryNode object with the sibling information or null if there is no sibling on that side.
		 *
		 * @since   1.6
		 */
		public function getSibling($right = true) : ?VirtuemartCategoryNode
		{
			if (!$this->_allChildrenloaded)
			{
				$temp                = self::getCategory($this->id, $this->language);
				$this->_children     = $temp->getChildren();
				$this->_leftSibling  = $temp->getSibling(false);
				$this->_rightSibling = $temp->getSibling();
				$this->setAllLoaded();
			}
			
			if ($right)
			{
				return $this->_rightSibling;
			}
			
			return $this->_leftSibling;
		}
		
		/**
		 * Returns the virtuemart-category path to the root virtuemart-category
		 *
		 * @return  array
		 *
		 * @since   1.6
		 */
		public function getPath() : array
		{
			return $this->_path;
		}
		
		/**
		 * Set to load all children
		 *
		 * @return  void
		 *
		 * @since   1.6
		 */
		public function setAllLoaded() : void
		{
			$this->_allChildrenloaded = true;
			
			foreach ($this->_children as $child)
			{
				/** @noinspection PhpPossiblePolymorphicInvocationInspection */
				$child->setAllLoaded();
			}
		}
	}
