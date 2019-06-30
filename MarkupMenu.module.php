<?php

namespace ProcessWire;

/**
 * MarkupMenu ProcessWire module
 *
 * MarkupMenu is a module for generating menu markup. See README.md for more details.
 * Some ideas and code in this module are based on the Markup Simple Navigation module.
 *
 * @version 0.6.2
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
            'nav' => '<nav class="{classes}">%s</nav>',
            'list' => '<ul class="{classes} {class}--level-{level}">%s</ul>',
            'list_item' => '<li class="{classes} {class}--level-{level}">%s</li>',
            'item' => '<a href="{item.url}" class="{classes}">{item.title}</a>',
            'item_current' => '<span class="{classes}">{item.title}</span>',
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
        'placeholder_options' => [],
        'placeholders' => [],
        'classes' => [
            // 'page_id' => '&--page-id-',
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

        if (!empty($out) && (!empty($options['templates']['list']) || !empty($options['templates']['nav']))) {

            // set up a placeholders
            $placeholders = [
                'level' => $level,
                'root_page' => $options['root_page'],
            ];

            // generate list markup
            $out = $this->applyTemplate('list', $out, $placeholders, $options);

            // generate nav markup
            if ($level === 1) {
                $out = $this->applyTemplate('nav', $out, $placeholders, $options);
            }

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
        $classes = [];
        if (!empty($options['classes']['page_id'])) {
            $classes['page_id'] = $options['classes']['page_id'] . $item->id;
        }

        // is this current page?
        $item_is_current = $options['current_page'] && $options['current_page']->id === $item->id;
        if ($item_is_current) $classes['current'] = $options['classes']['current'];

        // is this a parent page?
        $item_is_parent = !$item_is_current && ($item->id !== $root->id || !$options['flat_root']) && $options['current_page'] && $options['current_page']->parents->has($item);
        if ($item_is_parent) $classes['parent'] = $options['classes']['parent'];

        // have we reached the level limit?
        $level_limit_reached = $options['exclude']['level_greater_than'] && $level >= $options['exclude']['level_greater_than'];

        // does this page have children?
        $has_children = ($item->id !== $root->id || !$options['flat_root']) && !$level_limit_reached && $item->hasChildren;
        if ($has_children) $classes['has_children'] = $options['classes']['has_children'];

        // should we render the children for this item?
        $with_children = $has_children && (!$options['collapsed'] || $item_is_current || $item_is_parent);

        // placeholders for string replacements
        $placeholders = array_merge(
            [
                'level' => $level,
                'item' => $item,
                'classes' => $classes,
            ],
            $options['placeholders']
        );

        // generate markup for menu item
        $item_template_name = 'item' . ($item_is_current ? '_current' : '');
        $item_template = $this->getTemplate($item_template_name, $item, $options);
        $item_placeholders = $placeholders;
        $item_placeholders['classes'] = $this->parseClassesString($item_placeholders, $options, $item_template_name);
        $item_markup = wirePopulateStringTags(
            $item_template,
            new MarkupMenuData($item_placeholders),
            $options['placeholder_options']
        );

        // generate markup for menu item children
        if ($with_children) {
            $item_markup .= $this->renderTree($options, $item, $level + 1);
        }

        // generate markup for current list item
        $out .= $this->applyTemplate('list_item', $item_markup, $placeholders, $options, $item);

        return $out;

    }

    /**
     * Get a Page based on mixed source param
     *
     * @param mixed $source Page source argument
     * @param mixed $default Optional default page in case source is empty
     * @return null|Page Page object or null
     */
    protected function getPage($source = null, $default = null) : ?Page {
        
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
     * @param string $template_name Template name
     * @param Page|null $item Item being rendered
     * @param array $options An array of options
     * @return string Template
     */
    protected function ___getTemplate($template_name, Page $item = null, array $options = []) : string {
        return $options['templates'][$template_name];
    }

    /**
     * Apply a template to content string
     *
     * @param string $template_name Name of the template
     * @param string $content Content to be wrapped in template
     * @param array $placeholders Array of placeholders for string replacements
     * @param array $options An array of options
     * @param Page|null $item Item being rendered
     * @return string Content either wrapped in template, or as-is if no template was defined
     */
    protected function applyTemplate(string $template_name, string $content, array $placeholders, array $options, Page $item = null) : string {

        $out = '';

        $template = $this->getTemplate($template_name, $item, $options);
        if (!empty($template)) {
            $placeholders['class'] = $placeholders['classes'][$template_name] ?? $options['classes'][$template_name] ?? null;
            $placeholders['classes'] = $this->parseClassesString($placeholders, $options, $template_name);
            $out = sprintf(
                wirePopulateStringTags(
                    $template,
                    new MarkupMenuData(array_merge(
                        $placeholders,
                        $options['placeholders']
                    )),
                    $options['placeholder_options']
                ),
                $content
            );
        }

        return $out;

    }

    /**
     * Parse classes array to a string, adding template class and processing self-references
     *
     * @param array $placeholders
     * @param array $options
     * @param string $template_name
     * @return string Parsed classes string
     */
    protected function parseClassesString(array $placeholders, array $options, string $template_name) : string {

        $out = '';

        // get classes array
        $classes = [];
        if (!empty($placeholders['classes']) && is_array($placeholders['classes'])) {
            $classes = $placeholders['classes'];
        }

        // add template name class (if available)
        $template_name_class = $classes[$template_name] ?? $options['classes'][$template_name] ?? null;
        if (!empty($template_name_class)) {
            array_walk($classes, function(&$class) use ($template_name_class) {
                $class = str_replace('&', $template_name_class, $class);
            });
            $classes[$template_name] = $template_name_class;
        }

        // convert classes array to string
        if (!empty($classes)) {
            $out = implode(' ', array_filter($classes));
        }

        return $out;

    }

}
