<?php
defined( 'ABSPATH' ) || exit;

class PworkFiles {
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
        add_filter('cmb2_meta_boxes', array($this, 'add_file_metabox') );
        add_action('before_delete_post', array($this, 'delete_attachment'), 10, 2);
        add_action('wp_ajax_pworkSearchFiles', array($this, 'search_files'));
        add_action('wp_ajax_pworkDeleteFile', array($this, 'delete_file'));
        add_action('wp_ajax_pworkUploadFile', array($this, 'upload_file'));
        add_action('wp_ajax_pworkEditFile', array($this, 'edit_file'));
        add_filter('manage_edit-pworkfiles_columns', array($this, 'admin_column'), 5);
        add_action('manage_posts_custom_column', array($this, 'admin_row'), 5, 2);
    }

    /**
	 * Register Post Type
	 */
    public function register_post_type() {
        $labels = array(
            'name'              => esc_html__( 'Pwork Files', 'pwork' ),
            'singular_name'     => esc_html__( 'file', 'pwork' ),
            'add_new'           => esc_html__( 'Add new file', 'pwork' ),
            'add_new_item'      => esc_html__( 'Add new file', 'pwork' ),
            'edit_item'         => esc_html__( 'Edit file', 'pwork' ),
            'new_item'          => esc_html__( 'New file', 'pwork' ),
            'view_item'         => esc_html__( 'View file', 'pwork' ),
            'search_items'      => esc_html__( 'Search files', 'pwork' ),
            'not_found'         => esc_html__( 'No file found', 'pwork' ),
            'not_found_in_trash'=> esc_html__( 'No file found in trash', 'pwork' ),
            'parent_item_colon' => esc_html__( 'Parent file:', 'pwork' ),
            'menu_name'         => esc_html__( 'PW Files', 'pwork' )
        );
    
        $taxonomies = array();
        $supports = array('title','author');
     
        $post_type_args = array(
            'labels'            => $labels,
            'singular_label'    => esc_html__('file', 'pwork'),
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
            'menu_icon'         => 'dashicons-category',
            'taxonomies'        => $taxonomies
        );
        register_post_type('pworkfiles',$post_type_args);
    }

    /**
	 * Register Taxonomy
	 */
    public function register_taxonomy() {
        register_taxonomy(
            'pworkfolders',
            'pworkfiles',
            array(
                'labels' => array(
                    'name' => esc_html__( 'PW Files Tags', 'pwork' ),
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
	 * Add file metabox
	 */
    public function add_file_metabox( $meta_boxes ) {
        $meta_boxes['pwork_file_data'] = array(
            'id' => 'pwork_file_data',
            'title' => esc_html__( 'File', 'pwork'),
            'object_types' => array('pworkfiles'),
            'context' => 'normal',
            'priority' => 'default',
            'show_names' => true,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'File URL', 'pwork' ),
                    'id'      => 'pwork_file_url',
                    'type'    => 'file',
                    'options' => array(
                        'url' => true
                    ),
                )
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

        $meta_boxes['pwork_file_members_metabox'] = array(
            'id' => 'pwork_file_members_metabox',
            'title' => esc_html__( 'Accessibility', 'pwork'),
            'object_types' => array('pworkfiles'),
            'context' => 'side',
            'priority' => 'default',
            'show_names' => false,
            'fields' => array(
                array(
                    'name'    => esc_html__( 'Members', 'pwork' ),
                    'description' => esc_html__( 'Select which user(s) can access the file. Leave blank to upload a public file.', 'pwork' ),
                    'id'      => 'pwork_file_members',
                    'type' => 'select_multiple',
                    'options' => $users_array,
                    'attributes' => array(
                        'autocomplete' => 'off'
                    )
                ),
            ),
        );
    
        return $meta_boxes;
    }

    /**
     * Delete attachments
     */
    public function delete_attachment($post_id) {
        if ( get_post_type( $post_id ) == 'pworkfiles' ) {
            $file_url = get_post_meta($post_id, 'pwork_file_url', true ); 
            if ($file_url && !empty($file_url)) {
                $attachment_id = attachment_url_to_postid($file_url);
                $delete = wp_delete_attachment($attachment_id, true);
            }
        }
    }

    /**
	 * Search Files
	 */
    public function search_files() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $slug = PworkSettings::get_option('slug', 'pwork');
        $user = wp_get_current_user();

        $args = array(
            'post_status' => 'publish',
            'post_type' => 'pworkfiles',
            'posts_per_page'  => 99999,
            'order'  => 'DESC',
            'orderby'  => 'post_date'
        );

        if (isset($_POST['folder']) && !empty($_POST['folder'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'pworkfolders',
                    'field' => 'term_id',
                    'terms' => (int) $_POST['folder'],
                ),
            );
        }

        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $args['meta_query'] = array(
                array(
                    'key' => 'pwork_file_url',
                    'value' => $_POST['search'],
                    'compare' => 'LIKE'
                ),                
            );
        }

        if (isset($_POST['author']) && !empty($_POST['author']) && $_POST['author'] === 'my') {
            $args['author__in'] = get_current_user_id();
        }

        $the_query = new WP_Query( $args );
        if ( $the_query->have_posts() ) {
            while ( $the_query->have_posts() ) : $the_query->the_post();
            $postID = get_the_ID();
            $members = get_post_meta( $postID, 'pwork_file_members', true );
            $file_url = get_post_meta( $postID, 'pwork_file_url', true ); 
            $authorID = (int) get_post_field('post_author', $postID);
            $filename = basename($file_url);
            $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $authorID;
            $edit_url = get_site_url() . '/' . $slug . '/?page=file-edit&id=' . $postID;
            $path = str_replace( site_url('/'), ABSPATH, esc_url( $file_url) );
            $terms = get_the_terms($postID, 'pworkfolders'); 
            $badges = '';
            if ($terms) {
                foreach($terms as $term) {
                    $badges = '<span class="badge bg-secondary me-1 mt-1"><a href="' . esc_url(get_site_url() . '/' . $slug . '/?page=files-tag&tagID=' . $term->term_id) . '">' . esc_html($term->name) . '</a></span>' . $badges;
                }
            }
            if (!empty($members) && is_array($members)) {
                $badges = '<span class="badge bg-warning me-1 mt-1">' . esc_html__('Private', 'pwork') . '</span>' . $badges;
            }
            if (!empty($members) && is_array($members) && !current_user_can('administrator')) {
                if ($authorID == $user->ID || in_array($user->ID, $members)) { ?>
                <tr>
                    <td>
                        <a href="<?php echo esc_url($file_url); ?>" target="_blank"><strong class="pwork-file-title text-truncate"><?php echo esc_html($filename); ?></strong></a>
                        <?php echo wp_kses_post($badges); ?> 
                        <div class="d-block d-md-none">
                            <small class="d-block mt-2 mb-1"><?php echo esc_html__('Uploaded by', 'pwork') . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a> ' . esc_html__('on', 'pwork') . ' ' . esc_html(get_the_date(get_option('date_format'))); ?></small>
                            <small class="d-block"><?php echo esc_html__('File Size:', 'pwork'); ?> <?php Pwork::human_filesize(wp_filesize( $path )); ?></small>
                        </div>
                    </td>
                    <td class="d-none d-md-table-cell">
                    <small><?php echo esc_html__('Uploaded by', 'pwork') . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a><br>' . esc_html__('on', 'pwork') . ' ' . esc_html(get_the_date(get_option('date_format'))); ?></small>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <?php Pwork::human_filesize(wp_filesize( $path )); ?>
                    </td>
                    <td class="text-end">
                        <div class="btn-group" role="group"> 
                            <button type="button" class="btn btn-sm btn-secondary pwork-copy-url" title="<?php echo esc_attr__('Copy url', 'pwork'); ?>" data-url="<?php echo esc_url($file_url); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><span class="tf-icons bx bxs-copy"></span></button>
                            <a href="<?php echo esc_url($file_url); ?>" class="btn btn-sm btn-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Download File', 'pwork'); ?>" download><span class="tf-icons bx bxs-download"></span></a>
                            <?php if (current_user_can('administrator') || current_user_can('editor') || ($authorID === get_current_user_id())) { ?>
                            <a href="<?php echo esc_url($edit_url); ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Edit File', 'pwork'); ?>"><span class="tf-icons bx bxs-edit-alt"></span></a>
                            <button type="button" class="btn btn-sm btn-danger pwork-delete-file" title="<?php echo esc_attr__('Delete file', 'pwork'); ?>" data-id="<?php echo esc_attr($postID); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><span class="tf-icons bx bxs-trash"></span></button>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <?php }
            } else { ?>
            <tr>
                <td>
                    <a href="<?php echo esc_url($file_url); ?>" target="_blank"><strong class="pwork-file-title text-truncate"><?php echo esc_html($filename); ?></strong></a>
                    <?php echo wp_kses_post($badges); ?> 
                    <div class="d-block d-md-none">
                        <small class="d-block mt-2 mb-1"><?php echo esc_html__('Uploaded by', 'pwork') . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a> ' . esc_html__('on', 'pwork') . ' ' . esc_html(get_the_date(get_option('date_format'))); ?></small>
                        <small class="d-block"><?php echo esc_html__('File Size:', 'pwork'); ?> <?php Pwork::human_filesize(wp_filesize( $path )); ?></small>
                    </div>
                </td>
                <td class="d-none d-md-table-cell">
                <small><?php echo esc_html__('Uploaded by', 'pwork') . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a><br>' . esc_html__('on', 'pwork') . ' ' . esc_html(get_the_date(get_option('date_format'))); ?></small>
                </td>
                <td class="d-none d-md-table-cell">
                    <?php Pwork::human_filesize(wp_filesize( $path )); ?>
                </td>
                <td class="text-end">
                    <div class="btn-group" role="group"> 
                        <button type="button" class="btn btn-sm btn-secondary pwork-copy-url" title="<?php echo esc_attr__('Copy url', 'pwork'); ?>" data-url="<?php echo esc_url($file_url); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><span class="tf-icons bx bxs-copy"></span></button>
                        <a href="<?php echo esc_url($file_url); ?>" class="btn btn-sm btn-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Download File', 'pwork'); ?>" download><span class="tf-icons bx bxs-download"></span></a>
                        <?php if (current_user_can('administrator') || current_user_can('editor') || ($authorID === get_current_user_id())) { ?>
                        <a href="<?php echo esc_url($edit_url); ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Edit File', 'pwork'); ?>"><span class="tf-icons bx bxs-edit-alt"></span></a>
                        <button type="button" class="btn btn-sm btn-danger pwork-delete-file" title="<?php echo esc_attr__('Delete file', 'pwork'); ?>" data-id="<?php echo esc_attr($postID); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><span class="tf-icons bx bxs-trash"></span></button>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <?php }
            endwhile;
            wp_reset_postdata();
        } else {
            echo '<tr><td colspan="4"><div class="alert alert-warning m-0">' .  esc_html__('Nothing found.', 'pwork') . '</div></td></tr>';
        }
        wp_die();
    }

    /**
	 * Delete File
	 */
    public function delete_file(){
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
	 * Upload File
	 */
    public function upload_file(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $allowedFormats = array();
        foreach(get_allowed_mime_types() as $id => $key) { 
            array_push($allowedFormats, $key);
        }
        if (in_array($_FILES['file']['type'], $allowedFormats)) {
            $post_id = wp_insert_post(array (
                'post_title' => esc_html($_FILES["file"]["name"]),
                'post_type' => 'pworkfiles',
                'post_status' => 'publish'
            ));

            if (isset($_POST['tag']) && $_POST['tag'] !== 0 && !empty($_POST['tag'])) {
                $tags = json_decode( stripslashes( $_POST['tag'] ), true );
                wp_set_post_terms( $post_id, $tags, 'pworkfolders');
            }
            unset($_POST['tag']);

            $upload = wp_upload_bits($_FILES["file"]["name"], null, file_get_contents($_FILES["file"]["tmp_name"]));
            $path_parts = pathinfo($_FILES["file"]["name"]);
            $post_title = $path_parts['filename'];
            $info = wp_check_filetype( $upload['file'] );
            $attachment = [
                'post_title' => $post_title,
                'guid' => $upload['url'],
                'post_mime_type' => $info['type']
            ];
            $attachment_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
            wp_update_attachment_metadata(
                $attachment_id,
                wp_generate_attachment_metadata( $attachment_id, $upload['file'] )
            );
            $attachment_url = wp_get_attachment_url($attachment_id);
            add_post_meta($post_id, 'pwork_file_url', $attachment_url, true );

            if (isset($_POST['members']) && $_POST['members'] !== 0 && !empty($_POST['members'])) {
                $members = json_decode( stripslashes( $_POST['members'] ), true );
                add_post_meta($post_id, 'pwork_file_members', $members, true );
            }

        } else {
            wp_send_json_error(esc_html__('This file type is not allowed.', 'pwork'));
        }
        wp_die();
    }

    /**
	 * Edit File
	 */
    public function edit_file(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        if (isset($_POST['fileid']) && !empty($_POST['fileid'])) {
            $post = get_post((int) $_POST['fileid']);
            if ($post) {
                $post_id = $post->ID;
                $authorID = (int) get_post_field('post_author', $post_id);
                $userID = (int) get_current_user_id();
                if (current_user_can('administrator') || current_user_can('editor')) {
                } else if ($authorID != $userID) {
                    echo esc_html__('You are not allowed to edit this file.', 'pwork');
                    exit();
                }
                $update_post = array(
                    'ID' => $post_id,
                    'post_title' => sanitize_text_field($_POST["name"]),
                    'post_type' => 'pworkfiles',
                    'post_status' => 'publish'
                );
                $update = wp_update_post($update_post);
                if (is_wp_error($update) ) {
                    echo esc_html__('Something went wrong.', 'pwork');
                    exit();
                } else {
                    if (isset($_POST['members']) && $_POST['members'] !== 0 && !empty($_POST['members'])) {
                        $members = json_decode( stripslashes( $_POST['members'] ), true );
                        update_post_meta($post_id, 'pwork_file_members', $members);
                    }
            
                    if (isset($_POST['tag']) && $_POST['tag'] !== 0 && !empty($_POST['tag'])) {
                        $tags = json_decode( stripslashes( $_POST['tag'] ), true );
                        wp_set_post_terms( $post_id, $tags, 'pworkfolders');
                    }
                }
                echo 'done';
            } else {
                echo esc_html__('File not found.', 'pwork');
                exit();
            }
        } else {
            echo esc_html__('File ID required.', 'pwork');
            exit();
        }
        wp_die();
    }

    /**
	 * Add custom admin table column
	 */
    public function admin_column($defaults){
        $defaults['pwork_file_url'] = '';
        return $defaults;
    }

    /**
	 * Add custom admin table row
	 */
    public function admin_row($column_name, $post_id){
        if($column_name === 'pwork_file_url'){
            $file = get_post_meta($post_id, 'pwork_file_url', true );
            echo '<a href="' . esc_url($file) . '" class="button" download>' . esc_html__( 'Download', 'pwork' ) . '</a>';
        }   
    }

    /**
	 * Count Files
	 */
    public static function count_files(){
        $files = get_posts(array(
            'post_status' => 'publish',
            'post_type' => 'pworkfiles',
            'posts_per_page'  => 9999
        ));
        return count($files);
    }

    /**
	 * Count My Files
	 */
    public static function count_my_files(){
        $files = get_posts(array(
            'post_status' => 'publish',
            'post_type' => 'pworkfiles',
            'author__in' => get_current_user_id(),
            'posts_per_page'  => 9999
        ));
        return count($files);
    }
}

/**
 * Returns the main instance of the class
 */
function PworkFiles() {  
	return PworkFiles::instance();
}
// Global for backwards compatibility
$GLOBALS['PworkFiles'] = PworkFiles();