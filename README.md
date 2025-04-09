# Virtuemart Finder Package for Joomla Smart-Search

Virtuemart does not have Smart Search plugins for the newer Joomla integrated Search-Engine.

Here is a complete package which contains all necessary plugins to add Virtuemart products, categories and manufacturers entries to the Finder (aka Smart Search) Index and automatically update it upon changes in Virtuemart.

Since for some actions the Virtuemart-Core does not have any events (publish/unpublish/save,delete manufacturer), these specific events were realized with a system helper plugin.

<br>

[![Download Virtuemart Finder](https://img.shields.io/github/v/release/Dudebaker/Virtuemart-Finder?logo=github&label=Download%20Virtuemart%20Finder&color=blueviolet&style=for-the-badge)](https://github.com/Dudebaker/Virtuemart-Finder/releases/download/v1.2.4/pkg_virtuemart_finder.zip)

<b>After the installation you have to enable all plugins!</b>

<b>Tested with:</b><br> 
[![](https://img.shields.io/badge/Joomla-v5.2.5-2E5C6B?logo=joomla&logoColor=white&style=for-the-badge)](https://downloads.joomla.org/)<br>
[![](https://img.shields.io/badge/Virtuemart-v4.2.18-00A1DF?style=for-the-badge&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDEuOSAxNTIuMSIgaGVpZ2h0PSIxMDBweCIgd2lkdGg9IjEwMHB4IiB0cmFuc2Zvcm09InJvdGF0ZSgwKSBzY2FsZSgxLCAxKSI+CjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+LnN0MCB7IGZpbGw6ICNmZmZmZmY7IH0uc3QxIHsgZmlsbDogI2ZmZmZmZjsgfTwvc3R5bGU+CjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik04OS45LDYyLjFjMC41LTAuNSwxLjEtMS4xLDEuNy0xLjdjMjctMjYuOSw2My00NS45LDExMC4zLTU2LjJMMjAwLjMsMEM4NywxOSw0OS45LDkxLDM3LjYsMTIxLjIgIGMtMS44LTQuNy0xNy4yLTQ3LjktMjAuMy01Ni4xSDBsMzIuOSw4N2g4YzEuMy0zLjgsMTItMzkuMSwzMC42LTY1LjhjMC43LTEuMSwxLjQtMi4yLDIuMi0zLjRjMC44LTEsMS42LTIuMSwyLjMtMy4xICBjMC4yLTAuMiwxLTEuNCwxLTEuNHMwLjctMSwxLTEuNWMwLjUtMC44LDEtMS40LDEuMy0xLjljMC45LTQuMywxLjktOC42LDIuOC0xMi45Qzg0LjcsNjIuMSw4Ny4zLDYyLjEsODkuOSw2Mi4xeiIvPgo8cG9seWdvbiBjbGFzcz0ic3QxIiBwb2ludHM9IjEyOC45LDY1LjEgMTEwLjUsMTIwLjYgOTEuOSw2NS4xIDkxLjgsNjUuMSA4NC4zLDY1LjEgODEuOSw3Ni45IDY3LjcsMTQ5LjIgODEuNywxNDkuMiA5MS4zLDEwNS4zICAgMTA3LjksMTUxLjIgMTEzLDE1MS4yIDEyOS41LDEwNS4zIDEzOS4xLDE0OS4zIDE1My4xLDE0OS4zIDEzNi42LDY1LjEgIi8+Cjwvc3ZnPg==)](https://virtuemart.net/download)
[![](https://img.shields.io/badge/Virtuemart-v4.4.6-00A1DF?style=for-the-badge&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMDEuOSAxNTIuMSIgaGVpZ2h0PSIxMDBweCIgd2lkdGg9IjEwMHB4IiB0cmFuc2Zvcm09InJvdGF0ZSgwKSBzY2FsZSgxLCAxKSI+CjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+LnN0MCB7IGZpbGw6ICNmZmZmZmY7IH0uc3QxIHsgZmlsbDogI2ZmZmZmZjsgfTwvc3R5bGU+CjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik04OS45LDYyLjFjMC41LTAuNSwxLjEtMS4xLDEuNy0xLjdjMjctMjYuOSw2My00NS45LDExMC4zLTU2LjJMMjAwLjMsMEM4NywxOSw0OS45LDkxLDM3LjYsMTIxLjIgIGMtMS44LTQuNy0xNy4yLTQ3LjktMjAuMy01Ni4xSDBsMzIuOSw4N2g4YzEuMy0zLjgsMTItMzkuMSwzMC42LTY1LjhjMC43LTEuMSwxLjQtMi4yLDIuMi0zLjRjMC44LTEsMS42LTIuMSwyLjMtMy4xICBjMC4yLTAuMiwxLTEuNCwxLTEuNHMwLjctMSwxLTEuNWMwLjUtMC44LDEtMS40LDEuMy0xLjljMC45LTQuMywxLjktOC42LDIuOC0xMi45Qzg0LjcsNjIuMSw4Ny4zLDYyLjEsODkuOSw2Mi4xeiIvPgo8cG9seWdvbiBjbGFzcz0ic3QxIiBwb2ludHM9IjEyOC45LDY1LjEgMTEwLjUsMTIwLjYgOTEuOSw2NS4xIDkxLjgsNjUuMSA4NC4zLDY1LjEgODEuOSw3Ni45IDY3LjcsMTQ5LjIgODEuNywxNDkuMiA5MS4zLDEwNS4zICAgMTA3LjksMTUxLjIgMTEzLDE1MS4yIDEyOS41LDEwNS4zIDEzOS4xLDE0OS4zIDE1My4xLDE0OS4zIDEzNi42LDY1LjEgIi8+Cjwvc3ZnPg==)](https://virtuemart.net/download)<br>
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
- ignore variants
- add variants informations to parents
- ignore products which do not have a category assignment
- shoppergroups to index
- use the parent image if the variant does not have anything assigned
- use the parent category if the variant does not have anything assigned
- use the parent manufacturer if the variant does not have anything assigned
- use customfields as taxonomy, they will then not only be available for searching but also for filtering (and better usage in templates)

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

### Recommened Smart Search Component/Module

I recommened the search component/module from Minitek.

It has multiple ways to show the index, including filtering systems.

[![](https://img.shields.io/badge/Minitek_Live_Search-31587B?style=for-the-badge&logo=data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjM2IiBoZWlnaHQ9IjM3Ij4KPHBhdGggZD0iTTAgMCBDNC4yNDYwNDE0NSA0LjYzMTQ4MDA2IDUuNTY2OTYwNjEgNy43NjU4MDQ5NiA1LjQzOTQ1MzEyIDE0LjAxMzY3MTg4IEM1LjM5NzU1ODU5IDE0LjgxMzUzNTE2IDUuMzU1NjY0MDYgMTUuNjEzMzk4NDQgNS4zMTI1IDE2LjQzNzUgQzUuMjg5OTQxNDEgMTcuMjY0NDMzNTkgNS4yNjczODI4MSAxOC4wOTEzNjcxOSA1LjI0NDE0MDYyIDE4Ljk0MzM1OTM4IEM1LjE4NTc2ODQ3IDIwLjk2MzAzNTc3IDUuMDk2MTA2MzIgMjIuOTgxNzY3MjEgNSAyNSBDMy43ODMxMjUgMjQuNzg0NzI2NTYgMi41NjYyNSAyNC41Njk0NTMxMiAxLjMxMjUgMjQuMzQ3NjU2MjUgQy0yLjU1NDg5NDAyIDIzLjg2NDIzMiAtNC42NTI2MDg2IDI0LjI5MzA3NzM4IC04IDI2LjQzNzUgQy04Ljc2MzEyNSAyNi45MTA1ODU5NCAtOS41MjYyNSAyNy4zODM2NzE4OCAtMTAuMzEyNSAyNy44NzEwOTM3NSBDLTEwLjg2OTM3NSAyOC4yNDM2MzI4MSAtMTEuNDI2MjUgMjguNjE2MTcxODcgLTEyIDI5IEMtMi42MTAyMDY4MyAzMy4xMzk4OTI1NiAtMi42MTAyMDY4MyAzMy4xMzk4OTI1NiA3LjI4OTA2MjUgMzMuMjE4NzUgQzkuNzAzMDc1MzkgMzEuOTY2NTI0MjkgMTEuODU4ODgwMzggMzAuNjcyNzQ5NzEgMTQgMjkgQzEzLjY3IDI4LjY3IDEzLjM0IDI4LjM0IDEzIDI4IEMxMi43NjQ3MjIyNyAyNS42NTAxMjczOCAxMi41ODYxMzU4MyAyMy4yOTQ0Mzk2MSAxMi40Mzc1IDIwLjkzNzUgQzEyLjM1MzcxMDk0IDE5LjY0NzE0ODQ0IDEyLjI2OTkyMTg4IDE4LjM1Njc5Njg4IDEyLjE4MzU5Mzc1IDE3LjAyNzM0Mzc1IEMxMi4xMjMwMDc4MSAxNi4wMjgzMjAzMSAxMi4wNjI0MjE4NyAxNS4wMjkyOTY4OCAxMiAxNCBDMTEuMDEgMTQuNjYgMTAuMDIgMTUuMzIgOSAxNiBDNy41IDE0LjY4NzUgNy41IDE0LjY4NzUgNiAxMyBDNiAxMCA2IDEwIDguNTYyNSA3LjI1IEMxMC4wMDM5MDYyNSA1Ljg5MDYyNSAxMC4wMDM5MDYyNSA1Ljg5MDYyNSAxMiA1IEMxNC4zMSA1LjMzIDE2LjYyIDUuNjYgMTkgNiBDMTkgMTMuOTIgMTkgMjEuODQgMTkgMzAgQzUuNTQ4MTA3NjIgMzguMDcxMTM1NDMgNS41NDgxMDc2MiAzOC4wNzExMzU0MyAtMC41OTM3NSAzNi43MzA0Njg3NSBDLTEuMzg3ODEyNSAzNi40ODk0MTQwNiAtMi4xODE4NzUgMzYuMjQ4MzU5MzcgLTMgMzYgQy00LjEyNDA2MjUgMzUuNjg2NzU3ODEgLTUuMjQ4MTI1IDM1LjM3MzUxNTYzIC02LjQwNjI1IDM1LjA1MDc4MTI1IEMtMTUuNjUyODQxNzggMzIuMzQ3MTU4MjIgLTE1LjY1Mjg0MTc4IDMyLjM0NzE1ODIyIC0xNyAzMSBDLTE3LjQ0NjUxNjI0IDI3LjQ1MDQwODQ5IC0xNy41MTE0MTc4MyAyMy44ODU3OTQ5NCAtMTcuNjI1IDIwLjMxMjUgQy0xNy42OTU4OTg0NCAxOS4zMTc5ODgyOCAtMTcuNzY2Nzk2ODggMTguMzIzNDc2NTYgLTE3LjgzOTg0Mzc1IDE3LjI5ODgyODEyIEMtMTcuOTE2MDEyMTMgMTQuMjg2MTY4MjIgLTE3Ljg1Mzk5MTg4IDExLjg5NDk4MjI3IC0xNyA5IEMtMTQuMzI2MDQyNzYgNi4yODExMDc5NCAtMTEuMzk3Njk1NSA0LjY3NDY0MjM0IC04IDMgQy02LjU3Njg3NSAyLjA3MTg3NSAtNi41NzY4NzUgMi4wNzE4NzUgLTUuMTI1IDEuMTI1IEMtMyAwIC0zIDAgMCAwIFogTS0xMi44NDM3NSA4LjM3ODkwNjI1IEMtMTYuNjA3Mzc2ODYgMTIuOTUzODk0NyAtMTUuNzc3ODc3MDcgMTguMDgzMTQ4ODQgLTE1LjM1MTU2MjUgMjMuNzQ2MDkzNzUgQy0xNS4yMzU1NDY4OCAyNC44MTk4ODI4MSAtMTUuMTE5NTMxMjUgMjUuODkzNjcxODcgLTE1IDI3IEMtMTAuNTgyODI2MDEgMjUuMzkzNzU0OTEgLTcuMjI3NDM0ODIgMjMuNDE1Njc3MDQgLTMgMjEgQy0zIDE1LjM5IC0zIDkuNzggLTMgNCBDLTYuODU2OTIwMjEgNCAtOS43NTc2ODg5MyA2LjI0NjE1NDI3IC0xMi44NDM3NSA4LjM3ODkwNjI1IFogIiBmaWxsPSIjRUJFQkVCIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxNywwKSIvPgo8cGF0aCBkPSJNMCAwIEMwIDcuOTIgMCAxNS44NCAwIDI0IEMtMC45OSAyNCAtMS45OCAyNCAtMyAyNCBDLTMgMjMuMzQgLTMgMjIuNjggLTMgMjIgQy0zLjk5IDIyIC00Ljk4IDIyIC02IDIyIEMtNi4zMyAxNy4zOCAtNi42NiAxMi43NiAtNyA4IEMtNy45OSA4LjY2IC04Ljk4IDkuMzIgLTEwIDEwIEMtMTEuNSA4LjY4NzUgLTExLjUgOC42ODc1IC0xMyA3IEMtMTMgNCAtMTMgNCAtMTAuNDM3NSAxLjI1IEMtNi44MjkxOTkzMSAtMi4xNTI5NTAyNCAtNS43NTIwMzg1NSAtMC44MjE3MTk3OSAwIDAgWiAiIGZpbGw9IiNGNkY2RjYiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDM2LDYpIi8+CjxwYXRoIGQ9Ik0wIDAgQzMuNTY3NjcxNTcgMS4xODkyMjM4NiA0LjI2NDMyODM0IDEuNzAyNzM1ODQgNiA1IEM2LjM2MjIwNDg5IDguMDkyNjE0ODIgNi4yODIyMDI0NiAxMS4xNDE4MTY2NiA2LjE4NzUgMTQuMjUgQzYuMTczOTY0ODQgMTUuMDkwNDY4NzUgNi4xNjA0Mjk2OSAxNS45MzA5Mzc1IDYuMTQ2NDg0MzggMTYuNzk2ODc1IEM2LjExMTIzNDY0IDE4Ljg2NDg1OTM4IDYuMDU3NDMwMiAyMC45MzI1MTI3MSA2IDIzIEMyLjcgMjIuMzQgLTAuNiAyMS42OCAtNCAyMSBDLTQgMjAuMzQgLTQgMTkuNjggLTQgMTkgQy0zLjM0IDE5IC0yLjY4IDE5IC0yIDE5IEMtMi4wMjMyMDMxMyAxNy43OTYwMTU2MiAtMi4wNDY0MDYyNSAxNi41OTIwMzEyNSAtMi4wNzAzMTI1IDE1LjM1MTU2MjUgQy0yLjA4OTA2ODYzIDEzLjc3NjA0Nzg1IC0yLjEwNzI2ODk5IDEyLjIwMDUyNjUyIC0yLjEyNSAxMC42MjUgQy0yLjE0MTc1NzgxIDkuODMwOTM3NSAtMi4xNTg1MTU2MiA5LjAzNjg3NSAtMi4xNzU3ODEyNSA4LjIxODc1IEMtMi4xOTMzNTQ3MyA2LjE0NTA3OTg1IC0yLjEwMzM1MTY4IDQuMDcxMTY3NTkgLTIgMiBDLTEuMzQgMS4zNCAtMC42OCAwLjY4IDAgMCBaICIgZmlsbD0iI0Y3RjdGNyIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTYsMikiLz4KPHBhdGggZD0iTTAgMCBDMC42NiAwLjk5IDEuMzIgMS45OCAyIDMgQy04Ljg1NTc1Mjg0IDEwLjAwMDQzODc1IC04Ljg1NTc1Mjg0IDEwLjAwMDQzODc1IC0xNS43MTg3NSA4LjYzMjgxMjUgQy0xNi40NzE1NjI1IDguNDIzOTg0MzggLTE3LjIyNDM3NSA4LjIxNTE1NjI1IC0xOCA4IEMtMTguOTE3ODEyNSA3Ljc0OTkyMTg3IC0xOS44MzU2MjUgNy40OTk4NDM3NSAtMjAuNzgxMjUgNy4yNDIxODc1IEMtMjMuMjA1NDkwNDMgNi41NDA3OTIwOSAtMjUuNjA1MDg2MTcgNS43OTQ5NzE0IC0yOCA1IEMtMjUgMyAtMjUgMyAtMjIuNjYwMTU2MjUgMy4yMDcwMzEyNSBDLTIxLjgwMjkyOTY5IDMuNDA2ODM1OTQgLTIwLjk0NTcwMzEzIDMuNjA2NjQwNjMgLTIwLjA2MjUgMy44MTI1IEMtMTkuMjA3ODUxNTYgNC4wMDQ1NzAzMSAtMTguMzUzMjAzMTMgNC4xOTY2NDA2MyAtMTcuNDcyNjU2MjUgNC4zOTQ1MzEyNSBDLTE1IDUgLTE1IDUgLTEzLjA0Mjk2ODc1IDUuNzU3ODEyNSBDLTkuODUzMjM5MDYgNi4xMzU5NDQ4OSAtNy45NzIxNDgzOCA0LjY1MDQxOTg5IC01LjI1IDMuMDYyNSBDLTMuNzcyNzM0MzcgMi4yMTM2NTIzNCAtMy43NzI3MzQzNyAyLjIxMzY1MjM0IC0yLjI2NTYyNSAxLjM0NzY1NjI1IEMtMS41MTc5Njg3NSAwLjkwMjkyOTY5IC0wLjc3MDMxMjUgMC40NTgyMDMxMyAwIDAgWiAiIGZpbGw9IiNENUQ1RDUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDMyLDI4KSIvPgo8cGF0aCBkPSJNMCAwIEMtMS4zNDQwODM1NSA0LjAzMjI1MDY0IC0zLjk0NjA5ODUxIDUuMDg2MTcwOSAtNy40Mzc1IDcuMTI1IEMtOC4wMzYyNjk1MyA3LjQ4NDY0ODQ0IC04LjYzNTAzOTA2IDcuODQ0Mjk2ODggLTkuMjUxOTUzMTIgOC4yMTQ4NDM3NSBDLTExLjQ3MTY4ODUzIDkuNTQyMjEwNzIgLTEzLjY4NjAxNDAzIDEwLjg0MzAwNzAxIC0xNiAxMiBDLTE2LjMzIDExLjAxIC0xNi42NiAxMC4wMiAtMTcgOSBDLTMuNjMxMDY3OTYgLTAgLTMuNjMxMDY3OTYgLTAgMCAwIFogIiBmaWxsPSIjRDJEMkQyIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxNywwKSIvPgo8cGF0aCBkPSJNMCAwIEMxLjI5OTM3NSAwLjI4NjE3MTg4IDEuMjk5Mzc1IDAuMjg2MTcxODggMi42MjUgMC41NzgxMjUgQzMuOTI0Mzc1IDAuODQ4ODI4MTIgMy45MjQzNzUgMC44NDg4MjgxMiA1LjI1IDEuMTI1IEM3LjYyNSAxLjgyODEyNSA3LjYyNSAxLjgyODEyNSAxMC42MjUgMy44MjgxMjUgQzQuNzI4MDQ4NzIgNS41MzUxMzcyMSAwLjIwNTExMTgzIDMuNzgwMDYxMzUgLTUuMzc1IDEuODI4MTI1IEMtMi4zNzUgLTAuMTcxODc1IC0yLjM3NSAtMC4xNzE4NzUgMCAwIFogIiBmaWxsPSIjQ0FDQUNBIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSg5LjM3NSwzMS4xNzE4NzUpIi8+CjxwYXRoIGQ9Ik0wIDAgQzAuOTkgMC4zMyAxLjk4IDAuNjYgMyAxIEMzIDEuNjYgMyAyLjMyIDMgMyBDMy45OSAzLjMzIDQuOTggMy42NiA2IDQgQzYgNC42NiA2IDUuMzIgNiA2IEM0LjAyIDYgMi4wNCA2IDAgNiBDLTEuMTI1IDIuMjUgLTEuMTI1IDIuMjUgMCAwIFogIiBmaWxsPSIjREZERkRGIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxLDI2KSIvPgo8L3N2Zz4K)](https://www.minitek.gr/joomla/extensions/minitek-live-search)

---

### Debugging

If you enable Joomla debugging you will get a second option on the Smart Search Index button "Index debugging".<br>
Here you will see anything what got indexed for an specific item.<br>
If you get an error while the indexing is running, you can get here more informations what went wrong (but you have to know the ID).<br>
Or you enable can enable logging in the Smart Search configuration and get a protocol in your logs folder while the indexing is running.

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
