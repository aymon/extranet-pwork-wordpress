<?php
defined( 'ABSPATH' ) || exit;

class pworkProjects {
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
        add_filter('cmb2_meta_boxes', array($this, 'add_project_metabox') );
        add_action('pwork_body_end', array($this, 'scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('pworkprojectstags_add_form_fields', array($this, 'add_tag_color_field'));
        add_action('pworkprojectstags_edit_form_fields', array($this, 'edit_tag_color_field'));
        add_action('edited_term', array($this, 'update_term'));
        add_action('created_term', array($this, 'update_term'));
        add_action('wp_ajax_pworkUpdateChecklist', array($this, 'update_checklist'));
        add_action('wp_ajax_pworkProjectAddComment', array($this, 'add_comment'));
        add_action('wp_ajax_pworkAddProject', array($this, 'add_project'));
        add_action('wp_ajax_pworkLoadProjects', array($this, 'load_projects'));
        add_action('wp_ajax_pworkJoinProject', array($this, 'join_project'));
        add_action('publish_pworkprojects', array($this, 'send_notification'), 10, 2 );
    }

    /**
	 * Register Post Type
	 */
    public function register_post_type() {
        $labels = array(
            'name'              => esc_html__( 'Pwork Projects', 'pwork' ),
            'singular_name'     => esc_html__( 'Project', 'pwork' ),
            'add_new'           => esc_html__( 'Add new project', 'pwork' ),
            'add_new_item'      => esc_html__( 'Add new project', 'pwork' ),
            'edit_item'         => esc_html__( 'Edit project', 'pwork' ),
            'new_item'          => esc_html__( 'New project', 'pwork' ),
            'view_item'         => esc_html__( 'View project', 'pwork' ),
            'search_items'      => esc_html__( 'Search projects', 'pwork' ),
            'not_found'         => esc_html__( 'No project found', 'pwork' ),
            'not_found_in_trash'=> esc_html__( 'No project found in trash', 'pwork' ),
            'parent_item_colon' => esc_html__( 'Parent project:', 'pwork' ),
            'menu_name'         => esc_html__( 'PW Projects', 'pwork' )
        );
    
        $taxonomies = array();
        $supports = array('title','excerpt','thumbnail','comments','author');
     
        $post_type_args = array(
            'labels'            => $labels,
            'singular_label'    => esc_html__('Project', 'pwork'),
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
            'menu_icon'         => 'dashicons-pressthis',
            'taxonomies'        => $taxonomies
        );
        register_post_type('pworkprojects',$post_type_args);
    }

    /**
	 * Register Taxonomy
	 */
    public function register_taxonomy() {
        register_taxonomy(
            'pworkprojectstags',
            'pworkprojects',
            array(
                'labels' => array(
                    'name' => esc_html__( 'PW Project Tags', 'pwork' ),
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
	 * Add project metabox
	 */
    public function add_project_metabox( $meta_boxes ) {
        $meta_boxes['pwork_project_status_metabox'] = array(
            'id' => 'pwork_project_status_metabox',
            'title' => esc_html__( 'Info', 'pwork'),
            'object_types' => array('pworkprojects'),
            'context' => 'side',
            'priority' => 'high',
            'show_names' => true,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Due Date', 'pwork' ),
                    'id'      => 'pwork_project_due',
                    'type'    => 'text',
                    'attributes' => array(
                        'type' => 'datetime-local',
                        'autocomplete' => 'off'
                    )
                ),
            ),
        );

        $blocked_roles = PworkSettings::get_option('blocked_roles', array());
        $users = get_users(array(
            'role__not_in' => $blocked_roles
        ));
        $users_array = array();
        foreach ( $users as $user ) {
            $users_array[$user->ID] = $user->display_name;
        }

        $meta_boxes['pwork_project_members_metabox'] = array(
            'id' => 'pwork_project_members_metabox',
            'title' => esc_html__( 'Members', 'pwork'),
            'object_types' => array('pworkprojects'),
            'context' => 'side',
            'priority' => 'high',
            'show_names' => true,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Who Can Join?', 'pwork' ),
                    'id'      => 'pwork_project_who_can_join',
                    'type' => 'select',
                    'options' => array(
                        'everyone' => esc_html__( 'Anyone can join', 'pwork' ),
                        'invited'   => esc_html__( 'Only those added by the author', 'pwork' ),
                    ),
                    'attributes' => array(
                        'autocomplete' => 'off'
                    ),
                    'default' => 'everyone',
                ),
                array(
                    'name'    => esc_html__( 'Members', 'pwork' ),
                    'description' => esc_html__( 'Include yourself to receive email notifications and to comment.', 'pwork' ),
                    'id'      => 'pwork_project_members',
                    'type' => 'select_multiple',
                    'options' => $users_array,
                    'attributes' => array(
                        'autocomplete' => 'off'
                    ),
                    'default' => array(get_current_user_id()),
                ),
            ),
        );

        $meta_boxes['pwork_project_desc_metabox'] = array(
            'id' => 'pwork_project_desc_metabox',
            'title' => esc_html__( 'Description', 'pwork'),
            'object_types' => array('pworkprojects'),
            'context' => 'normal',
            'priority' => 'high',
            'show_names' => false,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Description', 'pwork' ),
                    'id'      => 'pwork_project_desc',
                    'type'    => 'wysiwyg',
                    'options' => array(
                        'wpautop' => true,
                        'media_buttons' => false,
                        'quicktags' => false,
                        'teeny' => true,
                    ),
                ),
            ),
        );

