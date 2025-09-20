<?php
defined( 'ABSPATH' ) || exit;

class PworkForum {
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
        add_filter('cmb2_meta_boxes', array($this, 'add_forum_metabox') );
        add_action('pworkforumtags_add_form_fields', array($this, 'add_tag_color_field'));
        add_action('pworkforumtags_edit_form_fields', array($this, 'edit_tag_color_field'));
        add_action('edited_term', array($this, 'update_term'));
        add_action('created_term', array($this, 'update_term'));
        add_action('publish_pworkforum', array($this, 'update_latest_activity'), 10, 2 );
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_ajax_pworkSearchTopic', array($this, 'search_topic'));
        add_action('wp_ajax_pworkAddTopic', array($this, 'add_topic'));
        add_action('wp_ajax_pworkDeleteTopic', array($this, 'delete_topic'));
        add_action('wp_ajax_pworkAddComment', array($this, 'add_comment'));
        add_action('wp_ajax_pworkAddReply', array($this, 'add_reply'));
        add_action('wp_ajax_pworkDeleteComment', array($this, 'delete_comment'));
        add_action('publish_pworkforum', array($this, 'send_notification'), 10, 2 );
    }

    /**
	 * Register Post Type
	 */
    public function register_post_type() {
        $labels = array(
            'name'              => esc_html__( 'Pwork Topics', 'pwork' ),
            'singular_name'     => esc_html__( 'Topic', 'pwork' ),
            'add_new'           => esc_html__( 'Add new topic', 'pwork' ),
            'add_new_item'      => esc_html__( 'Add new topic', 'pwork' ),
            'edit_item'         => esc_html__( 'Edit topic', 'pwork' ),
            'new_item'          => esc_html__( 'New topic', 'pwork' ),
            'view_item'         => esc_html__( 'View topic', 'pwork' ),
            'search_items'      => esc_html__( 'Search topics', 'pwork' ),
            'not_found'         => esc_html__( 'No topic found', 'pwork' ),
            'not_found_in_trash'=> esc_html__( 'No topic found in trash', 'pwork' ),
            'parent_item_colon' => esc_html__( 'Parent topic:', 'pwork' ),
            'menu_name'         => esc_html__( 'PW Forum', 'pwork' )
        );
    
        $taxonomies = array();
        $supports = array('title','comments','author');
     
        $post_type_args = array(
            'labels'            => $labels,
            'singular_label'    => esc_html__('Topic', 'pwork'),
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
            'menu_icon'         => 'dashicons-groups',
            'taxonomies'        => $taxonomies
        );
        register_post_type('pworkforum',$post_type_args);
    }

    /**
	 * Register Taxonomy
	 */
    public function register_taxonomy() {
        register_taxonomy(
            'pworkforumtags',
            'pworkforum',
            array(
                'labels' => array(
                    'name' => esc_html__( 'PW Forum Tags', 'pwork' ),
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
	 * Colorpicker field for custom taxonomy field
	 */
    public function admin_scripts( $hook ) {  
        if ('term.php' == $hook || 'edit-tags.php' == $hook) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('pwork-tag-colorpicker', PWORK_PLUGIN_URL . 'js/admin-colorpicker.js', array('jquery', 'wp-color-picker'), PWORK_VERSION, true);
        }
    }

    public function add_tag_color_field() {
        ?>
        <div class="form-field">
			<label for="term-pwork_tag_color"><?php echo esc_html__('Badge color', 'pwork'); ?></label>
            <input type="text" id="term-pwork_tag_color" name="pwork_tag_color" value="" autocomplete="off" />
		</div>
        <?php
    }
    
    public function edit_tag_color_field($term) {
        $color = get_term_meta( $term->term_id, 'pwork_tag_color', true );
        ?>
            <tr class="form-field">
                <th><label for="term-pwork_tag_color"><?php echo esc_html__('Badge color', 'pwork'); ?></label></th>
                <td><input type="text" id="term-pwork_tag_color" name="pwork_tag_color" value="<?php echo esc_attr( $color ) ?>" autocomplete="off" /></td>
            </tr>
        <?php
    }

    public function update_term( $term_id) {
        $color = ! empty( $_POST[ 'pwork_tag_color' ] ) ? $_POST[ 'pwork_tag_color' ] : '';
        update_term_meta( $term_id, 'pwork_tag_color', sanitize_hex_color( $color ) ); 
    }

    /**
	 * Update latest activity of the topic
	 */
    public function update_latest_activity($post_id, $post) {
        update_post_meta($post_id, 'pwork_last_activity', current_time( 'U' ));
    }

    /**
	 * Add forum metabox
	 */
    public function add_forum_metabox( $meta_boxes ) {
        $meta_boxes['pwork_forum_content_metabox'] = array(
            'id' => 'pwork_forum_content_metabox',
            'title' => esc_html__( 'Content', 'pwork'),
            'object_types' => array('pworkforum'),
            'context' => 'normal',
            'priority' => 'high',
            'show_names' => false,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Content', 'pwork' ),
                    'id'      => 'pwork_forum_content',
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
	 * Search Forum
	 */
    public function search_topic() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $slug = PworkSettings::get_option('slug', 'pwork');

        $args = array(
            'post_status' => 'publish',
            'post_type' => 'pworkforum',
            'posts_per_page'  => 99999,
            'meta_key' => 'pwork_last_activity',
            'order'  => 'DESC',
            'orderby'  => 'meta_value'
        );

        if (isset($_POST['tag']) && !empty($_POST['tag'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'pworkforumtags',
                    'field' => 'term_id',
                    'terms' => (int) $_POST['tag'],
                ),
            );
        }

        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $args['s'] = sanitize_text_field($_POST['search']);
        }

        $topic_query = new WP_Query($args);
        if ( $topic_query->have_posts() ) {
            while ( $topic_query->have_posts() ) : $topic_query->the_post();
                $postID = get_the_ID();
                $authorID = (int) get_post_field('post_author', $postID);
                $topic_url = get_site_url() . '/' . $slug . '/?page=forum&topicID=' . $postID;
                $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $authorID;
                $last_activity = get_post_meta($postID, 'pwork_last_activity', true );
                $terms = get_the_terms($postID, 'pworkforumtags'); 
                $badges = '';
                if ($terms) {
                    foreach($terms as $term) {
                        $color = get_term_meta( $term->term_id, 'pwork_tag_color', true );
                        if (empty($color )) {
                            $color = '#8592a3';
                        }
                        $badges = '<span class="badge me-1 mt-1" style="background-color:' . esc_attr($color) . '"><a href="' . esc_url(get_site_url() . '/' . $slug . '/?page=forum-tag&tagID=' . $term->term_id) . '">' . esc_html($term->name) . '</a></span>' . $badges;
                    }
                }
                ?>
                <tr>
                    <td class="w-100">
                        <a href="<?php echo esc_url($topic_url); ?>" class="d-block">
                            <strong class="pwork-topic-title text-truncate">
                                <?php the_title(); ?>
                                <?php if (!comments_open($postID)) {
                                    echo '<span>('  . esc_html__('Closed', 'pwork') .')</span>';
                                } ?>
                            </strong>
                        </a>
                        <?php echo wp_kses_post($badges); ?> 
                        <div class="d-block d-md-none">
                            <small class="d-block mt-2 mb-1"><?php echo esc_html__('Posted by', 'pwork') . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a> ' . esc_html__('on', 'pwork') . ' ' . esc_html(get_the_date(get_option('date_format'))); ?></small>
                            <small class="d-block">
                            <?php
                                if ($last_activity && !empty($last_activity)) { 
                                    echo esc_html__('Last Activity:', 'pwork') . ' ' . human_time_diff( $last_activity, current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'pwork' );
                                } else {
                                    echo esc_html__('Last Activity:', 'pwork') . ' ' . human_time_diff( get_the_date('U'), current_time( 'U' ) ) . ' ' . esc_html__( 'ago', 'pwork' );
                                }
                                ?>
                            </small>
                        </div>
                    </td>
                    <td class="text-center">
                    <?php echo esc_html(get_comment_count($postID)['approved']); ?>
                    </td>
                    <td class="d-none d-md-table-cell text-center">
                        <small>
                        <?php
                        if ($last_activity && !empty($last_activity)) { 
                            echo human_time_diff( $last_activity, current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'pwork' );
                        } else {
                            echo human_time_diff( get_the_date('U'), current_time( 'U' ) ) . ' ' . esc_html__( 'ago', 'pwork' );
                        }
                        ?>
                        </small>
                    </td>
                    <td class="d-none d-md-table-cell text-end">
                        <small><?php echo esc_html__('Posted by', 'pwork') . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a><br>' . esc_html__('on', 'pwork') . ' ' . esc_html(get_the_date(get_option('date_format'))); ?></small>
                    </td>
                </tr>
                <?php 
            endwhile;
            wp_reset_postdata();
        } else {
        echo '<tr><td colspan="4"><div class="alert alert-warning m-0">' . esc_html__( 'Nothing found.', 'pwork' ) . '</div></td></tr>';
        }
        wp_die();
    }

    /**
	 * Add Topic
	 */
    public function add_topic(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $comment_status = 'open';
        if (isset($_POST['status']) && !empty($_POST['status'])) {
            $comment_status = sanitize_text_field($_POST['status']);
        }

        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $post = get_post((int) $_POST['id']);
            if ($post) {
                $post_id = $post->ID;
                $authorID = (int) get_post_field('post_author', $post_id);
                $userID = (int) get_current_user_id();
                if (current_user_can('administrator') || current_user_can('editor')) {
                } else if ($authorID != $userID) {
                    echo esc_html__('You are not allowed to edit this topic.', 'pwork');
                    exit();
                }
                $update_post = array(
                    'ID'           => $post_id,
                    'post_title'   => sanitize_text_field($_POST['title']),
                    'comment_status' => $comment_status
                );
                $update = wp_update_post($update_post);
                if (is_wp_error($update) ) {
                    echo esc_html__('Something went wrong.', 'pwork');
                    exit();
                } else {
                    if (isset($_POST['content']) && !empty($_POST['content'])) {
                        update_post_meta($post_id, 'pwork_forum_content', wp_kses_post($_POST['content']));
                    }
                    if (isset($_POST['tag']) && $_POST['tag'] !== 0 && !empty($_POST['tag'])) {
                        $tags = json_decode( stripslashes( $_POST['tag'] ), true );
                        wp_set_post_terms( $post_id, $tags, 'pworkforumtags');
                    }
                    update_post_meta($post_id, 'pwork_last_activity', current_time( 'U' ));
                }
            } else {
                echo esc_html__('Topic not found.', 'pwork');
                exit();
            }
        } else {
            $post_id = wp_insert_post(array (
                'post_title' => sanitize_text_field($_POST["title"]),
                'post_type' => 'pworkforum',
                'post_status' => 'publish',
                'comment_status' => $comment_status
            ));

            if (is_wp_error( $post_id ) ) {
                echo esc_html__('Something went wrong.', 'pwork');
                exit();
            }
    
            if (isset($_POST['content']) && !empty($_POST['content'])) {
                update_post_meta($post_id, 'pwork_forum_content', wp_kses_post($_POST['content']));
            }
    
            if (isset($_POST['tag']) && $_POST['tag'] !== 0 && !empty($_POST['tag'])) {
                $tags = json_decode( stripslashes( $_POST['tag'] ), true );
                wp_set_post_terms( $post_id, $tags, 'pworkforumtags');
            }
        }
        echo 'done';
        wp_die();
    }

    /**
	 * Delete Topic
	 */
    public function delete_topic(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $authorID = (int) get_post_field('post_author', $_POST['topicid']);
        $userID = (int) get_current_user_id();
        if ($authorID === $userID || current_user_can('administrator') || current_user_can('editor')) {
        if (isset($_POST['topicid']) && !empty($_POST['topicid'])) {
            $topic_id = wp_delete_post((int) $_POST['topicid'], true);
            if ( is_wp_error( $topic_id ) ) {
                echo esc_html__('Something went wrong.', 'pwork');
                exit();
            }
        } else {
            echo esc_html__('Topic ID is required.', 'pwork');
            exit();
        }
    } else {
        echo esc_html__('You are not allowed to delete this topic.', 'pwork');
    }
        echo 'done';
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
        if (isset($_POST['comment']) && !empty($_POST['comment'])) {
            if (isset($_POST['topicid']) && !empty($_POST['topicid'])) {
                if ( comments_open($_POST['topicid']) ) {
                    $data = array(
                        'comment_post_ID'      => (int) $_POST['topicid'],
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
                echo esc_html__('Topic ID is required.', 'pwork');
                exit();
            }
        } else {
            echo esc_html__('The comment field cannot be left blank.', 'pwork');
            exit();
        }
        echo 'done';
        update_post_meta($_POST['topicid'], 'pwork_last_activity', current_time( 'U' ));
        $authorID = (int) get_post_field('post_author', (int) $_POST['topicid']);
        if ($authorID != get_current_user_id()) {
            $slug = PworkSettings::get_option('slug', 'pwork'); 
            $blog_name = esc_html(get_bloginfo( 'name' ));
            $url = get_site_url() . '/' . $slug . '/?page=forum&topicID=' . $_POST['topicid'];
            $subject = esc_html__('New comment at', 'pwork') . ' ' . $blog_name;
            $message = '<p><strong>' . esc_html__('A new comment has been added to your topic.', 'pwork') . '</strong></p><p><a href="' . esc_url($url) . '"><strong>' . esc_html__('Click here to view the topic.', 'pwork') . '</strong></a></p>';
            Pwork::send_email('new_comment', $subject, $message, $authorID);
        }
        wp_die();
    }

    /**
	 * Add Reply
	 */
    public function add_reply(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $current_user = wp_get_current_user();
        if (isset($_POST['comment']) && !empty($_POST['comment'])) {
            if (isset($_POST['topicid']) && !empty($_POST['topicid'])) {
                if ( comments_open($_POST['topicid']) ) {
                    $data = array(
                        'comment_post_ID'      => (int) $_POST['topicid'],
                        'comment_content'      => wp_kses_post($_POST['comment']),
                        'comment_parent'       => (int) $_POST['commentid'],
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
                echo esc_html__('Topic ID is required.', 'pwork');
                exit();
            }
        } else {
            echo esc_html__('The comment field cannot be left blank.', 'pwork');
            exit();
        }
        echo 'done';
        update_post_meta($_POST['topicid'], 'pwork_last_activity', current_time( 'U' ));
        $authorID = (int) get_post_field('post_author', (int) $_POST['topicid']);
        if ($authorID != get_current_user_id()) {
            $slug = PworkSettings::get_option('slug', 'pwork'); 
            $blog_name = esc_html(get_bloginfo( 'name' ));
            $url = get_site_url() . '/' . $slug . '/?page=forum&topicID=' . $_POST['topicid'];
            $subject = esc_html__('New reply at', 'pwork') . ' ' . $blog_name;
            $message = '<p><strong>' . esc_html__('A new reply has been added to your comment.', 'pwork') . '</strong></p><p><a href="' . esc_url($url) . '"><strong>' . esc_html__('Click here to view the topic.', 'pwork') . '</strong></a></p>';
            Pwork::send_email('new_reply', $subject, $message, $authorID);
        }
        wp_die();
    }

    /**
	 * Delete Comment
	 */
    public function delete_comment(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        if (isset($_POST['commentid']) && !empty($_POST['commentid'])) {
            $comment_id = wp_delete_comment((int) $_POST['commentid'], true);
            if ( is_wp_error( $comment_id ) ) {
                echo esc_html__('Something went wrong.', 'pwork');
                exit();
            }
        } else {
            echo esc_html__('Comment ID is required.', 'pwork');
            exit();
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
        $url = get_site_url() . '/' . $slug . '/?page=forum';
        $subject = esc_html__('New topic at', 'pwork') . ' ' . $blog_name;
        $message = '<p><strong>' . esc_html__('A new topic has been added.', 'pwork') . '</strong></p><p><a href="' . esc_url($url) . '"><strong>' . esc_html(get_the_title($id)) . '</strong></a></p>';

        Pwork::add_notification('new_topic', false);
        Pwork::send_email('new_topic', $subject, $message, false);
    }

    /**
	 * Count topics created by the user
	 */
    public static function count_user_topics($userID){
        $posts = get_posts(array(
            'post_status' => 'publish',
            'post_type' => 'pworkforum',
            'posts_per_page'  => 9999,
            'author__in' => (int) $userID
        ));
        return count($posts);
    }

    /**
	 * Count replies created by the user
	 */
    public static function count_user_replies($userID){
        global $wpdb;
        $sql = "SELECT * FROM $wpdb->comments LEFT OUTER JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID) WHERE comment_approved = '1' AND post_password = '' AND post_type='pworkforum' AND post_author=$userID ORDER BY comment_date_gmt DESC LIMIT 99999";
        $comments = $wpdb->get_results($sql);
        return count($comments);
    }

    /**
	 * Count topics
	 */
    public static function count_topics(){
        $posts = get_posts(array(
            'post_status' => 'publish',
            'post_type' => 'pworkforum',
            'posts_per_page'  => 9999
        ));
        return count($posts);
    }

    /**
	 * Count replies
	 */
    public static function count_replies(){
        global $wpdb;
        $sql = "SELECT * FROM $wpdb->comments LEFT OUTER JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID) WHERE comment_approved = '1' AND post_password = '' AND post_type='pworkforum' ORDER BY comment_date_gmt DESC LIMIT 99999";
        $comments = $wpdb->get_results($sql);
        return count($comments);
    }

}

/**
 * Returns the main instance of the class
 */
function PworkForum() {  
	return PworkForum::instance();
}
// Global for backwards compatibility
$GLOBALS['PworkForum'] = PworkForum();