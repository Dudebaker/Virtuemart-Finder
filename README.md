# Virtuemart Finder Package for Joomla Smart-Search

Virtuemart does not have Smart Search plugins for the newer Joomla integrated Search-Engine.

Here is a complete package which contains all necessary plugins to add Virtuemart products, categories and manufacturers entries to the Finder (aka Smart Search) Index and automatically update it upon changes in Virtuemart.

Since for some actions the Virtuemart-Core does not have any events (publish/unpublish/save,delete manufacturer), these specific events were realized with a system helper plugin.

<br>

[![Download Virtuemart Finder](https://img.shields.io/github/v/release/Dudebaker/Virtuemart-Finder?logo=github&label=Download%20Virtuemart%20Finder&color=blueviolet&style=for-the-badge)](https://github.com/Dudebaker/Virtuemart-Finder/releases/download/v1.2.0/pkg_virtuemart_finder.zip)

<b>Version 1.2.0 was tested with Joomla 5.2.3 and Virtuemart 4.4.4.</b>

<b>After the installation you have to enable all plugins!</b>

---

### Index

<b>The index will be generated for all in Virtuemart enabled, active languages.</b>

<b>Products:</b>
- Name
- Short description
- Long description
- SKU
- MPN
- GTIN
- Metakeys
- Metadescription
- Category Names (all levels)
- Manufacturer Names
- Customfields <b>(incl. Breakdesign CustomFilter!)</b> which have the search parameter enabled

<b>Categories:</b>
- Name
- Description
- Metakeys
- Metadescription

<b>Manufacturers:</b>
- Name
- Description
- Metakeys
- Metadescription

---

### Settings

The Virtuemart Products Finder plugin does have some additional settings:
- ignore products which do not have a category assignment
- use the parent image if the child does not have anything assigned
- use the parent category if the child does not have anything assigned
- use the parent manufacturer if the child does not have anything assigned

For Breakdesign Customfields are two additional settings which are helpful for customfields that represent checkboxes:
- if the "checkbox" has the value "Yes" then only the customfield title will be indexed
- if the "checkbox" has the value "No" then the customfield will be completely ignored
- in the settings you can add your own values which represent "Yes" and "No"

---

### Extend the index

You can extend what should be indexed by using the Virtuemart Finder Extender plugin as base and add your own code in it. 

You have access to more or less all values of virtuemart product/category/manufacturer which you already use in the templates.

[![Download Virtuemart Finder Extender](https://img.shields.io/badge/Download_Virtuemart_Finder_Extender-v1.0.0-blue?style=for-the-badge&logo=github)](https://github.com/Dudebaker/Virtuemart-Finder/releases/download/v1.2.0/plg_system_virtuemart_finder_extender.zip)

---

### Debugging

If you enable Joomla debugging you will get a second option on the Smart Search Index button "Index debugging".<br>
Here you will see anything what got indexed for an specific item.<br>
If you get an error while the indexing is running, you can get here more informations what went wrong (but first you have to find out on which ID the indexing stopped).

- Select the context you want to debug
- Enter the product/category/manufacturer ID
- After the ID add an underscore and then your language you would like to check
- Examples:
  - 1234_de-DE
  - 1234_en-GB

---

### Version compatibility

Version 1.2.0 - Joomla 5.2.3 Virtuemart 4.4.4

Version 1.1.0 - Joomla 5.1.2 Virtuemart 4.2.16

Version 1.0.1 - Joomla 4

---

### ToDo

Shoppergroups are currently not really supported.<br>
All products which have shoppergroup 1 or 2 will be indexed.<br>
Shoppergroups do not have any relation to Joomla usergroups, with these the indexed products could be seperated.<br>
Maybe in the future I will add settings in which you can assign shoppergroups to usergroups.

