<?php

namespace Municipio\Helper;

class NavigationTree
{
    public $args = array();

    protected $postStatuses = array('publish');

    protected $currentPage = null;
    protected $ancestors = null;

    protected $topLevelPages = null;
    protected $secondLevelPages = null;

    protected $pageForPostTypeIds = null;

    public $itemCount = 0;
    protected $depth = 0;
    protected $currentDepth = 0;

    protected $output = '';

    protected $isAjaxParent = false;

    public function __construct($args = array(), $parent = false)
    {
        if ($parent) {
            $parent = get_post($parent);
            $this->isAjaxParent = true;
        }

        // Merge args
        $this->args = array_merge(array(
            'theme_location' => '',
            'include_top_level' => false,
            'sublevel' => false,
            'top_level_type' => 'tree',
            'render' => 'active',
            'depth' => -1,
            'start_depth' => 1,
            'wrapper' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
            'classes' => 'nav',
            'id' => '',
            'sidebar' => false
        ), $args);

        if ($this->args['depth'] > -1 && $this->args['start_depth'] > 1) {
            $this->args['depth'] += $this->args['start_depth'];
        }

        if (is_user_logged_in()) {
            $this->postStatuses[] = 'private';
        }

        // Get valuable page information
        if ($parent) {
            $parent->post_parent = 0;
            $this->currentPage = $parent;
        } else {
            $this->currentPage = $this->getCurrentPage();
        }

        $this->ancestors = array();
        if (is_a($this->currentPage, 'WP_Post')) {
            $this->ancestors = $this->getAncestors($this->currentPage->ID);
        }

        if ($this->args['top_level_type'] == 'mobile') {
            $themeLocations = get_nav_menu_locations();
            $this->topLevelPages = wp_get_nav_menu_items($themeLocations['main-menu'], array(
                'menu_item_parent' => 0
            ));

            if (is_array($this->topLevelPages)) {
                $this->topLevelPages = array_filter($this->topLevelPages, function ($item) {
                    return intval($item->menu_item_parent) === 0;
                });
            }
        } else {
            if ($parent) {
                $this->topLevelPages = array($parent);
            } else {
                $this->getTopLevelPages();
            }
        }

        if ($this->args['include_top_level']) {
            if ($this->args['sublevel']) {
                $this->walk($this->topLevelPages, 1, 'nav-has-sublevel');
                $this->getSecondLevelPages();

                $walkIndex = null;
                if (!empty($this->ancestors)) {
                    $walkIndex = $this->ancestors[0];
                } else {
                    $walkIndex = $this->currentPage->ID;
                }

                if (isset($this->secondLevelPages[$walkIndex]) && !is_null($walkIndex)) {
                    if ($this->currentPage->post_parent == 0) {
                        global $isSublevel;
                        $isSublevel = true;
                    }

                    $this->walk($this->secondLevelPages[$walkIndex], 2, 'nav-sublevel');
                }
            } else {
                $this->startWrapper();
                $this->walk($this->topLevelPages);
                $this->endWrapper();
            }
        } else {
            $ancestors = $this->getAncestors($this->currentPage);
            $page = isset($ancestors[0]) ? $ancestors[0] : $this->currentPage;

            if ($page) {
                $this->startWrapper();
                $this->walk(array($page));
                $this->endWrapper();
            }
        }
    }

