<?php
defined( 'ABSPATH' ) || exit;

class PworkKB {
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
        add_action('init', array($this, 'register_taxonomy'), 1);
        add_filter('cmb2_meta_boxes', array($this, 'add_article_metabox') );
        add_action('wp_ajax_pworkAddArticle', array($this, 'add_article'));
        add_action('wp_ajax_pworkDeleteArticle', array($this, 'delete_article'));
    }

    /**
	 * Register Post Type
	 */
    public function register_post_type() {
        $labels = array(
            'name'              => esc_html__( 'Pwork Knowledge Base', 'pwork' ),
            'singular_name'     => esc_html__( 'Article', 'pwork' ),
            'add_new'           => esc_html__( 'Add new article', 'pwork' ),
            'add_new_item'      => esc_html__( 'Add new article', 'pwork' ),
            'edit_item'         => esc_html__( 'Edit article', 'pwork' ),
            'new_item'          => esc_html__( 'New article', 'pwork' ),
            'view_item'         => esc_html__( 'View article', 'pwork' ),
            'search_items'      => esc_html__( 'Search articles', 'pwork' ),
            'not_found'         => esc_html__( 'No article found', 'pwork' ),
            'not_found_in_trash'=> esc_html__( 'No article found in trash', 'pwork' ),
            'parent_item_colon' => esc_html__( 'Parent article:', 'pwork' ),
            'menu_name'         => esc_html__( 'PW Articles', 'pwork' )
        );
    
        $taxonomies = array();
        $supports = array('title','thumbnail', 'author');
     
        $post_type_args = array(
            'labels'            => $labels,
            'singular_label'    => esc_html__('Article', 'pwork'),
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
            'menu_icon'         => 'dashicons-sos',
            'taxonomies'        => $taxonomies
        );
        register_post_type('pworkkb',$post_type_args);
    }

     /**
	 * Register Taxonomy
	 */
    public function register_taxonomy() {
        register_taxonomy(
            'pworkkbtags',
            'pworkkb',
            array(
                'labels' => array(
                    'name' => esc_html__( 'PW Article Tags', 'pwork' ),
                    'add_new_item' => esc_html__( 'Add new tag', 'pwork' ),
                    'edit_item'         => esc_html__( 'Edit tag', 'pwork' ),
                    'new_item_name' => esc_html__( 'New tag', 'pwork' ),
                    'parent_item' => esc_html__( 'Parent tag', 'pwork' ),
                    'parent_item_colon' => esc_html__( 'Parent tag:', 'pwork' ),
                    'search_items'      => esc_html__( 'Search tags', 'pwork' ),
                ),
                'show_ui' => true,
                'show_tagcloud' => false,
                'show_admin_column' => true,
                'show_in_nav_menus' => false,
                'hierarchical' => true,
                'query_var' => true
            )
        );
    }

    /**
	 * Add article metabox
	 */
    public function add_article_metabox( $meta_boxes ) {
        $meta_boxes['pwork_article_content_metabox'] = array(
            'id' => 'pwork_article_content_metabox',
            'title' => esc_html__( 'Content', 'pwork'),
            'object_types' => array('pworkkb'),
            'context' => 'normal',
            'priority' => 'high',
            'show_names' => false,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Content', 'pwork' ),
                    'id'      => 'pwork_article_content',
                    'type'    => 'wysiwyg',
                    'options' => array(
                        'wpautop' => true,
                        'media_buttons' => true,
                        'quicktags' => false,
                        'teeny' => true,
                    ),
                ),
            ),
        );
    
        return $meta_boxes;
    }

    /**
	 * Add Article
	 */
    public function add_article() {
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
                    echo esc_html__('You are not allowed to edit this post.', 'pwork');
                    exit();
                }
                $update_post = array(
                    'ID'           => $post_id,
                    'post_title'   => sanitize_text_field($_POST['title']),
                    'post_excerpt' => sanitize_text_field($_POST['excerpt'])
                );
                $update = wp_update_post($update_post);
                if (is_wp_error($update) ) {
                    echo esc_html__('Something went wrong.', 'pwork');
                    exit();
                } else {
                    if (isset($_POST['content']) && !empty($_POST['content'])) {
                        update_post_meta($post_id, 'pwork_article_content', wp_kses_post($_POST['content']));
                    }

                    if (isset($_POST['tag']) && $_POST['tag'] !== 0 && !empty($_POST['tag'])) {
                        $tags = json_decode( stripslashes( $_POST['tag'] ), true );
                        wp_set_post_terms( $post_id, $tags, 'pworkkbtags');
                    }

                    if ($_FILES['file']['size'] != 0) {
                        $arr_img_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/webp');
                        if (in_array($_FILES['file']['type'], $arr_img_ext)) {
                            $upload = wp_upload_bits($_FILES["file"]["name"], null, file_get_contents($_FILES["file"]["tmp_name"]));
                            $path_parts = pathinfo($_FILES["file"]["name"]);
                            $post_title = $path_parts['filename'];
                            $info = wp_check_filetype( $upload['file'] );
                            $post = [
                                'post_title' => $post_title,
                                'guid' => $upload['url'],
                                'post_mime_type' => $info['type']
                            ];
                            $attachment_id = wp_insert_attachment( $post, $upload['file'] );
                            wp_update_attachment_metadata(
                                $attachment_id,
                                wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
                            );
                            set_post_thumbnail( $post_id, $attachment_id );
                        }
                    }
                }
            } else {
                echo esc_html__('Article not found.', 'pwork');
                exit();
            }
        } else {
            $post_id = wp_insert_post(array (
                'post_title' => sanitize_text_field($_POST['title']),
                'post_type' => 'pworkkb',
                'post_status' => 'publish',
                'post_excerpt' => sanitize_text_field($_POST['excerpt'])
            ));

            if (is_wp_error($post_id) ) {
                echo esc_html__('Something went wrong.', 'pwork');
                exit();
            } else {
                if (isset($_POST['content']) && !empty($_POST['content'])) {
                    add_post_meta($post_id, 'pwork_article_content', sanitize_text_field($_POST['content']), true );
                }

                if (isset($_POST['tag']) && $_POST['tag'] !== 0 && !empty($_POST['tag'])) {
                    $tags = json_decode( stripslashes( $_POST['tag'] ), true );
                    wp_set_post_terms( $post_id, $tags, 'pworkkbtags');
                }
    
                if ($_FILES['file']['size'] != 0) {
                    $arr_img_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/webp');
                    if (in_array($_FILES['file']['type'], $arr_img_ext)) {
                        $upload = wp_upload_bits($_FILES["file"]["name"], null, file_get_contents($_FILES["file"]["tmp_name"]));
                        $path_parts = pathinfo($_FILES["file"]["name"]);
                        $post_title = $path_parts['filename'];
                        $info = wp_check_filetype( $upload['file'] );
                        $post = [
                            'post_title' => $post_title,
                            'guid' => $upload['url'],
                            'post_mime_type' => $info['type']
                        ];
                        $attachment_id = wp_insert_attachment( $post, $upload['file'] );
                        wp_update_attachment_metadata(
                            $attachment_id,
                            wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
                        );
                        set_post_thumbnail( $post_id, $attachment_id );
                    }
                }
            }
        }

        echo 'done';
        wp_die();
    }

    /**
	 * Delete Article
	 */
    public function delete_article(){
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
	 * Count Posts
	 */
    public static function count_posts(){
        $posts = get_posts(array(
            'post_status' => 'publish',
            'post_type' => 'pworkkb',
            'posts_per_page'  => 9999
        ));
        return count($posts);
    }
}

/**
 * Returns the main instance of the class
 */
function PworkKB() {  
	return PworkKB::instance();
}
// Global for backwards compatibility
$GLOBALS['PworkKB'] = PworkKB();