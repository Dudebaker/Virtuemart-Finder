<?php
	/**
	 * @package         plg_system_breakdesignsproductbuilder
	 *
	 * @copyright   (C) Open Source Matters, Inc.
	 * @license         GNU General Public License version 2 or later
	 */
	
	defined('_JEXEC') or die;
	
	use Joomla\CMS\Factory;
	use Joomla\CMS\Language\Text;
	use Joomla\CMS\Table\Extension;
	use Joomla\CMS\Version;
	use Joomla\Database\DatabaseInterface;
	
	class pkg_virtuemart_finderInstallerScript
	{
		public const MAX_VERSION_JOOMLA = '6.0.0';
		public const MIN_VERSION_JOOMLA = '5.2.0';
		public const MIN_VERSION_PHP = '8.2.0';
		
		protected string $extensionName = 'Virtuemart Finder Package';
		
		#region Joomla Events
		
		/**
		 * This event is fired before Joomla processes a request, enabling custom pre-processing or validation.
		 *
		 * @param string $type
		 * @param object $parent
		 *
		 * @return bool
		 * @throws Exception
		 */
		public function preflight(string $type, object $parent) : bool
		{
			if ($type === 'uninstall')
			{
				return true;
			}
			
			if (!$this->checkVersionJoomla())
			{
				return false;
			}
			
			if (!$this->checkVersionPhp())
			{
				return false;
			}
			
			return true;
		}
		
		/**
		 * This event is fired after Joomla completes a request, allowing cleanup or post-processing tasks.
		 *
		 * @param string $type
		 * @param object $parent
		 *
		 * @return void
		 * @throws \Exception
		 * @since version
		 */
		public function postflight(string $type, object $parent) : void
		{
			if ($type === 'uninstall')
			{
				return;
			}
			
			$this->enableExtension();
		}
		#endregion
		
		#region Helper function
		
		/**
		 * Checks whether the Joomla! version meets the requirement
		 *
		 * @return bool
		 * @throws Exception
		 * @since version
		 */
		private function checkVersionJoomla() : bool
		{
			$version = new Version();
			
			if (!$version->isCompatible(self::MIN_VERSION_JOOMLA))
			{
				Factory::getApplication()?->enqueueMessage(sprintf('<p><strong>%s - Installation failed!</strong></p><p>Minimum requirements are not met for the execution! The installation process was stopped to avoid errors.</p><p>You need at least <strong>Joomla! version %s</strong> to use this extension! Please update your Joomla! version if you want to use this extension.</p>', $this->extensionName, self::MIN_VERSION_JOOMLA), 'error');
				
				return false;
			}
			
			if (version_compare(JVERSION, self::MAX_VERSION_JOOMLA, 'ge'))
			{
				Factory::getApplication()?->enqueueMessage(sprintf('<p><strong>%s - Installation failed!</strong></p><p>Requirements are not met for the execution! The installation process was stopped to avoid errors.</p><p>The Joomla! version is not supported. The version must be lower than <strong>Joomla! version %s</strong>! Please use a compatible Joomla! version to be able to use this extension.</p>', $this->extensionName, self::MAX_VERSION_JOOMLA), 'error');
				
				return false;
			}
			
			return true;
		}
		
		/**
		 * Checks whether the PHP version meets the requirement
		 *
		 * @return bool
		 * @throws Exception
		 * @since version
		 */
		private function checkVersionPhp() : bool
		{
			if (!version_compare(PHP_VERSION, self::MIN_VERSION_PHP, 'ge'))
			{
				Factory::getApplication()?->enqueueMessage(sprintf('<p><strong>%s - Installation failed!</strong></p><p>Minimum requirements are not met for the execution! The installation process was stopped to avoid errors.</p><p>You need at least <strong>PHP version %s</strong> to use this extension! Please update your PHP version if you want to use this extension.</p>', $this->extensionName, self::MIN_VERSION_PHP), 'error');

				return false;
			}
			
			return true;
		}
		
		private function getPackageElements() : array
		{
			$elements = [];

			$element = new stdClass();
			$element->name = 'virtuemart_finder_helper';
			$element->type = 'plugin';
			$element->folder = 'system';

			$elements[] = $element;
		

			$element = new stdClass();
			$element->name = 'virtuemart_products';
			$element->type = 'plugin';
			$element->folder = 'finder';

			$elements[] = $element;


			$element = new stdClass();
			$element->name = 'virtuemart_categories';
			$element->type = 'plugin';
			$element->folder = 'finder';

			$elements[] = $element;


			$element = new stdClass();
			$element->name = 'virtuemart_manufacturers';
			$element->type = 'plugin';
			$element->folder = 'finder';

			$elements[] = $element;


			$element = new stdClass();
			$element->name = 'finder';
			$element->type = 'plugin';
			$element->folder = 'vmcustom';

			$elements[] = $element;


			return $elements;
		}

		/**
		 * Enables the extension
		 *
		 * @return void
		 * @throws Exception
		 * @since version
		 */
		private function enableExtension() : void
		{
			$db = Factory::getContainer()->get(DatabaseInterface::class);

			$elements = $this->getPackageElements();

			foreach($elements as $element)
			{
				$query = $db->getQuery(true);
				$query->select('extension_id')
				      ->from('#__extensions')
				      ->where($db->quoteName('element') . ' = ' . $db->quote($element->name))
				      ->where($db->quoteName('type') . ' = ' . $db->quote($element->type))
				      ->where($db->quoteName('folder') . ' = ' . $db->quote($element->folder));
				$db->setQuery($query);
				$extensionId = $db->loadResult();
				
				if (empty($extensionId))
				{
					Factory::getApplication()?->enqueueMessage(sprintf('<p><strong>%s - %s.%s.%s - not found!</strong></p><p>Could not find the element in the database! Please reinstall the extension or activate the element on your own.</p>', $this->extensionName, $element->type, $element->folder, $element->name), 'error');
					continue;
				}
				
				$extension = new Extension($db);
				$extension->load($extensionId);
				
				$extension->enabled = 1;
				
				if (!$extension->store())
				{
					Factory::getApplication()?->enqueueMessage(sprintf('<p><strong>%s - %s.%s.%s - Activation failed!</strong></p><p>Please activate the element on your own.</p>', $this->extensionName, $element->type, $element->folder, $element->name), 'error');
				}
			}
		}
		#endregion
	}
