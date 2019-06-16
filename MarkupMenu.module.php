<?php

namespace ProcessWire;

/**
 * MarkupMenu ProcessWire module
 *
 * MarkupMenu is a module for generating menu markup. See README.md for more details.
 * Some ideas and code in this module are based on the Markup Simple Navigation module.
 * 
 * @version 0.1.0
 * @author Teppo Koivula <teppo.koivula@gmail.com>
 * @license Mozilla Public License v2.0 http://mozilla.org/MPL/2.0/
 */
class MarkupMenu extends WireData implements Module {

    /**
     * Default options
     *
     * @var array
     */
    public static $defaultOptions = [
        'root_page' => null,
        'current_page' => null,
        'templates' => [
            'list' => '<ul class="level-{level} {classes}">%s</ul>',
            'list_item' => '<li class="level-{level} {classes}">%s</li>',
            'item' => '<a href="{item.url}">{item.title}</a>',
            'item_current' => '<span>{item.title}</span>',
        ],
        'include' => [
            'selector' => null,
            'root_page' => false,
        ],
        'exclude' => [
            'selector' => null,
            'listable' => false,
            'level_greater_than' => null,
        ],
        'collapsed' => true,
        'flat_root' => true,
        'text_tools_options' => [],
        'placeholders' => [],
        'classes' => [
            // 'page_id' => 'id-',
            'current' => 'current',
            'parent' => 'parent',
            'has_children' => 'has-children',
        ],
    ];

    /**
     * Text tools instance
     *
     * @var null|WireTextTools
     */
    protected $textTools = null;

    /**
     * Render menu markup
     *
     * @param array $options Custom options
     * @return string Rendered menu markup
     */
    public function render(array $options = []) : string {

        // merge options with default options and config options
        $options = array_replace_recursive(
            static::$defaultOptions,
            is_array($this->wire('config')->MarkupMenu) ? $this->wire('config')->MarkupMenu : [],
            $options
        );

        // get the root page
        $options['root_page'] = $this->getPage($options['root_page'], '/');

        // get current page
        $options['current_page'] = $this->getPage($options['current_page']);

        // get an instance of text tools
        $this->textTools = $this->wire('sanitizer')->getTextTools();

        // load MarkupMenuData
        require_once __DIR__ . '/MarkupMenuData.php';

        // generate and return menu markup
        $menu = $this->renderTree($options, $options['root_page']);
        return $menu;
        
    }

    /**
     * Render tree of items using recursion
     *
     * @param array $options Options for rendering
     * @param Page $root Root page for the menu
     * @param int $level Current tree level (depth)
     * @return string Rendered menu markup
     */
    protected function renderTree(array $options = [], Page $root, int $level = 1) : string {

        $out = '';

        // fetch items (children of the root page), optionally filtered by a selector string
        $items = new PageArray();
        if (!$options['include']['root_page'] || $options['flat_root']) {
            $items->add($root->children($options['include']['selector']));
        }

        // optionally prepend the root page itself – but only once!
        if ($options['include']['root_page']) {
            $options['include']['root_page'] = false;
            $items->prepend($root);
        }

        // exclude rules based on selector string
        if (!empty($options['exclude']['selector'])) {
            $items->not($options['exclude']['selector']);
        }

        // iterate items and render markup for each separately
        foreach ($items as $item) {
            $out .= $this->renderTreeItem($options, $item, $root, $level);
        }

        // generate list markup
        if (!empty($out)) {
            $placeholders = ( new MarkupMenuData() )
                ->setArray([
                    'level' => $level,
                    'placeholders' => $options['placeholders'],
                    'root_page' => $options['root_page'],
                ]);
            $out = sprintf(
                $this->textTools->populatePlaceholders(
                    $options['templates']['list'],
                    $placeholders,
                    $options['text_tools_options']
                ),
                $out
            );
        }

        return $out;

    }

    /**
     * Render markup for a single menu item
     *
     * @param array $options Options for rendering
     * @param Page $item Menu item being rendered
     * @param bool $with_children Include markup for child pages?
     * @param int $level Current tree level (depth)
     * @return string Rendered menu item markup
     */
    protected function renderTreeItem(array $options = [], Page $item, Page $root, int $level = 1) : string {

        $out = '';

        // exclude rules based on listability
        if (isset($options['exclude']['listable']) && $item->listable() == $options['exclude']['listable']) {
            return $out;
        }

        // default classes
        $item_classes = [];
        if ($options['classes']['page_id']) {
            $item_classes['page_id'] = $options['classes']['page_id'] . $item->id;
        }

        // is this current page?
        $item_is_current = $options['current_page'] && $options['current_page']->id === $item->id;
        if ($item_is_current) $item_classes['current'] = $options['classes']['current'];

        // is this a parent page?
        $item_is_parent = !$item_is_current && ($item->id !== $root->id || !$options['flat_root']) && $options['current_page'] && $options['current_page']->parents->has($item);
        if ($item_is_parent) $item_classes['parent'] = $options['classes']['parent'];

        // have we reached the level limit?
        $level_limit_reached = $options['exclude']['level_greater_than'] && $level >= $options['exclude']['level_greater_than'];

        // should we render children for this item?
        $with_children = ($item->id !== $root->id || !$options['flat_root']) && !$level_limit_reached && (!$options['collapsed'] || $item_is_current || $item_is_parent);

        // placeholders for string replacements
        $placeholders = ( new MarkupMenuData() )
            ->setArray(array_merge($options['placeholders'], [
                'level' => $level,
                'item' => $item,
                'classes' => implode(' ', $item_classes),
            ]));

        // generate markup for menu item
        $item_template = $this->getTemplate('item' . ($item_is_current ? '_current' : ''), $item, $options);
        $item_markup = $this->textTools->populatePlaceholders(
            $item_template,
            $placeholders,
            $options['text_tools_options']
        );

        // generate markup for menu item children
        if ($with_children) {
            $item_markup .= $this->renderTree($options, $item, $level + 1);
        }

        // generate markup for current list item
        $out .= sprintf(
            $this->textTools->populatePlaceholders(
                $this->getTemplate('list_item', $item, $options),
                $placeholders,
                $options['text_tools_options']
            ),
            $item_markup
        );

        return $out;

    }

    /**
     * Get a Page based on mixed source param
     *
     * @param mixed $source Page source argument
     * @param mixed $default Optional default page in case source is empty
     * @return null|Page Page object or null
     */
    protected function getPage($source = null, $default = null) {
        
        $page = null;

        if ($source instanceof Page) {
            $page = $source;
        } else if (is_int($source)) {
            $page = $this->wire('pages')->findOne('id=' . $source);
        } else if (is_string($source)) {
            $page = $this->wire('pages')->findOne($source);
        } else if (!empty($default)) {
            $page = $this->getPage($default);
        }

        return $page;
 
    }

    /**
     * Get template for rendering an element (list item, item, or item_current)
     *
     * @param string $template Template name
     * @param Page $item Item being rendered
     * @return string Template
     */
    protected function ___getTemplate($template, Page $item, array $options) {
        return $options['templates'][$template];
    }

}
