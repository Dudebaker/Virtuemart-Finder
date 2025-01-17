# Virtuemart Finder Package for Joomla Smart-Search

Virtuemart does not have Smart Search plugins for the newer Joomla integrated Search-Engine.

Here is a complete package which contains all necessary plugins to add Virtuemart products, categories and manufacturers entries to the Finder Index and automatically update it upon changes in Virtuemart.

Since for some actions the Virtuemart-Core does not have any events (publish/unpublish/save,delete manufacturer), these specific events were realized with a system helper plugin.

Customfields (incl. Breakdesign CustomFilter!) are supported too!

All customfields which have the search parameter enabled are automatically indexed.

---

The Finder Virtuemart Products plugin does have some additional settings:

- ignore products which do not have a category assignment
- use the parent image if the child does not have anything assigned
- use the parent category if the child does not have anything assigned
- use the parent manufacturer if the child does not have anything assigned

For Breakdesign Customfields are two additional settings which are helpful for customfields that represent checkboxes:

- If the "checkbox" has the value "Yes" then only the customfield title will be indexed.
- If the "checkbox" has the value "No" then the customfield will be completely ignored.
- In the settings you can add your own values which represent "Yes" and "No".

---
---

[![Download Virtuemart Finder](https://img.shields.io/github/v/release/Dudebaker/Virtuemart-Finder?logo=github&label=Download%20Virtuemart%20Finder&color=blueviolet&style=for-the-badge)](https://github.com/Dudebaker/Virtuemart-Finder/releases/download/v1.2.0/pkg_virtuemart_finder.zip)

### After the installation you have to enable all plugins!

---
---

You can extend what should be indexed by using the plugin "plg_system_virtuemart_finder_extender" as base and add your own code in it. 

You have access to more or less all values of virtuemart product/category/manufacturer which you already use in the templates.

[![Download Virtuemart Finder Extender](https://img.shields.io/badge/Download_Virtuemart_Finder_Extender-v1.0.0-blue?style=for-the-badge&logo=github)](https://github.com/Dudebaker/Virtuemart-Finder/releases/download/v1.2.0/plg_system_virtuemart_finder_extender.zip)

---
---

### Tested with Virtuemart 4.4.4 and Joomla 5.2.3

---

Version 1.2.0 - Joomla 5.2.3 Virtuemart 4.4.4

Version 1.1.0 - Joomla 5.1.2 Virtuemart 4.2.16

Version 1.0.1 - Joomla 4
