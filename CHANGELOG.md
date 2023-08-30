# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2023-08-30

### Added
- New hookable methods treeItemIsParent() and arrayItemIsParent().

### Changed
- Signature for hookable method renderArrayItem has changed, $root item is now provided as the third param. This applies to a protected method, but is still a breaking change in case third party code is hooking into said method.

### Fixed
- Root page ID check in renderArrayItem() was referring to a non-existing variable.

## [0.11.0] - 2022-12-04

### Added
- Support for providing menu items as a prepopulated array via the menu_items option.
- Support for callables as templates, enabling template string to be defined dynamically.

## [0.10.0] - 2021-09-02

### Added
- Include level by default in the 'item' template.

## [0.9.0] - 2021-06-09

### Added
- New hookable method getSelector().

## [0.8.2] - 2020-11-30

### Changed
- Updated ProcessWire version requirement in composer.json.

## [0.8.1] - 2020-03-12

### Fixed
- Consider possible include selector when checking if a menu item has children.

## [0.8.0] - 2019-12-25

### Added
- Support for passing in a prepopulated PageArray of menu items via the menu_items option.

### Fixed
- Fixed an issue where include option root_page wasn't being properly reset (since 0.7.0).

## [0.7.0] - 2019-12-18

### Added
- New hookable method getItems().

### Changed
- Method renderTreeItem() made hookable.

## [0.6.2] - 2019-06-30

### Changed
- Bumped required version of wireframe-framework/processwire-composer-installer to 1.0.

## [0.6.1] - 2019-06-26

### Fixed
- An issue where the has_class class was not applied to elements with children.

## [0.6.0] - 2019-06-23

### Changed
- Changes to default templates and classes, and the addition of a new {class} default placeholder.

### Fixed
- An issue where sub-trees were also getting wrapped with the 'nav' element.

## [0.5.0] - 2019-06-23

### Changed
- Switched WireTextTools::populatePlaceholders() calls to wirePopulateStringTags() in order to support current stable ProcessWire version.
- Renamed 'text_tools_options' option to 'placeholder_options'.

## [0.4.1] - 2019-06-22

### Changed
- Updated the required version of wireframe-framework/processwire-composer-installer.

## [0.4.0] - 2019-06-19

### Added
- Support for "&" placeholders in class names, referencing current template class.
- Constructor method for MarkupMenuData for easily instantiating objects with data.

### Changed
- Changes to default classes and some code reordering.

### Fixed
- An issue with menu item class name output, where the result was "Array".

## [0.3.0] - 2019-06-18

### Changed
- Add classes to templates array items nav, item, and item_current in default options.

### Added
- Classes array indexes for items in the templates array: nav, list, list_item, item, and item_current.

## [0.2.0] - 2019-06-17

### Changed
- Custom placeholders from options array are merged with default placeholder values instead of getting added as property "placeholders".

## [0.1.0] - 2019-06-06

### Added
- New semantic 'nav' element for wrapping existing list, list item, and item elements.

### Changed
- Rewrote parts of the MarkupMenu codebase to decrease repetition of code.