    /**
     * Gets top level pages
     * @return void
     */
    protected function getTopLevelPages()
    {
        $topLevelQuery = new \WP_Query(array(
            'post_parent' => 0,
            'post_type' => 'page',
            'post_status' => $this->postStatuses,
            'orderby' => 'menu_order post_title',
            'order' => 'asc',
            'posts_per_page' => -1,
            'meta_query'    => array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'hide_in_menu',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key'   => 'hide_in_menu',
                        'value' => '0',
                        'compare' => '='
                    )
                )
            )
        ));

        $this->topLevelPages = $topLevelQuery->posts;
        return $this->topLevelPages;
    }

    /**
     * Gets second level pages
     * @return array
     */
    protected function getSecondLevelPages()
    {
        $secondLevel = array();

        foreach ($this->topLevelPages as $topLevelPage) {
            $pages = get_posts(array(
                'post_parent' => $topLevelPage->ID,
                'post_type' => 'page',
                'orderby' => 'menu_order post_title',
                'order' => 'asc'
            ));

            $secondLevel[$topLevelPage->ID] = $pages;
        }

        $this->secondLevelPages = $secondLevel;
        return $secondLevel;
    }

    /**
     * Walks pages in the menu
     * @param  array $pages Pages to walk
     * @return void
     */
    protected function walk($pages, $depth = 1, $classes = null)
    {
        $this->currentDepth = $depth;

        if ($this->args['sublevel']) {
            $this->startWrapper($classes, $depth === 1);
        }

        if (!is_array($pages)) {
            return;
        }

        foreach ($pages as $page) {
            $pageId = $this->getPageId($page);
            $attributes = array();
            $attributes['class'] = array();
            $output = true;

            if (is_numeric($page)) {
                $page = get_post($page);
            }

            if ($this->isAncestors($pageId)) {
                $attributes['class'][] = 'current-node current-menu-ancestor';

                if (count($this->getChildren($pageId)) > 0) {
                    $attributes['class'][] = 'is-expanded';
                }
            }

            if ($this->getPageId($this->currentPage) == $pageId) {
                $attributes['class'][] = 'current current-menu-item';
                if (count($this->getChildren($this->currentPage->ID)) > 0 && $depth != $this->args['depth']) {
                    $attributes['class'][] = 'is-expanded';
                }
            }

            if (($this->isAjaxParent && $depth === 1) || $depth < $this->args['start_depth']) {
                $output = false;
            }

            $this->item($page, $depth, $attributes, $output);
        }

        if ($this->args['sublevel']) {
            $this->endWrapper($depth === 1);
        }
    }

    /**
     * Outputs item
     * @param  object $page    The item
     * @param  array  $classes Classes
     * @return void
     */
    protected function item($page, $depth, $attributes = array(), $output = true)
    {
        $pageId = $this->getPageId($page);
        $children = $this->getChildren($pageId);

        $hasChildren = false;
        if (count($children) > 0) {
            $hasChildren = true;
            $attributes['class'][] = 'has-children';
            $attributes['class'][] = 'has-sub-menu';
        }

        if ($output) {
            $this->startItem($page, $attributes, $hasChildren);
        }

        if ($this->isActiveItem($pageId) && count($children) > 0 && ($this->args['depth'] <= 0 || $depth < $this->args['depth'])) {
            if ($output) {
                $this->startSubmenu($page);
            }

            $this->walk($children, $depth + 1);

            if ($output) {
                $this->endSubmenu($page);
            }
        }

        if ($output) {
            $this->endItem($page);
        }
    }

    /**
     * Gets the current page object
     * @return object
     */
    protected function getCurrentPage()
    {
        if (is_post_type_archive()) {
            $pageForPostType = get_option('page_for_' . get_post_type());
            return get_post($pageForPostType);
        }

        global $post;

        if (!is_object($post)) {
            return get_queried_object();
        }

        return $post;
    }

    /**
     * Get page children
     * @param  integer $parent The parent page ID
     * @return object          Page objects for children
     */
    protected function getChildren($parent)
    {
        $key = array_search($parent, $this->getPageForPostTypeIds());

        if ($key && is_post_type_hierarchical($key)) {
            $inMenu = false;

            foreach (get_field('avabile_dynamic_post_types', 'options') as $type) {
                if (sanitize_title(substr($type['post_type_name'], 0, 19)) !== $key) {
                    continue;
                }

                $inMenu = $type['show_posts_in_sidebar_menu'];
            }

            if ($inMenu) {
                return get_posts(array(
                    'post_type' => $key,
                    'post_status' => $this->postStatuses,
                    'post_parent' => 0,
                    'orderby' => 'menu_order post_title',
                    'order' => 'asc',
                    'posts_per_page' => -1,
                    'meta_query'    => array(
                        'relation' => 'OR',
                        array(
                            'key' => 'hide_in_menu',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key'   => 'hide_in_menu',
                            'value' => '0',
                            'compare' => '='
                        )
                    )
                ), 'OBJECT');
            }

            return array();
        }

        return get_posts(array(
            'post_parent' => $parent,
            'post_type' => get_post_type($parent),
            'post_status' => $this->postStatuses,
            'orderby' => 'menu_order post_title',
            'order' => 'asc',
            'posts_per_page' => -1,
            'meta_query'    => array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key' => 'hide_in_menu',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key'   => 'hide_in_menu',
                        'value' => '0',
                        'compare' => '='
                    )
                )
            )
        ), 'OBJECT');
    }

    protected function getPageForPostTypeIds()
    {
        if (is_array($this->pageForPostTypeIds)) {
            return $this->pageForPostTypeIds;
        }

        $pageIds = array();

        foreach (get_post_types(array(), 'objects') as $postType) {
            if (! $postType->has_archive) {
                continue;
            }

            if ('post' === $postType->name) {
                $pageId = get_option('page_for_posts');
            } else {
                $pageId = get_option("page_for_{$postType->name}");
            }

            if (!$pageId) {
                continue;
            }

            $pageIds[$postType->name] = $pageId;
        }

        $this->pageForPostTypeIds = $pageIds;
        return $this->pageForPostTypeIds;
    }

    /**
     * Get ancestors of the current page
     * @param  integer / post object $post
     * @return array ID's of ancestors
     */
    protected function getAncestors($post)
    {
        return array_reverse(get_post_ancestors($post));
    }

    /**
     * Checks if a specific id is in the ancestors array
     * @param  integer  $id
     * @return boolean
     */
    protected function isAncestors($id)
    {
        $ancestors  = $this->ancestors;
        $baseParent = $this->getAncestors($this->currentPage);
        if (is_array($baseParent) && !empty($baseParent)) {
            $ancestors = array_merge($ancestors, $baseParent);
        }

        return in_array($id, $ancestors);
    }

    /**
     * Checks if the given id is in a active/open menu scope
     * @param  integer  $id Page id
     * @return boolean
     */
    protected function isActiveItem($id)
    {
        if ($this->args['render'] == 'all' || !is_object($this->currentPage)) {
            return true;
        }

        return $this->isAncestors($id) || $id === $this->currentPage->ID;
    }

    /**
     * Opens a menu item
     * @param  object $item    The menu item
     * @param  array  $classes Classes
     * @return void
     */
    protected function startItem($item, $attributes = array(), $hasChildren = false)
    {
        if (!$this->shouldBeIncluded($item) || !is_object($item)) {
            return;
        }

        $this->itemCount++;
        $outputSubmenuToggle = false;

        $attributes['class'][] = 'page-' . $item->ID;

        if ($hasChildren && ($this->args['depth'] === -1 || $this->currentDepth < $this->args['depth'] + 1)) {
            $outputSubmenuToggle = true;

            if (array_search('has-children', $attributes['class']) > -1) {
                unset($attributes['class'][array_search('has-children', $attributes['class'])]);
            }
        }

        $title = isset($item->post_title) ? $item->post_title : '';
        $objId = $this->getPageId($item);

        if (isset($item->post_type) && $item->post_type == 'nav_menu_item') {
            $title = $item->title;
        }

        if (!empty(get_field('custom_menu_title', $objId))) {
            $title = get_field('custom_menu_title', $objId);
        }

        $href = get_permalink($objId);
        if (isset($item->type) && $item->type == 'custom') {
            $href = $item->url;
        }

        $this->addOutput(sprintf(
            '<li%1$s><a href="%2$s">%3$s</a>',
            $this->attributes($attributes),
            $href,
            $title
        ));

        if ($outputSubmenuToggle) {
            $this->addOutput('<button data-load-submenu="' . $objId . '"><span class="sr-only">' . __('Show submenu', 'municipio') . '</span><span class="icon"></span></button>');
        }
    }

    private function attributes($attributes = array())
    {
        foreach ($attributes as $attribute => &$data) {
            $data = implode(' ', (array) $data);
            $data = $attribute . '="' . $data . '"';
        }

        return $attributes ? ' ' . implode(' ', $attributes) : '';
    }

    /**
     * Closes a menu item
     * @param  object $item The menu item
     * @return void
     */
    protected function endItem($item)
    {
        if (!$this->shouldBeIncluded($item)) {
            return;
        }

        $this->addOutput('</li>');
    }

    /**
     * Starts wrapper
     * @return void
     */
    protected function startWrapper($classes = null, $filters = true)
    {
        $wrapperStart = explode('%3$s', $this->args['wrapper'])[0];

        if ($filters) {
            $wrapperStart = apply_filters('Municipio/main_menu/wrapper_start', $wrapperStart, $this->args);
        }

        $this->addOutput(sprintf(
            $wrapperStart,
            $this->args['id'],
            trim($this->args['classes'] . ' ' . $classes)
        ));
    }

    /**
     * Ends wrapper
     * @return void
     */
    protected function endWrapper($filters = true)
    {
        $wrapperEnd = explode('%3$s', $this->args['wrapper'])[1];

        if ($filters) {
            $wrapperEnd = apply_filters('Municipio/main_menu/wrapper_end', $wrapperEnd, $this->args);
        }

        $this->addOutput(sprintf(
            $wrapperEnd,
            $this->args['id'],
            $this->args['classes']
        ));
    }

    /**
     * Opens a submenu
     * @return void
     */
    protected function startSubmenu($item)
    {
        if (!$this->shouldBeIncluded($item)) {
            return;
        }

        $this->addOutput('<ul class="sub-menu">');
    }

    /**
     * Closes a submenu
     * @return void
     */
    protected function endSubmenu($item)
    {
        if (!$this->shouldBeIncluded($item)) {
            return;
        }

        $this->addOutput('</ul>');
    }

    /**
     * Datermines if page should be included in the menu or not
     * @param  object $item The menu item
     * @return boolean
     */
    public function shouldBeIncluded($item)
    {
        if (!is_object($item)) {
            return false;
        }

        $pageId = $this->getPageId($item);
        $showInMenu = get_field('hide_in_menu', $pageId) ? !get_field('hide_in_menu', $pageId) : true;
        $isNotTopLevelItem = !($item->post_type === 'page' && isset($item->post_parent) && $item->post_parent === 0);
        $showTopLevel = $this->args['include_top_level'];

        return ($showTopLevel || $isNotTopLevelItem) && $showInMenu;
    }


    /**
     * Adds markup to the output string
     * @param string $string Markup to add
     */
    protected function addOutput($string)
    {
        $this->output .= $string;
    }

    /**
     * Echos the output
     * @return void
     */
    public function render($echo = true, $wrapper = array())
    {
        if ($echo) {
            echo $this->output;
            return true;
        }

        return $this->output;
    }

    /**
     * Gets the item count
     * @return void
     */
    public function itemCount()
    {
        return $this->itemCount;
    }

    public function getPageId($page)
    {
        if (is_null($page)) {
            return false;
        }

        if (!is_object($page)) {
            $page = get_post($page);
        }

        if (isset($page->post_type) && $page->post_type == 'nav_menu_item') {
            return intval($page->object_id);
        }

        return $page->ID;
    }
}
