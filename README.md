# Virtuemart Finder Package for Joomla Smart-Search

Virtuemart does not have Smart Search plugins for the newer Joomla integrated Search-Engine.

Here is a complete package which contains all necessary plugins to add Virtuemart products, categories and manufacturers entries to the Finder (aka Smart Search) Index and automatically update it upon changes in Virtuemart.

Since for some actions the Virtuemart-Core does not have any events (publish/unpublish/save,delete manufacturer), these specific events were realized with a system helper plugin.

<br>

[![Download Virtuemart Finder](https://img.shields.io/github/v/release/Dudebaker/Virtuemart-Finder?logo=github&label=Download%20Virtuemart%20Finder&color=blueviolet&style=for-the-badge)](https://github.com/Dudebaker/Virtuemart-Finder/releases/download/v1.2.2/pkg_virtuemart_finder.zip)

<b>After the installation you have to enable all plugins!</b>

<b>Tested with:</b><br> 
[![](https://img.shields.io/badge/Joomla-v5.1.2-2E5C6B?logo=joomla&logoColor=white&style=for-the-badge)](https://downloads.joomla.org/)
[![](https://img.shields.io/badge/Joomla-v5.2.3-2E5C6B?logo=joomla&logoColor=white&style=for-the-badge)](https://downloads.joomla.org/)<br>
[![](https://img.shields.io/badge/Virtuemart-v4.2.18-00A1DF?style=for-the-badge&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDEuOSAxNTIuMSIgaGVpZ2h0PSIxMDBweCIgd2lkdGg9IjEwMHB4IiB0cmFuc2Zvcm09InJvdGF0ZSgwKSBzY2FsZSgxLCAxKSI+CjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+LnN0MCB7IGZpbGw6ICNmZmZmZmY7IH0uc3QxIHsgZmlsbDogI2ZmZmZmZjsgfTwvc3R5bGU+CjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik04OS45LDYyLjFjMC41LTAuNSwxLjEtMS4xLDEuNy0xLjdjMjctMjYuOSw2My00NS45LDExMC4zLTU2LjJMMjAwLjMsMEM4NywxOSw0OS45LDkxLDM3LjYsMTIxLjIgIGMtMS44LTQuNy0xNy4yLTQ3LjktMjAuMy01Ni4xSDBsMzIuOSw4N2g4YzEuMy0zLjgsMTItMzkuMSwzMC42LTY1LjhjMC43LTEuMSwxLjQtMi4yLDIuMi0zLjRjMC44LTEsMS42LTIuMSwyLjMtMy4xICBjMC4yLTAuMiwxLTEuNCwxLTEuNHMwLjctMSwxLTEuNWMwLjUtMC44LDEtMS40LDEuMy0xLjljMC45LTQuMywxLjktOC42LDIuOC0xMi45Qzg0LjcsNjIuMSw4Ny4zLDYyLjEsODkuOSw2Mi4xeiIvPgo8cG9seWdvbiBjbGFzcz0ic3QxIiBwb2ludHM9IjEyOC45LDY1LjEgMTEwLjUsMTIwLjYgOTEuOSw2NS4xIDkxLjgsNjUuMSA4NC4zLDY1LjEgODEuOSw3Ni45IDY3LjcsMTQ5LjIgODEuNywxNDkuMiA5MS4zLDEwNS4zICAgMTA3LjksMTUxLjIgMTEzLDE1MS4yIDEyOS41LDEwNS4zIDEzOS4xLDE0OS4zIDE1My4xLDE0OS4zIDEzNi42LDY1LjEgIi8+Cjwvc3ZnPg==)](https://virtuemart.net/download)
[![](https://img.shields.io/badge/Virtuemart-v4.4.4-00A1DF?style=for-the-badge&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDEuOSAxNTIuMSIgaGVpZ2h0PSIxMDBweCIgd2lkdGg9IjEwMHB4IiB0cmFuc2Zvcm09InJvdGF0ZSgwKSBzY2FsZSgxLCAxKSI+CjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+LnN0MCB7IGZpbGw6ICNmZmZmZmY7IH0uc3QxIHsgZmlsbDogI2ZmZmZmZjsgfTwvc3R5bGU+CjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik04OS45LDYyLjFjMC41LTAuNSwxLjEtMS4xLDEuNy0xLjdjMjctMjYuOSw2My00NS45LDExMC4zLTU2LjJMMjAwLjMsMEM4NywxOSw0OS45LDkxLDM3LjYsMTIxLjIgIGMtMS44LTQuNy0xNy4yLTQ3LjktMjAuMy01Ni4xSDBsMzIuOSw4N2g4YzEuMy0zLjgsMTItMzkuMSwzMC42LTY1LjhjMC43LTEuMSwxLjQtMi4yLDIuMi0zLjRjMC44LTEsMS42LTIuMSwyLjMtMy4xICBjMC4yLTAuMiwxLTEuNCwxLTEuNHMwLjctMSwxLTEuNWMwLjUtMC44LDEtMS40LDEuMy0xLjljMC45LTQuMywxLjktOC42LDIuOC0xMi45Qzg0LjcsNjIuMSw4Ny4zLDYyLjEsODkuOSw2Mi4xeiIvPgo8cG9seWdvbiBjbGFzcz0ic3QxIiBwb2ludHM9IjEyOC45LDY1LjEgMTEwLjUsMTIwLjYgOTEuOSw2NS4xIDkxLjgsNjUuMSA4NC4zLDY1LjEgODEuOSw3Ni45IDY3LjcsMTQ5LjIgODEuNywxNDkuMiA5MS4zLDEwNS4zICAgMTA3LjksMTUxLjIgMTEzLDE1MS4yIDEyOS41LDEwNS4zIDEzOS4xLDE0OS4zIDE1My4xLDE0OS4zIDEzNi42LDY1LjEgIi8+Cjwvc3ZnPg==)](https://virtuemart.net/download)<br>
[![](https://img.shields.io/badge/PHP-v8.1-777BB3?logo=php&logoColor=white&style=for-the-badge)]([https://downloads.joomla.org/](https://www.php.net/downloads.php))
[![](https://img.shields.io/badge/PHP-v8.2-777BB3?logo=php&logoColor=white&style=for-the-badge)]([https://downloads.joomla.org/](https://www.php.net/downloads.php))
[![](https://img.shields.io/badge/PHP-v8.3-777BB3?logo=php&logoColor=white&style=for-the-badge)]([https://downloads.joomla.org/](https://www.php.net/downloads.php))

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
- Image (only the first)
- Category Names (all levels)
- Manufacturer Names
- Customfields <b>(incl. Breakdesign CustomFilter!)</b> which have the search parameter enabled

<b>Categories:</b>
- Name
- Description
- Metakeys
- Metadescription
- Image

<b>Manufacturers:</b>
- Name
- Description
- Metakeys
- Metadescription
- Image

---

### Settings

The Virtuemart Products Finder plugin does have some additional settings:
- shoppergroups to index
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

[![Download Virtuemart Finder Extender](https://img.shields.io/badge/Download_Virtuemart_Finder_Extender-v1.0.0-blue?style=for-the-badge&logo=github)](https://github.com/Dudebaker/Virtuemart-Finder/releases/download/v1.2.1/plg_system_virtuemart_finder_extender.zip)

---

### Debugging

If you enable Joomla debugging you will get a second option on the Smart Search Index button "Index debugging".<br>
Here you will see anything what got indexed for an specific item.<br>
If you get an error while the indexing is running, you can get here more informations what went wrong.<br>
To find out which ID stopped the indexing you can enable logging in the Smart Search configuration and get a protocol in your logs folder.

- Select the context you want to debug
- Enter the product/category/manufacturer ID
- After the ID add an underscore and then your language you would like to check
- Examples:
  - 1234_de-DE
  - 1234_en-GB

---

### ToDo

Seperat indexes based on shoppergroups are currently not supported.<br>
Shoppergroups do not have any relation to Joomla usergroups, with these the indexed products could be seperated.<br>
Maybe in the future I will add settings in which you can assign shoppergroups to usergroups.<br>
Currently there is only a setting for telling which shoppergroups should be indexed.
