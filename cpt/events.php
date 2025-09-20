<?php
defined( 'ABSPATH' ) || exit;

class PworkEvents {
    /**
	 * The single instance of the class
	 */
	protected static $_instance = null;

    /**
	 * Main Instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
	 * Constructor
	 */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'), 1);
        add_filter('cmb2_meta_boxes', array($this, 'add_event_metabox') );
        add_action('pwork_body_end', array($this, 'get_events'));
        add_action('wp_ajax_pworkAddEvent', array($this, 'add_event'));
        add_action('wp_ajax_pworkDeleteEvent', array($this, 'delete_event'));
        add_filter('manage_edit-pworkevents_columns', array($this, 'admin_column'), 5);
        add_action('manage_posts_custom_column', array($this, 'admin_row'), 5, 2);
        add_action('publish_pworkevents', array($this, 'send_notification'), 10, 2 );
    }

    /**
	 * Register Post Type
	 */
    public function register_post_type() {
        $labels = array(
            'name'              => esc_html__( 'Pwork Events', 'pwork' ),
            'singular_name'     => esc_html__( 'Event', 'pwork' ),
            'add_new'           => esc_html__( 'Add new event', 'pwork' ),
            'add_new_item'      => esc_html__( 'Add new event', 'pwork' ),
            'edit_item'         => esc_html__( 'Edit event', 'pwork' ),
            'new_item'          => esc_html__( 'New event', 'pwork' ),
            'view_item'         => esc_html__( 'View event', 'pwork' ),
            'search_items'      => esc_html__( 'Search events', 'pwork' ),
            'not_found'         => esc_html__( 'No event found', 'pwork' ),
            'not_found_in_trash'=> esc_html__( 'No event found in trash', 'pwork' ),
            'parent_item_colon' => esc_html__( 'Parent event:', 'pwork' ),
            'menu_name'         => esc_html__( 'PW Events', 'pwork' )
        );
    
        $taxonomies = array();
        $supports = array('title','author');
     
        $post_type_args = array(
            'labels'            => $labels,
            'singular_label'    => esc_html__('Event', 'pwork'),
            'public'            => false,
            'exclude_from_search' => true,
            'show_ui'           => true,
            'show_in_nav_menus' => false,
            'publicly_queryable'=> true,
            'query_var'         => true,
            'capability_type'   => 'post',
            'capabilities' => array(
                'edit_post'          => 'manage_options',
                'read_post'          => 'manage_options',
                'delete_post'        => 'manage_options',
                'edit_posts'         => 'manage_options',
                'edit_others_posts'  => 'manage_options',
                'delete_posts'       => 'manage_options',
                'publish_posts'      => 'manage_options',
                'read_private_posts' => 'manage_options'
            ),
            'has_archive'       => false,
            'hierarchical'      => false,
            'supports'          => $supports,
            'menu_position'     => 27,
            'menu_icon'         => 'dashicons-calendar-alt',
            'taxonomies'        => $taxonomies
        );
        register_post_type('pworkevents',$post_type_args);
    }

    /**
	 * Add event metabox
	 */
    public function add_event_metabox( $meta_boxes ) {
        $meta_boxes['pwork_event_metabox'] = array(
            'id' => 'pwork_event_metabox',
            'title' => esc_html__( 'Event Info', 'pwork'),
            'object_types' => array('pworkevents'),
            'context' => 'normal',
            'priority' => 'default',
            'show_names' => true,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Start Date', 'pwork' ),
                    'id'      => 'pwork_event_start',
                    'type'    => 'text',
                    'attributes' => array(
                        'type' => 'datetime-local',
                        'autocomplete' => 'off'
                    )
                ),
                array(
                    'name'    => esc_html__( 'End Date', 'pwork' ),
                    'id'      => 'pwork_event_end',
                    'type'    => 'text',
                    'attributes' => array(
                        'type' => 'datetime-local',
                        'autocomplete' => 'off'
                    )
                ),
                array(
                    'name'    => esc_html__( 'Color', 'pwork' ),
                    'id'      => 'pwork_event_color',
                    'type'    => 'colorpicker',
                    'default' => '#6658ea'
                ),
                array(
                    'name'    => esc_html__( 'URL (Optional)', 'pwork' ),
                    'id'      => 'pwork_event_url',
                    'type'    => 'text',
                    'attributes' => array(
                        'type' => 'url',
                        'autocomplete' => 'off'
                    ),
                ),
                array(
                    'name'    => esc_html__( 'All Day Event', 'pwork' ),
                    'id'      => 'pwork_event_all_day',
                    'type' => 'radio_inline',
                    'options' => array(
                        'true' => esc_html__( 'Yes', 'pwork' ),
                        'false'   => esc_html__( 'No', 'pwork' )
                    ),
                    'attributes' => array(
                        'autocomplete' => 'off'
                    ),
                    'default' => 'false',
                ),
            ),
        );
    
        return $meta_boxes;
    }

    /**
	 * Get Events
	 */
    public function get_events(){ 
        if (!isset($_GET['page']) || (isset($_GET['page']) && $_GET['page'] == 'events')) {
        $args = array(
            'post_status' => 'publish',
            'post_type' => 'pworkevents',
            'posts_per_page'  => 99999,
            'order'  => 'DESC',
            'orderby'  => 'post_date'
        );
        $query = new WP_Query($args);
        $events = '[]';
        if ( $query->have_posts() ) {
            $events = '[';
            while ( $query->have_posts() ) : $query->the_post();
            $postID = get_the_ID();
            $title = get_the_title();
            $url = get_post_meta( $postID, 'pwork_event_url', true );
            $start = get_post_meta( $postID, 'pwork_event_start', true ); 
            $end = get_post_meta( $postID, 'pwork_event_end', true ); 
            $color = get_post_meta( $postID, 'pwork_event_color', true ); 
            $allday = get_post_meta( $postID, 'pwork_event_all_day', true );
            $events .= "{title: '" . $title . "',start: '" . $start . "',end: '" . $end . "',backgroundColor: '" . $color . "',borderColor: '" . $color . "',allDay:" . $allday . ",url:'" . $url . "'},";

            endwhile;
            $events = $events . ']';
        }
        wp_reset_postdata();
        ?>
        <script>
            var pworkCalendarEvents = <?php echo $events; ?>;
        </script>
    <?php }
    }

    /**
	 * Add Event
	 */
    public function add_event() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }

        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $post = get_post((int) $_POST['id']);
            if ($post) {
                $post_id = $post->ID;
                $authorID = (int) get_post_field('post_author', $post_id);
                $userID = (int) get_current_user_id();
                if (current_user_can('administrator') || current_user_can('editor')) {
                } else if ($authorID != $userID) {
                    echo esc_html__('You are not allowed to edit this event.', 'pwork');
                    exit();
                }
                $update_post = array(
                    'ID'           => $post_id,
                    'post_title'   => sanitize_text_field($_POST['title'])
                );
                $update = wp_update_post($update_post);
                if (is_wp_error($update) ) {
                    echo esc_html__('Something went wrong.', 'pwork');
                    exit();
                } else {
                    if (isset($_POST['start']) && !empty($_POST['start'])) {
                        update_post_meta($post_id, 'pwork_event_start', sanitize_text_field($_POST['start']));
                    }
            
                    if (isset($_POST['end']) && !empty($_POST['end'])) {
                        update_post_meta($post_id, 'pwork_event_end', sanitize_text_field($_POST['end']));
                    }
            
                    if (isset($_POST['color']) && !empty($_POST['color'])) {
                        update_post_meta($post_id, 'pwork_event_color', sanitize_text_field($_POST['color']));
                    }
            
                    if (isset($_POST['url']) && !empty($_POST['url'])) {
                        update_post_meta($post_id, 'pwork_event_url', esc_url($_POST['url']));
                    }
            
                    if (isset($_POST['allday']) && !empty($_POST['allday'])) {
                        update_post_meta($post_id, 'pwork_event_all_day', sanitize_text_field($_POST['allday']));
                    }
                }
            } else {
                echo esc_html__('Event not found.', 'pwork');
                exit();
            }
        } else {
            $post_id = wp_insert_post(array (
                'post_title' => sanitize_text_field($_POST['title']),
                'post_type' => 'pworkevents',
                'post_status' => 'publish'
            ));

            if (is_wp_error($post_id) ) {
                echo esc_html__('Something went wrong.', 'pwork');
                exit();
            } else {
                if (isset($_POST['start']) && !empty($_POST['start'])) {
                    add_post_meta($post_id, 'pwork_event_start', sanitize_text_field($_POST['start']), true );
                }
        
                if (isset($_POST['end']) && !empty($_POST['end'])) {
                    add_post_meta($post_id, 'pwork_event_end', sanitize_text_field($_POST['end']), true );
                }
        
                if (isset($_POST['color']) && !empty($_POST['color'])) {
                    add_post_meta($post_id, 'pwork_event_color', sanitize_text_field($_POST['color']), true );
                }
        
                if (isset($_POST['url']) && !empty($_POST['url'])) {
                    add_post_meta($post_id, 'pwork_event_url', esc_url($_POST['url']), true );
                }
        
                if (isset($_POST['allday']) && !empty($_POST['allday'])) {
                    add_post_meta($post_id, 'pwork_event_all_day', sanitize_text_field($_POST['allday']), true );
                }
            }
        }

        echo 'done';
        wp_die();
    }

    /**
	 * Delete Event
	 */
    public function delete_event(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $authorID = (int) get_post_field('post_author', $_POST['id']);
        $userID = (int) get_current_user_id();
        if ($authorID === $userID || current_user_can('administrator') || current_user_can('editor')) {
            $deletePost = wp_delete_post($_POST['id'], true);
            if (is_wp_error($deletePost) ) {
                echo esc_html__('Something went wrong.', 'pwork');
            } else {
                echo 'done';
            }
        } else {
            echo esc_html__('You are not allowed to delete this file.', 'pwork');
        }
        wp_die();
    }

    /**
	 * Count events
	 */
    public static function count_events(){
        $posts = get_posts(array(
            'post_status' => 'publish',
            'post_type' => 'pworkevents',
            'posts_per_page'  => 9999
        ));
        return count($posts);
    }

    /**
	 * Count My Events
	 */
    public static function count_my_events(){
        $events = get_posts(array(
            'post_status' => 'publish',
            'post_type' => 'pworkevents',
            'author__in' => get_current_user_id(),
            'posts_per_page'  => 9999
        ));
        return count($events);
    }

    /**
	 * Send notification
	 */
    public function send_notification($id, $post){
        $slug = PworkSettings::get_option('slug', 'pwork'); 
        $blog_name = esc_html(get_bloginfo( 'name' ));
        $url = get_site_url() . '/' . $slug . '/?page=events';
        $subject = esc_html__('New event at', 'pwork') . ' ' . $blog_name;
        $message = '<p><strong>' . esc_html__('A new event has been added.', 'pwork') . '</strong></p><p><a href="' . esc_url($url) . '"><strong>' . esc_html__('Click here to visit the events page.', 'pwork') . '</strong></a></p>';

        Pwork::add_notification('new_event', false);
        Pwork::send_email('new_event', $subject, $message, false);
    }

    /**
	 * Add custom admin table column
	 */
    public function admin_column($defaults){
        $defaults['pwork_event_start'] = esc_html__( 'Start Date', 'pwork' );
        $defaults['pwork_event_end'] = esc_html__( 'End Date', 'pwork' );
        return $defaults;
    }

    /**
	 * Add custom admin table row
	 */
    public function admin_row($column_name, $post_id){
        if($column_name === 'pwork_event_start'){
            $start = get_post_meta($post_id, 'pwork_event_start', true );
            echo esc_html(date(get_option('date_format') . ' ' .  get_option('time_format'), strtotime($start)));
        } 
        if($column_name === 'pwork_event_end'){
            $end = get_post_meta($post_id, 'pwork_event_end', true );
            echo esc_html(date(get_option('date_format') . ' ' .  get_option('time_format'), strtotime($end)));
        }    
    }
}

/**
 * Returns the main instance of the class
 */
function PworkEvents() {  
	return PworkEvents::instance();
}
// Global for backwards compatibility
$GLOBALS['PworkEvents'] = PworkEvents();