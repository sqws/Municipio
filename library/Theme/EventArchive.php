<?php

namespace Municipio\Theme;

class EventArchive extends Archive
{

    private $eventPostType = "event";
    private $dbTable;
    private $postPerPage = 50;

    public function __construct()
    {
        //Setup local wpdb instance
        global $wpdb;
        $this->db = $wpdb;
        $this->db_table = $wpdb->prefix . "integrate_occasions";

        add_action('wp_ajax_nopriv_getRenderedArchivePosts', array($this,'getRenderedArchivePosts'));
        add_action('wp_ajax_getRenderedArchivePosts', array($this,'getRenderedArchivePosts'));

        add_action('wp_ajax_nopriv_getPagesLeft', array($this,'getPagesLeft'));
        add_action('wp_ajax_getPagesLeft', array($this,'getPagesLeft'));

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
        if ( ! is_admin() || is_post_type_archive($this->eventPostType) || wp_doing_ajax()) {

            $query->set('posts_per_page', $this->postPerPage);

            add_filter('posts_fields', array($this, 'eventFilterSelect'));
            add_filter('posts_join', array($this, 'eventFilterJoin'));
            add_filter('posts_where', array($this, 'eventFilterWhere'), 10, 2);
            add_filter('posts_groupby', array($this, 'eventFilterGroupBy'));
            add_filter('posts_orderby', array($this, 'eventFilterOrderBy'));

        }

        return $query;
    }

    public function getRenderedArchivePosts(){

        parse_str($_GET['archiveGet'], $getArray);

        $date = date('Y-m-d');

        if(array_key_exists('from', $getArray) && !empty($getArray['from'])){
            $_GET['from'] = $getArray['from'];
        } else {
            $_GET['from'] = $date;
        }

        $_GET['s'] = (array_key_exists('s',$getArray)) ? $getArray['s'] : '';
        $_GET['to'] = (array_key_exists('to',$getArray)) ? $getArray['to'] : '';
        $_GET['filter'] = (array_key_exists('filter',$getArray)) ? $getArray['filter'] : '';
    
        $params = array(
            'post_type'         => 'event',
            's'                 => $getArray['s'],
            'posts_per_page'    => $this->postPerPage,
        );


        if(array_key_exists('filter', $getArray) && !empty($getArray['filter'])){
            $params['tax_query'] = array(
                    array(
                        'taxonomy'  => 'event_categories',
                        'field'     => 'slug',
                        'terms'      => $getArray['filter']['event_categories']
                    )
                );
        }

        $page = 0;

        if(array_key_exists('page', $getArray) && !empty($getArray['page'])){
            $page = intval($getArray['page']);
            $params['paged'] = $page;
        }
        
        $WpQuery = new \WP_Query($params);
        $filteredQuery = $this->filterEvents($WpQuery);
        $maxPages = $filteredQuery->max_num_pages;
        $pagesLeft = $maxPages - $page;

        $data = array(
            'items' => array(),
            'pagesLeft' => 1,
        );

        if ($filteredQuery->have_posts()) {
            while ($filteredQuery->have_posts()) {
                $filteredQuery->the_post();
                $data['items'][] = $this->getRenderedItem();
            }
            wp_reset_postdata();
        } else {
            $data['items'][] = $this->getRenderedNoEvent();
        }

        if($pagesLeft < 2){
            $data['pagesLeft'] = 0;
        }

        echo json_encode($data);

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

        $rendered = $template->getRendered($view, $data);

        return $rendered;
    }

    public function getRenderedNoEvent() {

        $template = new \Municipio\Template;
        $view = \Municipio\Helper\Template::locateTemplate('no-events', array(get_template_directory().'/views/partials/archive/event/'));
        $view = $template->cleanViewPath($view);

        $rendered = $template->getRendered($view, $data);

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
