<?php

namespace Municipio\Theme;

class EventArchive extends Archive
{

    private $eventPostType = "event";
    private $dbTable;

    public function __construct()
    {
        //Setup local wpdb instance
        global $wpdb;
        $this->db = $wpdb;
        $this->db_table = $wpdb->prefix . "integrate_occasions";

        add_action('wp_ajax_nopriv_getRenderedArchivePosts', array($this,'getRenderedArchivePosts'));
        add_action('wp_ajax_getRenderedArchivePosts', array($this,'getRenderedArchivePosts'));

        //Run functions if table exists
        if ($this->db->get_var("SHOW TABLES LIKE '" . $this->db_table . "'") !== null) {
            add_action('pre_get_posts', array($this, 'filterEvents'), 100);
        }
    }

    /**
     * Filter events
     * @param  object $query object WP Query
     */
    public function filterEvents($query)
    {
        if (is_admin() || ! is_post_type_archive($this->eventPostType)) {
            return $query;
        }

        $query->set('posts_per_page', 50);

        add_filter('posts_fields', array($this, 'eventFilterSelect'));
        add_filter('posts_join', array($this, 'eventFilterJoin'));
        add_filter('posts_where', array($this, 'eventFilterWhere'), 10, 2);
        add_filter('posts_groupby', array($this, 'eventFilterGroupBy'));
        add_filter('posts_orderby', array($this, 'eventFilterOrderBy'));

        return $query;
    }

    public function getRenderedArchivePosts(){

        $getParams = $_GET['archiveGet'];
        
        $args = array(
            'post_type' => $this->eventPostType,
	    );

        $loop = $this->filterEvents(new \WP_Query($args));
        $items = [];
        if ($loop->have_posts()) {
            while ($loop->have_posts()) {
                $loop->the_post();
                $items[] = $this->getRenderedItem();
            }
        }
        
        echo json_encode($items);
        wp_die();
    }

    public function getRenderedItem() {
        
        global $post;

        $location = get_field('location');

        $data = ['href' => esc_url(add_query_arg('date', preg_replace('/\D/', '', $post->start_date), get_permalink())),
            'title' => get_the_title(), 
            'dateLang' => _x('Date', 'Event archive', 'municipio'), 
            'date' => \Municipio\Helper\Event::formatEventDate($post->start_date, $post->end_date), 
            'hasLocation' => !empty($location['title']), 
            'locationLang' => _x('Location', 'Event archive','municipio'), 
            'locationTitle' => $location['title'], 
            'postContentMode' => $post->content_mode,
            'hasPostContent' => !empty($post->content),
            'postContentTrim' => wp_trim_words($post->content, 50, ' [...]'),
            'excerpt' => apply_filters('the_excerpt', get_the_excerpt()),
            'thumbnailSource' => municipio_get_thumbnail_source(null,array(400,250))
        ];

        $template = new \Municipio\Template;
        $view = \Municipio\Helper\Template::locateTemplate('item', array(get_template_directory().'/views/partials/archive/event/'));
        $view = $template->cleanViewPath($view);

        $rendered = $template->render($view, $data);

        return $rendered;
    }

    /**
     * Select tables
     * @param  string $select Original query
     * @return string         Modified query
     */
    public function eventFilterSelect($select)
    {
        return $select . ",{$this->db_table}.start_date,{$this->db_table}.end_date,{$this->db_table}.door_time,{$this->db_table}.status,{$this->db_table}.exception_information,{$this->db_table}.content_mode,{$this->db_table}.content ";
    }

    /**
     * Join taxonomies and postmeta to sql statement
     * @param  string $join current join sql statement
     * @return string       updated statement
     */
    public function eventFilterJoin($join)
    {
        return $join . "LEFT JOIN {$this->db_table} ON ({$this->db->posts}.ID = {$this->db_table}.event_id) ";
    }

    /**
     * Add where statements
     * @param  string $where current where statement
     * @return string        updated statement
     */
    public function eventFilterWhere($where)
    {
        $from = null;
        $to = null;

        if (isset($_GET['from']) && !empty($_GET['from'])) {
            $from = sanitize_text_field($_GET['from']);
        }

        if (isset($_GET['to']) && !empty($_GET['to'])) {
            $to = sanitize_text_field($_GET['to']);
        }

        if (!is_null($from) && !is_null($to)) {
            // USE BETWEEN ON START DATE
            $where = str_replace(
                "{$this->db->posts}.post_date >= '{$from}'",
                "{$this->db_table}.start_date BETWEEN '{$from}' AND '{$to}'",
                $where
            );
            $where = str_replace(
                "AND {$this->db->posts}.post_date <= '{$to}'",
                "",
                $where
            );
        } elseif (!is_null($from) || !is_null($to)) {
            // USE FROM OR TO
            $where = str_replace("{$this->db->posts}.post_date >=", "{$this->db_table}.start_date >=", $where);
            $where = str_replace("{$this->db->posts}.post_date <=", "{$this->db_table}.end_date <=", $where);
        }

        return $where;
    }

    /**
     * Add group by statement
     * @param  string $groupby current group by statement
     * @return string          updated statement
     */
    public function eventFilterGroupBy($groupby)
    {
        return "{$this->db_table}.start_date, {$this->db_table}.end_date";
    }

    /**
     * Add group by statement
     * @param  string $groupby current group by statement
     * @return string          updated statement
     */
    public function eventFilterOrderBy($orderby)
    {
        return "{$this->db_table}.start_date ASC";
    }

}