        $meta_boxes['pwork_project_checklist_metabox'] = array(
            'id' => 'pwork_project_checklist_metabox',
            'title' => esc_html__( 'Tasks', 'pwork'),
            'object_types' => array('pworkprojects'),
            'context' => 'normal',
            'priority' => 'high',
            'show_names' => true,
            'fields' => array(
                array(
                    'id'      => 'pwork_project_checklist_enable',
                    'name'   => esc_html__( 'Checklist', 'pwork' ),
                    'type' => 'radio_inline',
                    'options' => array(
                        'enable' => esc_html__( 'Enable', 'pwork' ),
                        'disable'   => esc_html__( 'Disable', 'pwork' )
                    ),
                    'attributes' => array(
                        'autocomplete' => 'off'
                    ),
                    'default' => 'disable'
                ),
                array(
                    'id' => 'pwork_project_checklist',
                    'type' => 'group',
                    'options' => array(
                        'group_title'   => esc_html__( 'Item {#}', 'pwork' ),
                        'add_button' => esc_html__( 'Add Another Item', 'pwork' ),
                        'remove_button' => esc_html__( 'Remove Item', 'pwork' ),
                        'sortable' => false,
                        'closed' => true
                    ),
                    'fields' => array(
                        array(
                            'name' => esc_html__( 'Description:', 'pwork'),
                            'id' => 'desc',
                            'type' => 'text'
                        ),
                        array(
                            'name' => esc_html__( 'Status:', 'pwork'),
                            'id' => 'status',
                            'type' => 'radio_inline',
                            'options' => array(
                                'inprogress' => esc_html__( 'In Progress', 'pwork' ),
                                'completed'   => esc_html__( 'Completed', 'pwork' ),
                            ),
                            'default' => 'inprogress'
                        ),
                    ),
                ),
            ),
        );
    
