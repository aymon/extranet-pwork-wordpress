<?php
defined( 'ABSPATH' ) || exit;

class PworkAnns {
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
        add_filter('cmb2_meta_boxes', array($this, 'add_announcement_metabox') );
        add_action('wp_ajax_pworkLoadMoreAnns', array($this, 'load_more'));
        add_action('wp_ajax_pworkAddAnn', array($this, 'add_announcement'));
        add_action('wp_ajax_pworkDeleteAnn', array($this, 'delete_announcement'));
        add_action('wp_ajax_pworkAnnAddComment', array($this, 'add_comment'));
        add_action('publish_pworkanns', array($this, 'send_notification'), 10, 2 );
    }

    /**
	 * Register Post Type
	 */
    public function register_post_type() {
        $labels = array(
            'name'              => esc_html__( 'Pwork News', 'pwork' ),
            'singular_name'     => esc_html__( 'Post', 'pwork' ),
            'add_new'           => esc_html__( 'Add new post', 'pwork' ),
            'add_new_item'      => esc_html__( 'Add new post', 'pwork' ),
            'edit_item'         => esc_html__( 'Edit post', 'pwork' ),
            'new_item'          => esc_html__( 'New post', 'pwork' ),
            'view_item'         => esc_html__( 'View post', 'pwork' ),
            'search_items'      => esc_html__( 'Search posts', 'pwork' ),
            'not_found'         => esc_html__( 'No post found', 'pwork' ),
            'not_found_in_trash'=> esc_html__( 'No post found in trash', 'pwork' ),
            'parent_item_colon' => esc_html__( 'Parent post:', 'pwork' ),
            'menu_name'         => esc_html__( 'PW News', 'pwork' )
        );
    
        $taxonomies = array();
        $supports = array('title','excerpt','thumbnail', 'author', 'comments');
     
        $post_type_args = array(
            'labels'            => $labels,
            'singular_label'    => esc_html__('Post', 'pwork'),
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
            'menu_icon'         => 'dashicons-megaphone',
            'taxonomies'        => $taxonomies
        );
        register_post_type('pworkanns',$post_type_args);
    }

     /**
	 * Register Taxonomy
	 */
    public function register_taxonomy() {
        register_taxonomy(
            'pworkannstags',
            'pworkanns',
            array(
                'labels' => array(
                    'name' => esc_html__( 'PW News Tags', 'pwork' ),
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
	 * Add announcement metabox
	 */
    public function add_announcement_metabox( $meta_boxes ) {
        $meta_boxes['pwork_announcement_metabox'] = array(
            'id' => 'pwork_announcement_metabox',
            'title' => esc_html__( 'Card Style', 'pwork'),
            'object_types' => array('pworkanns'),
            'context' => 'side',
            'priority' => 'default',
            'show_names' => false,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Card Style', 'pwork' ),
                    'id'      => 'pwork_announcement_card',
                    'type' => 'select',
                    'options' => array(
                        'default' => esc_html__( 'Default', 'pwork' ),
                        'primary'   => esc_html__( 'Primary', 'pwork' ),
                        'secondary'   => esc_html__( 'Secondary', 'pwork' ),
                        'info'   => esc_html__( 'Info', 'pwork' ),
                        'danger'   => esc_html__( 'Danger', 'pwork' ),
                        'warning'   => esc_html__( 'Warning', 'pwork' ),
                        'success'   => esc_html__( 'Success', 'pwork' ),
                    ),
                    'attributes' => array(
                        'autocomplete' => 'off'
                    ),
                    'default' => 'default',
                ),
            ),
        );

        $meta_boxes['pwork_announcement_content_metabox'] = array(
            'id' => 'pwork_announcement_content_metabox',
            'title' => esc_html__( 'Content', 'pwork'),
            'object_types' => array('pworkanns'),
            'context' => 'normal',
            'priority' => 'high',
            'show_names' => false,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Content', 'pwork' ),
                    'id'      => 'pwork_announcement_content',
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
	 * Load More Announcements
	 */
    public function load_more() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $slug =  PworkSettings::get_option('slug', 'pwork');
        $limit = (int) PworkSettings::get_option('anns_limit', 12);
        $offset = 0;
        if (isset($_POST['offset']) && !empty($_POST['offset'])) {
            $offset = (int) $_POST['offset'];
        }
        $newofset = $offset + $limit;
        $args = array(
            'post_status' => 'publish',
            'post_type' => 'pworkanns',
            'posts_per_page'  => $limit,
            'offset'  => $offset,
            'order'  => 'DESC',
            'orderby'  => 'post_date'
        );
        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $args['s'] = sanitize_text_field($_POST['search']);
        }
        if (isset($_POST['tag']) && !empty($_POST['tag'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'pworkannstags',
                    'field' => 'term_id',
                    'terms' => (int) $_POST['tag'],
                ),
            );
        }
        $query = new WP_Query($args);
        $visible_posts = $query->post_count;
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) : $query->the_post();
            $postID = get_the_ID();
            $authorID = (int) get_post_field('post_author', $postID);
            $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $authorID;
            $ann_url = get_site_url() . '/' . $slug . '/?page=announcements-single&ID=' . $postID;
            $terms = get_the_terms( $postID, 'pworkannstags' );
            $style = get_post_meta( $postID, 'pwork_announcement_card', true );
            $text_color = '';
            $bg_color = '';
            if ($style == 'primary') {
            $text_color = 'text-white';
            $bg_color = 'bg-primary';
            } else if ($style == 'secondary') {
            $text_color = 'text-white';
            $bg_color = 'bg-secondary';
            } else if ($style == 'info') {
            $text_color = 'text-white';
            $bg_color = 'bg-info';
            } else if ($style == 'danger') {
            $text_color = 'text-white';
            $bg_color = 'bg-danger';
            } else if ($style == 'warning') {
            $text_color = 'text-dark';
            $bg_color = 'bg-warning';
            } else if ($style == 'success') {
            $text_color = 'text-white';
            $bg_color = 'bg-success';
            }
            $content = get_post_meta( $postID, 'pwork_announcement_content', true );
            ?>
            <div class="col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3">
                    <div class="card post-card mb-4 <?php echo esc_attr($text_color . ' ' . $bg_color); ?>">
                      <?php 
                      if (has_post_thumbnail()) {
                          $thumbnail_id = get_post_thumbnail_id();
                          $thumbnail_src = wp_get_attachment_image_src($thumbnail_id, 'large', true);
                          echo '<a href="' . esc_url($ann_url) . '"><img class="card-img-top" src="' . esc_url($thumbnail_src[0]) . '"></a>';
                      } ?>
                      <div class="card-body">
                        <span class="card-date <?php echo esc_attr($text_color); ?>"><a href="<?php echo esc_url($ann_url); ?>" style="color:inherit"><?php echo esc_html(get_the_date(get_option('date_format'))); ?></a></span>
                        <h4 class="card-title <?php echo esc_attr($text_color); ?>"><a href="<?php echo esc_url($ann_url); ?>" style="color:inherit"><?php the_title(); ?></a></h4>
                        <p class="card-text"><?php the_excerpt(); ?></p>
                        <?php 
                        if (!empty($terms) && is_array($terms)) {
                          echo '<div class="mt-3">';
                          foreach($terms as $term) {
                            echo '<span class="badge bg-dark me-1 mt-1"><a href="' . esc_url(get_site_url() . '/' . $slug . '/?page=announcements&tagID=' . $term->term_id) . '">' . esc_html($term->name) . '</a></span>';
                          }
                          echo '</div>';
                        } 
                        ?>
                      </div>
                      <div class="pwork-card-footer justify-content-start">
                      <div class="d-flex align-items-center"><a href="<?php echo esc_url($user_profile_url); ?>"><?php echo get_avatar($authorID, 80); ?></a><strong class="ms-2"><?php echo esc_html(get_the_author_meta('display_name')); ?></strong></div>
                      </div>
                    </div>
                  </div>
            <?php
            endwhile;
            if ($limit == $visible_posts) { ?>
            <div class="col-12 mt-2">
                <button id="pwork-load-more-anns" type="button" class="btn btn-lg btn-primary w-100" data-offset="<?php echo esc_attr($newofset); ?>"><?php echo esc_html__( 'LOAD MORE', 'pwork' ); ?></button>
            </div>
            <?php }
            wp_reset_postdata();
        }
        wp_die();
    }

    /**
	 * Add Announcement
	 */
    public function add_announcement() {
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
                    if (isset($_POST['card']) && !empty($_POST['card'])) {
                        update_post_meta($post_id, 'pwork_announcement_card', sanitize_text_field($_POST['card']));
                    }
                    
                    if (isset($_POST['content']) && !empty($_POST['content'])) {
                        update_post_meta($post_id, 'pwork_announcement_content', wp_kses_post($_POST['content']));
                    }

                    if (isset($_POST['tag']) && $_POST['tag'] !== 0 && !empty($_POST['tag'])) {
                        $tags = json_decode( stripslashes( $_POST['tag'] ), true );
                        wp_set_post_terms( $post_id, $tags, 'pworkannstags');
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
                echo esc_html__('Announcement not found.', 'pwork');
                exit();
            }
        } else {
            $post_id = wp_insert_post(array (
                'post_title' => sanitize_text_field($_POST['title']),
                'post_type' => 'pworkanns',
                'post_status' => 'publish',
                'post_excerpt' => sanitize_text_field($_POST['excerpt'])
            ));

            if (is_wp_error($post_id) ) {
                echo esc_html__('Something went wrong.', 'pwork');
                exit();
            } else {
                if (isset($_POST['card']) && !empty($_POST['card'])) {
                    add_post_meta($post_id, 'pwork_announcement_card', sanitize_text_field($_POST['card']), true );
                }

                if (isset($_POST['content']) && !empty($_POST['content'])) {
                    add_post_meta($post_id, 'pwork_announcement_content', sanitize_text_field($_POST['content']), true );
                }

                if (isset($_POST['tag']) && $_POST['tag'] !== 0 && !empty($_POST['tag'])) {
                    $tags = json_decode( stripslashes( $_POST['tag'] ), true );
                    wp_set_post_terms( $post_id, $tags, 'pworkannstags');
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
	 * Send notification
	 */
    public function send_notification($id, $post){
        $slug = PworkSettings::get_option('slug', 'pwork'); 
        $blog_name = esc_html(get_bloginfo( 'name' ));
        $url = get_site_url() . '/' . $slug . '/?page=announcements-single&ID=' . $id;
        $subject = esc_html__('New announcement at', 'pwork') . ' ' . $blog_name;
        $message = '<p><strong>' . esc_html__('A new announcement has been added.', 'pwork') . '</strong></p><p><a href="' . esc_url($url) . '"><strong>' . esc_html__('Click here to visit the announcements page.', 'pwork') . '</strong></a></p>';

        Pwork::add_notification('new_announcement', false);
        Pwork::send_email('new_announcement', $subject, $message, false);
    }

    /**
	 * Delete Announcement
	 */
    public function delete_announcement(){
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
	 * Add Comment
	 */
    public function add_comment(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $current_user = wp_get_current_user();
        $post_id = (int) $_POST['postid'];
        if (isset($_POST['comment']) && !empty($_POST['comment'])) {
            if (isset($_POST['postid']) && !empty($_POST['postid'])) {
                if ( comments_open($post_id) ) {
                    $data = array(
                        'comment_post_ID'      => $post_id,
                        'comment_content'      => wp_kses_post($_POST['comment']),
                        'user_id'              => $current_user->ID,
                        'comment_author'       => $current_user->user_login,
                        'comment_author_email' => $current_user->user_email,
                        'comment_author_url'   => $current_user->user_url
                    );
                    $comment_id = wp_insert_comment( $data );
                    if ( is_wp_error( $comment_id ) ) {
                        echo esc_html__('Something went wrong.', 'pwork');
                        exit();
                    }
                } else {
                    echo esc_html__('Comments are closed.', 'pwork');
                    exit();
                }          
            } else {
                echo esc_html__('Post ID is required.', 'pwork');
                exit();
            }
        } else {
            echo esc_html__('The comment field cannot be left blank.', 'pwork');
            exit();
        }
        echo 'done';
        wp_die();
    }

    /**
	 * Count Posts
	 */
    public static function count_posts(){
        $posts = get_posts(array(
            'post_status' => 'publish',
            'post_type' => 'pworkanns',
            'posts_per_page'  => 9999
        ));
        return count($posts);
    }
}

/**
 * Returns the main instance of the class
 */
function PworkAnns() {  
	return PworkAnns::instance();
}
// Global for backwards compatibility
$GLOBALS['PworkAnns'] = PworkAnns();