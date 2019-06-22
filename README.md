MarkupMenu ProcessWire module
-----------------------------

MarkupMenu is a markup module for generating menu trees. When provided a root page as a starting point,
it generates a navigation tree (by default as a HTML `<ul>` element) from that point onwards. If you've
also provided it with current (active) page, the menu will be rendered according to that information.

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

Note: if you omit root_page, site root page is used by default. If you omit current_page, the menu
will be rendered, but current (active) page won't be highlighted.

## Options

Below you'll find all the available options and their default values. You can override these defaults
with the array you pass to the render method, or you can specify an array of site-wide custom options
via site config setting `$config->MarkupMenu`.

```
[
    // 'root_page' is the starting point for the menu.
    'root_page' => null,

    // 'current' page is the current or active menu page.
    'current_page' => null,

    // 'templates' are used for rendering individual parts of the menu:
    //
    // - the semantic <nav> element that acts as a wrapper for the menu ('nav'),
    // - the lists wrapping the menu items and the subtrees within it ('list'),
    // - the list items wrapping menu branches ('list_item'),
    // - the items (links) in the menu ('item')
    // - the active item ('item_current')
    'templates' => [
        'nav' => '<nav class="{classes}">%s</nav>',
        'list' => '<ul class="level-{level} {classes}">%s</ul>',
        'list_item' => '<li class="level-{level} {classes}">%s</li>',
        'item' => '<a href="{item.url}" class="{classes}">{item.title}</a>',
        'item_current' => '<span class="{classes}">{item.title}</span>',
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

    // 'text_tools_options' is an optional array of options that will be passed to WireTextTools,
    // used for tag replacements in templates defined via the 'templates' option.
    'text_tools_options' => [],

    // 'placeholders' can be used to provide additional custom values for string replacements used
    // within the templates defined via the 'templates' option.
    'placeholders' => [],

    // 'classes' can be used to override default class names added to items when the {classes} tag
    // is used in a template defined via the 'templates' option.
    'classes' => [
        // 'page_id' => 'page-id-', // note: page_id is disabled by default!
        'nav' => 'menu',
        'list' => 'menu__list',
        'list_item' => 'menu__list-item',
        'item' => 'menu__item',
        'item_current' => 'menu__item menu__item--current',
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