        return $meta_boxes;
    }

    /**
	 * Update checklist
	 */
    public function update_checklist() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $current_user = wp_get_current_user();
        $postID = (int) $_POST['postid'];
        $item = (int) $_POST['item'];
        $status = sanitize_text_field($_POST['status']);
        $statusText = esc_html__( 'in progress', 'pwork' );
        if ($status == 'completed') {
            $statusText = esc_html__( 'completed', 'pwork' );
        }
        $checklist = get_post_meta( $postID, 'pwork_project_checklist', true );

        if (!empty($checklist) && is_array($checklist)) {
            $checklist[$item]['status'] = $status;
            $itemDesc = $checklist[$item]['desc'];
            update_post_meta($postID, 'pwork_project_checklist', $checklist);
            $commentData = array(
                'comment_post_ID'      => $postID,
                'comment_content'      => '<strong>"' . $itemDesc . '" ' . esc_html__( 'is', 'pwork' ) . ' ' . $statusText . '.</strong>',
                'comment_parent'       => 0,
                'user_id'              => $current_user->ID,
                'comment_author'       => $current_user->user_login,
                'comment_author_email' => $current_user->user_email,
                'comment_author_url'   => $current_user->user_url
            );
    
            wp_insert_comment($commentData);
            $members = get_post_meta( $postID, 'pwork_project_members', true );
            $slug = PworkSettings::get_option('slug', 'pwork'); 
            $blog_name = esc_html(get_bloginfo( 'name' ));
            $url = get_site_url() . '/' . $slug . '/?page=projects-my';
            $subject = esc_html__('New comment at', 'pwork') . ' ' . $blog_name;
            $message = '<p><strong>' . esc_html__('A new comment has been added to ', 'pwork') . ' "' . get_the_title($postID) . '".</strong></p><p><a href="' . esc_url($url) . '"><strong>' . esc_html__('Click here to view your projects.', 'pwork') . '</strong></a></p>';
            Pwork::send_email('new_project_activity', $subject, $message, $members);
        } else {
            echo 'error';
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
        $members = get_post_meta( $postID, 'pwork_project_members', true );
        $slug = PworkSettings::get_option('slug', 'pwork'); 
        $blog_name = esc_html(get_bloginfo( 'name' ));
        $url = get_site_url() . '/' . $slug . '/?page=projects';
        $subject = esc_html__('New comment at', 'pwork') . ' ' . $blog_name;
        $message = '<p><strong>' . esc_html__('A new comment has been added to ', 'pwork') . ' "' . get_the_title($post_id) . '".</strong></p><p><a href="' . esc_url($url) . '"><strong>' . esc_html__('Click here to view the projects.', 'pwork') . '</strong></a></p>';
        Pwork::send_email('new_project_activity', $subject, $message, $members);
        wp_die();
    }

    /**
	 * Load Projects
	 */
    public function load_projects(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $slug =  PworkSettings::get_option('slug', 'pwork');
        $limit = PworkSettings::get_option('projects_limit', 12);
        $offset = 0;
        if (isset($_POST['offset']) && !empty($_POST['offset'])) {
            $offset = (int) $_POST['offset'];
        }
        $newofset = $offset + $limit;
        $args = array(
            'post_status' => 'publish',
            'post_type' => 'pworkprojects',
            'posts_per_page'  => $limit,
            'offset'  => $offset,
            'order'  => 'DESC',
            'orderby'  => 'post_date'
        );
        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $args['s'] = sanitize_text_field($_POST['search']);
        }
        if (isset($_POST['userid']) && !empty($_POST['userid'])) {
            $args['meta_query'] = array(
                array(
                    'key' => 'pwork_project_members',
                    'value' => '"(' . (int) $_POST['userid'] . ')"',
                    'compare' => 'REGEXP'
                ),
            );
        }
        if (isset($_POST['tag']) && !empty($_POST['tag'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'pworkprojectstags',
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
        $project_url = get_site_url() . '/' . $slug . '/?page=projects-single&ID=' . $postID;
        $content = get_post_meta( $postID, 'pwork_project_desc', true );
        $due = get_post_meta( $postID, 'pwork_project_due', true );
        $members = get_post_meta( $postID, 'pwork_project_members', true );
        $who_can_join = get_post_meta( $postID, 'pwork_project_who_can_join', true );
        $checklist_check = get_post_meta( $postID, 'pwork_project_checklist_enable', true );
        $checklist = get_post_meta( $postID, 'pwork_project_checklist', true );
        $terms = get_the_terms( $postID, 'pworkprojectstags' );
        ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3">
            <div class="card post-card mb-4">
                <?php 
                if (has_post_thumbnail()) {
                    $thumbnail_id = get_post_thumbnail_id();
                    $thumbnail_src = wp_get_attachment_image_src($thumbnail_id, 'large', true);
                    echo '<a href="' . esc_url($project_url) . '"><img class="card-img-top" src="' . esc_url($thumbnail_src[0]) . '"></a>';
                } ?>
                <div class="card-body">
                <span class="card-date"><a href="<?php echo esc_url($project_url); ?>" style="color:inherit"><?php echo get_the_date(get_option('date_format') . ' ' . get_option('time_format')); ?></a></span>
                <h4 class="card-title"><a href="<?php echo esc_url($project_url); ?>" style="color:inherit"><?php the_title(); ?></a></h4>
                <p class="card-text"><?php the_excerpt(); ?></p>
                <?php if (!empty($members) && is_array($members)) { ?>
                <ul class="list-unstyled users-list avatar-group d-flex align-items-center me-0 ms-0 mt-3 mb-0">
                    <?php 
                    foreach($members as $member_id) {
                        $member = get_user_by('id', $member_id);
                        $member_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $member_id;
                        echo '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-sm pull-up" title="" data-bs-original-title="' . esc_attr($member->display_name) . '"><a href="' . esc_url($member_profile_url) . '" target="_blank">' . get_avatar($member_id, 100) . '</a></li>';
                    }
                    ?>
                </ul>
                <?php } ?>
                </div>
                <?php if (!empty($terms) && is_array($terms)) { ?>
                <div class="pwork-card-footer justify-content-start">
                <?php
                foreach($terms as $term) {
                    echo '<span class="badge bg-dark me-1 project-tag"><a href="' . esc_url(get_site_url() . '/' . $slug . '/?page=projects&tagID=' . $term->term_id) . '">' . esc_html($term->name) . '</a></span>';
                }
                ?>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php
        endwhile;
        if ($limit == $visible_posts) { ?>
            <div class="col-12 mt-2">
                <button id="pwork-load-more-projects" type="button" class="btn btn-lg btn-primary w-100" data-offset="<?php echo esc_attr($newofset); ?>"><?php echo esc_html__( 'LOAD MORE', 'pwork' ); ?></button>
            </div>
            <?php }
            wp_reset_postdata();
        }
        wp_die();
    }

    /**
	 * Add Project
	 */
    public function add_project(){
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
                    echo esc_html__('You are not allowed to edit this project.', 'pwork');
                    exit();
                }
                $update_post = array(
                    'ID' => $post_id,
                    'post_title' => sanitize_text_field($_POST["title"]),
                    'post_type' => 'pworkprojects',
                    'post_status' => 'publish',
                    'post_excerpt' => sanitize_text_field($_POST['excerpt'])
                );
                $update = wp_update_post($update_post);
                if (is_wp_error($update) ) {
                    echo esc_html__('Something went wrong.', 'pwork');
                    exit();
                } else {
                    if (isset($_POST['due']) && !empty($_POST['due'])) {
                        update_post_meta($post_id, 'pwork_project_due', sanitize_text_field($_POST['due']));
                    }
            
                    if (isset($_POST['content']) && !empty($_POST['content'])) {
                        update_post_meta($post_id, 'pwork_project_desc', wp_kses_post($_POST['content']));
                    }
        
                    if (isset($_POST['who']) && !empty($_POST['who'])) {
                        update_post_meta($post_id, 'pwork_project_who_can_join', sanitize_text_field($_POST['who']));
                    }
        
                    if (isset($_POST['checklist']) && !empty($_POST['checklist'])) {
                        update_post_meta($post_id, 'pwork_project_checklist_enable', sanitize_text_field($_POST['checklist']));
                    }
        
                    if (isset($_POST['tasks']) && !empty($_POST['tasks'])) {
                        $tasks = json_decode( stripslashes( $_POST['tasks'] ), true );
                        $newtasks = array();
                        foreach ($tasks as $entry) {
                            array_push($newtasks, array(
                                'desc' => $entry[0],
                                'status' => $entry[1]
                            ));
                        }
                        update_post_meta($post_id, 'pwork_project_checklist', $newtasks);
                    }
        
                    if (isset($_POST['members']) && $_POST['members'] !== 0 && !empty($_POST['members'])) {
                        $members = json_decode( stripslashes( $_POST['members'] ), true );
                        update_post_meta($post_id, 'pwork_project_members', $members);
                    }
            
                    if (isset($_POST['tag']) && $_POST['tag'] !== 0 && !empty($_POST['tag'])) {
                        $tags = json_decode( stripslashes( $_POST['tag'] ), true );
                        wp_set_post_terms( $post_id, $tags, 'pworkprojectstags');
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
                echo esc_html__('Topic not found.', 'pwork');
                exit();
            }
        } else {
            $post_id = wp_insert_post(array (
                'post_title' => sanitize_text_field($_POST["title"]),
                'post_type' => 'pworkprojects',
                'post_status' => 'publish',
                'post_excerpt' => sanitize_text_field($_POST['excerpt'])
            ));

            if (is_wp_error( $post_id ) ) {
                echo esc_html__('Something went wrong.', 'pwork');
                exit();
            }

            if (isset($_POST['due']) && !empty($_POST['due'])) {
                update_post_meta($post_id, 'pwork_project_due', sanitize_text_field($_POST['due']));
            }
    
            if (isset($_POST['content']) && !empty($_POST['content'])) {
                update_post_meta($post_id, 'pwork_project_desc', wp_kses_post($_POST['content']));
            }

            if (isset($_POST['who']) && !empty($_POST['who'])) {
                update_post_meta($post_id, 'pwork_project_who_can_join', sanitize_text_field($_POST['who']));
            }

            if (isset($_POST['checklist']) && !empty($_POST['checklist'])) {
                update_post_meta($post_id, 'pwork_project_checklist_enable', sanitize_text_field($_POST['checklist']));
            }

            if (isset($_POST['tasks']) && !empty($_POST['tasks'])) {
                $tasks = json_decode( stripslashes( $_POST['tasks'] ), true );
                $newtasks = array();
                foreach ($tasks as $entry) {
                    array_push($newtasks, array(
                        'desc' => $entry[0],
                        'status' => $entry[1]
                    ));
                }
                update_post_meta($post_id, 'pwork_project_checklist', $newtasks);
            }

            if (isset($_POST['members']) && $_POST['members'] !== 0 && !empty($_POST['members'])) {
                $members = json_decode( stripslashes( $_POST['members'] ), true );
                update_post_meta($post_id, 'pwork_project_members', $members);
            }
    
            if (isset($_POST['tag']) && $_POST['tag'] !== 0 && !empty($_POST['tag'])) {
                $tags = json_decode( stripslashes( $_POST['tag'] ), true );
                wp_set_post_terms( $post_id, $tags, 'pworkprojectstags');
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
        echo 'done';
        wp_die();
    }

    /**
	 * Join Project
	 */
    public function join_project(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $postID = (int) $_POST['id'];
            $current_user = wp_get_current_user();
            $members = get_post_meta($_POST['id'], 'pwork_project_members', true);
            array_push($members, $current_user->ID);
            update_post_meta($_POST['id'], 'pwork_project_members', $members);
            $commentData = array(
                'comment_post_ID'      => $postID,
                'comment_content'      => '<strong>"' . $current_user->display_name . '" ' . esc_html__( 'is joined the project', 'pwork' ) . '.</strong>',
                'comment_parent'       => 0,
                'user_id'              => $current_user->ID,
                'comment_author'       => $current_user->user_login,
                'comment_author_email' => $current_user->user_email,
                'comment_author_url'   => $current_user->user_url
            );
            wp_insert_comment($commentData);
            $slug = PworkSettings::get_option('slug', 'pwork'); 
            $blog_name = esc_html(get_bloginfo( 'name' ));
            $url = get_site_url() . '/' . $slug . '/?page=projects-my';
            $subject = esc_html__('New project member at', 'pwork') . ' ' . $blog_name;
            $message = '<p><strong>' . esc_html__('A new member has been joined to ', 'pwork') . ' "' . get_the_title($postID) . '".</strong></p><p><a href="' . esc_url($url) . '"><strong>' . esc_html__('Click here to view your projects.', 'pwork') . '</strong></a></p>';
            Pwork::send_email('new_project_activity', $subject, $message, $members);
            echo 'done';
        }
        wp_die();
    }

    /**
	 * Send notification
	 */
    public function send_notification($id, $post){
        $slug = PworkSettings::get_option('slug', 'pwork'); 
        $blog_name = esc_html(get_bloginfo( 'name' ));
        $url = get_site_url() . '/' . $slug . '/?page=projects-single&ID=' . $id;
        $subject = esc_html__('New project at', 'pwork') . ' ' . $blog_name;
        $message = '<p><strong>' . esc_html__('A new project has been added.', 'pwork') . '</strong></p><p><a href="' . esc_url($url) . '"><strong>' . esc_html(get_the_title($id)) . '</strong></a></p>';

        Pwork::add_notification('new_project', false);
        Pwork::send_email('new_project', $subject, $message, false);
    }

    /**
	 * Progress bar
	 */
    public static function progress_bar($completed, $total){
        $width = round(($completed * 100) / $total);
        echo '<div class="project-progress"><div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: ' . $width . '%" aria-valuenow="' . $width . '" aria-valuemin="0" aria-valuemax="100"></div></div><label class="d-block fst-italic">%' . $width . ' ' . esc_html__('of the tasks completed.', 'pwork') . ' </label></div>';
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
	 * Count projects
	 */
    public static function count_projects(){
        $projects = get_posts(array(
            'post_status' => 'publish',
            'post_type' => 'pworkprojects',
            'posts_per_page'  => 9999
        ));
        return count($projects);
    }

    /**
	 * Count my projects
	 */
    public static function count_my_projects(){
        $projects = get_posts(array(
            'post_status' => 'publish',
            'post_type' => 'pworkprojects',
            'author__in' => get_current_user_id(),
            'posts_per_page'  => 9999
        ));
        return count($projects);
    }

    /**
	 * Count activities
	 */
    public static function count_activities(){
        global $wpdb;
        $activities = $wpdb->get_var("SELECT COUNT(comment_ID)
            FROM $wpdb->comments
            WHERE comment_post_ID in (
            SELECT ID 
            FROM $wpdb->posts 
            WHERE post_type = 'pworkprojects' 
            AND post_status = 'publish')
            AND comment_approved = '1'
        ");
        return $activities;
    }

    /**
	 * Custom Scripts
	 */
    public function scripts(){
        if (isset($_GET['page']) && $_GET['page'] == 'projects-manage') {
            ?>
            <script type="text/template" id="pwork-tasks-template">
                <div class="input-group mb-3">
                    <input autocomplete="off" type="text" class="form-control" id="pwork_task_desc_{?}" name="pwork_task_desc_{?}" value="" placeholder="<?php echo esc_attr__('Enter description...', 'pwork'); ?>">
                    <select id="pwork_task_status_{?}" name="pwork_task_status_{?}" class="form-select" autocomplete="off">
                        <option value="inprogress" selected><?php echo esc_html__( 'In progress', 'pwork' ); ?></option>
                        <option value="completed"><?php echo esc_html__( 'Completed', 'pwork' ); ?></option>
                    </select>
                    <button type="button" class="btn btn-danger pwork-delete-task"><i class="bx bx-trash"></i></button>
                </div>
            </script>
        <?php } 
    }
}

/**
 * Returns the main instance of the class
 */
function pworkProjects() {  
	return pworkProjects::instance();
}
// Global for backwards compatibility
$GLOBALS['pworkProjects'] = pworkProjects();