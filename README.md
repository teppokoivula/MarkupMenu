MarkupMenu ProcessWire module
-----------------------------

MarkupMenu is a markup module for generating menu trees. When provided a root page as a starting point,
it generates a navigation tree (by default as a HTML `<ul>` element wrapped by a `<nav>` element) from
that point onwards. If you've also provided it with current (active) page, the menu will be rendered
accordingly.

## Usage

As a markup module, MarkupMenu is intended for front-end use, but you can of course use it in a module
as well. Typically you'll only need the render() method, which takes an array of options as its only
argument:

```
echo $modules->get('MarkupMenu')->render([
    'root_page' => $pages->get(1),
    'current_page' => $page,
]);
```

Note: if you omit root_page, site root page is used by default – unless you've specified the menu_items
option instead, in which case a root page is not necessary. If you omit current_page, the menu will be
rendered, but current (active) page can't be highlighted.

## Options

Below you'll find all the available options and their default values. You can override these defaults
with the array you pass to the render method, or you can specify an array of site-wide custom options
via site config setting `$config->MarkupMenu`.

```
[
    // 'root_page' is the starting point for the menu. This is optional if you specify the 'menu_items'
    // option instead, but leaving *both* empty will make MarkupMenu::render() return an empty string.
    'root_page' => null,

    // 'menu_items' is an optional, prepopulated PageArray of first level menu items.
    'menu_items' => null,

    // 'current' page is the current or active menu page.
    'current_page' => null,

    // 'templates' are used for rendering individual parts of the menu:
    //
    // - the semantic <nav> element that acts as a wrapper for the menu ('nav'),
    // - the lists wrapping the menu items and the subtrees within it ('list'),
    // - the list items wrapping menu branches ('list_item'),
    // - the items (links) in the menu ('item')
    // - the active item ('item_current')
    //
    // special placeholder values populated by default:
    //
    // - {classes}: all default classes applied, including template class, current class, etc.
    // - {class}: the template class only, mostly useful for adding a prefix to other classes
    // - {item}: the item itself, i.e. a Page object and all its field values andproperties
    // - {level}: the level of current item, represented by an integer starting from 1
    'templates' => [
        'nav' => '<nav class="{classes}">%s</nav>',
        'list' => '<ul class="{classes} {class}--level-{level}">%s</ul>',
        'list_item' => '<li class="{classes} {class}--level-{level}">%s</li>',
        'item' => '<a href="{item.url}" class="{classes} {class}--level-{level}">{item.title}</a>',
        'item_current' => '<span class="{classes} {class}--level-{level}">{item.title}</span>',
    ],

    // 'include' defines the pages included in the menu: you can provide 'selector' string to choose
    // suitable pages, and use a boolean toggle ('root_page') to choose whether the root page itself
    // should be included in the menu.
    'include' => [
        'selector' => null,
        'root_page' => false,
    ],

    // 'exclude' rules are the opposite of the include rules, and allow you to define the pages not
    // included in the menu: pages matching a selector string, non-listable pages ('listable' value
    // of false means that non-listable pages are excluded), and pages that would exceed a maximum
    // level or depth ('level_greater_than').
    'exclude' => [
        'selector' => null,
        'listable' => false,
        'level_greater_than' => null,
    ],

    // 'collapsed', in the lack of a better name, defines whether your menu should only be rendered
    // up current (active) page, or first level if no current page was provided.
    'collapsed' => true,

    // 'flat_root' is only useful if you've chosen to include the root page in the menu: this option
    // puts the root page at the same level as your other first level pages – typically you'd want
    // this, so that your home page shows up at the same level as the first level below it.
    'flat_root' => true,

    // 'placeholder_options' is an optional array of options that will be passed to WireTextTools or
    // the wirePopulateStringTags() function, used for tag replacements in templates defined via the
    // 'templates' option.
    'placeholder_options' => [],

    // 'placeholders' can be used to provide additional custom values for string replacements used
    // within the templates defined via the 'templates' option.
    'placeholders' => [],

    // 'classes' can be used to override default class names added to items when the {classes} tag
    // is used in a template defined via the 'templates' option.
    'classes' => [
        // 'page_id' => '&--page-id-', // note: page_id is disabled by default!
        'nav' => 'menu',
        'list' => 'menu__list',
        'list_item' => 'menu__list-item',
        'item' => 'menu__item',
        'item_current' => 'menu__item',
        'current' => '&--current',
        'parent' => '&--parent',
        'has_children' => '&--has-children',
    ],
];
```

## Requirements

- ProcessWire >= 3.0.112
- PHP >= 7.1.0

If you're working on an earlier version of ProcessWire or PHP, I'd highly recommend checking out the
MarkupSimpleNavigation module instead: https://github.com/somatonic/MarkupSimpleNavigation.

## Installing

This module can be installed just like any other ProcessWire module, by downloading or cloning the
MarkupMenu directory into your /site/modules/ directory. Alternatively you can install MarkupMenu
through Composer by executing `composer require teppokoivula/markup-menu` in your site directory.

## License

This project is licensed under the Mozilla Public License Version 2.0.
