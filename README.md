# Virtuemart Finder Package for Joomla Smart-Search

Virtuemart does not have Smart Search plugins for the newer in Joomla integrated Search-Engine.

Here is a complete package which contains all necessary plugins to add Virtuemart products (incl. customfields + Breakdesign CustomFilter!), categories and manufacturers entries to the Finder Index and automatically update it upon changes in Virtuemart.

Since for some actions the Virtuemart-Core does not have any events (publish/unpublish/save,delete manufacturer), these specific events where realized with a system helper plugin.

---

The Finder Virtuemart Products plugin does have some additional settings to use the parent image/category/manufacturer if the child does not have anything assigned

---

---

You can extend what should be indexed by using the plugin "plg_system_virtuemart_finder_extender" as base and add your own code in it. 

You have there access to more or less all values of virtuemart product/category/manufacturer which you already use in the templates.

---

### After the installation you have to enable all plugins!

---

### Tested with Virtuemart 4.4.16 and Joomla 5.1.2

---

Version 1.2.0 - Joomla 5.2.3 Virtuemart 4.4.4

Version 1.1.0 - Joomla 5.1.2 Virtuemart 4.2.16

Version 1.0.1 - Joomla 4
