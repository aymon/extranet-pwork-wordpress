<?php
defined( 'ABSPATH' ) || exit;

class PworkSettings {
    /* The single instance of the class */
	protected static $_instance = null;

    /* Main Instance */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /* Constructor */
    public function __construct() {
        add_action( 'cmb2_admin_init', array($this, 'register_metabox') );
        add_action( 'admin_enqueue_scripts',array($this, 'colorpicker_labels'), 99 );
        add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );
        add_action( 'cmb2_render_select_multiple', array($this, 'cmb2_render_select_multiple_field_type'), 10, 5 );
        add_filter( 'cmb2_sanitize_select_multiple', array($this, 'cmb2_sanitize_select_multiple_callback'), 10, 2 );
    }

    /* Admin Scripts */
    public function admin_scripts($hook){
        wp_enqueue_style('pwork-admin-general', PWORK_PLUGIN_URL . 'css/admin-general.css', false, PWORK_VERSION);
        if ('toplevel_page_pwork_options' == $hook)  {
            wp_enqueue_style('pwork-admin', PWORK_PLUGIN_URL . 'css/admin.css', false, PWORK_VERSION);
            wp_enqueue_script('pwork-admin', PWORK_PLUGIN_URL . 'js/admin.js', array( 'jquery' ), PWORK_VERSION, true);
        }
    }

    /**
    * Hook in and register a metabox to handle a plugin options page and adds a menu item.
    */
    public function register_metabox() {
        $slug = PworkSettings::get_option('slug', 'pwork');
        global $wp_roles;
        $roles = array();
        $all_roles = $wp_roles->roles;
        $editable_roles = apply_filters('editable_roles', $all_roles);
        foreach ($editable_roles as $role_name => $role_info) {
            $roles[$role_name] = $role_info['name'];
        }
        $args = array(
            'id'           => 'pwork_options',
            'title'        => esc_html__('Pwork', 'pwork') . ' - v' . PWORK_VERSION . ' <a href="' . get_site_url() . '/' . $slug . '/" target="_blank">' . esc_html__( 'OPEN APP', 'pwork' ) . '<span class="dashicons dashicons-external"></span></a>',
            'menu_title'   => esc_html__('PWork', 'pwork'),
            'object_types' => array( 'options-page' ),
            'option_key'   => 'pwork_options',
            'capability'      => 'manage_options',
            'position' => 26,
            'save_button'     => esc_html__( 'Save Settings', 'pwork' )
        );

        $options = new_cmb2_box( $args );

        /* TABS */
        $options->add_field( array(
            'name' => esc_html__( 'General', 'pwork' ),
            'id'   => 'general_title',
            'classes'   => array('active'),
            'type' => 'title',
            'before_row' => '<div id="pwork-tabs">'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'News', 'pwork' ),
            'id'   => 'announcements_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Knowledge Base', 'pwork' ),
            'id'   => 'kb_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Forum', 'pwork' ),
            'id'   => 'forum_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Projects', 'pwork' ),
            'id'   => 'projects_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Events', 'pwork' ),
            'id'   => 'events_title',
            'type' => 'title',
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Users', 'pwork' ),
            'id'   => 'users_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Messages', 'pwork' ),
            'id'   => 'messages_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Files', 'pwork' ),
            'id'   => 'files_title',
            'type' => 'title',
            'after_row' => '</div><div id="pwork-tab-boxes">'
        ) );

        /* GENERAL */
        $options->add_field( array(
            'name'    => esc_html__( 'Blocked Roles', 'pwork' ),
            'desc'    => esc_html__( 'Select which user roles will NOT be allowed to access the application.', 'pwork' ),
            'id'      => 'blocked_roles',
            'type'    => 'multicheck_inline',
            'options' => $roles,
            'before_row' => '<div class="pwork-tab-content active" data-id="general-title">',
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Custom URL Slug', 'pwork' ),
            'description' => esc_html__( '"Your site url + slug" is the url of the app.', 'pwork' ) . '<br><a href="' . get_site_url() . '/' . $slug . '/" target="_blank">' . get_site_url() . '/' . $slug . '/</a>',
            'id'   => 'slug',
            'type' => 'text',
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => esc_html__( 'pwork', 'pwork' )
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Logo', 'pwork' ),
            'id'      => 'logo',
            'type'    => 'file',
            'query_args' => array(
                'type' => array(
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'preview_size' => 'medium',
            'default' => PWORK_PLUGIN_URL . 'assets/logo.webp',
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Primary Color', 'pwork' ),
            'id'      => 'primary_color',
            'type'    => 'colorpicker',
            'default' => '#6658ea'
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Secondary Color', 'pwork' ),
            'id'      => 'secondary_color',
            'type'    => 'colorpicker',
            'default' => '#5546e8'
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Background Color', 'pwork' ),
            'id'      => 'bg_color',
            'type'    => 'colorpicker',
            'default' => '#f5f5f9'
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Background Image', 'pwork' ),
            'id'      => 'bg_img',
            'type'    => 'file',
            'query_args' => array(
                'type' => array(
                    'image/jpeg',
                    'image/png',
                ),
            ),
            'preview_size' => 'medium',
            'default' => '',
        ) );

        $options->add_field( array(
            'id'      => 'email_module',
            'name'   => esc_html__( 'Email Notifications', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ));

        $options->add_field( array(
            'id'      => 'live_notifications',
            'name'   => esc_html__( 'Live Notifications', 'pwork' ),
            'description'   => esc_html__( 'If enabled, the script checks for new notifications with AJAX every minute and shows new notification counts in the sidebar menu.', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'disable'
        ));

        $options->add_field( array(
            'id'      => 'demo_mode',
            'name'   => esc_html__( 'Demo Mode', 'pwork' ),
            'description'   => esc_html__( 'Front-end save and publish functions do not work in demo mode.', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'on' => esc_html__( 'On', 'pwork' ),
                'off'   => esc_html__( 'Off', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'off'
        ));

        $options->add_field( array(
            'name' => esc_html__( 'Custom CSS', 'pwork' ),
            'id' => 'custom_css',
            'type' => 'textarea_code',
            'attributes' => array(
                'data-codeeditor' => json_encode( array(
                    'codemirror' => array(
                        'mode' => 'css'
                    ),
                ) ),
            ),
            'after_row' => '</div>',
        ) );

        /* ANNOUNCEMENTS */

        $options->add_field( array(
            'id'      => 'announcements_module',
            'name'   => esc_html__( 'News', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable',
            'before_row' => '<div class="pwork-tab-content" data-id="announcements-title">',
        ));

        $options->add_field( array(
            'name'    => esc_html__( 'Add Post', 'pwork' ),
            'desc'    => esc_html__( 'Choose which user roles are NOT allowed to add new posts.', 'pwork' ),
            'id'      => 'add_ann_not_allowed_roles',
            'type'    => 'multicheck_inline',
            'options' => $roles
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Number of Posts', 'pwork' ),
            'description' => esc_html__( 'Max. number of posts to show on the news page.', 'pwork' ),
            'id'   => 'anns_limit',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 12,
            'after_row' => '</div>',
        ) );

        /* ANNOUNCEMENTS */

        $options->add_field( array(
            'id'      => 'kb_module',
            'name'   => esc_html__( 'Knowledge base', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable',
            'before_row' => '<div class="pwork-tab-content" data-id="kb-title">',
        ));

        $options->add_field( array(
            'name' => esc_html__( 'Number of Articles', 'pwork' ),
            'description' => esc_html__( 'Number of articles to show on the knowledge base.', 'pwork' ),
            'id'   => 'kb_limit',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 5,
            'after_row' => '</div>',
        ) );

        /* FORUM */

        $options->add_field( array(
            'id'      => 'forum_module',
            'name'   => esc_html__( 'Forum', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable',
            'before_row' => '<div class="pwork-tab-content" data-id="forum-title">',
        ));

        $options->add_field( array(
            'name'    => esc_html__( 'Add Topic', 'pwork' ),
            'desc'    => esc_html__( 'Choose which user roles are NOT allowed to add new topics.', 'pwork' ),
            'id'      => 'add_forum_not_allowed_roles',
            'type'    => 'multicheck_inline',
            'options' => $roles
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Number of Topics', 'pwork' ),
            'description' => esc_html__( 'Max. number of topics to show on the forum.', 'pwork' ),
            'id'   => 'forum_limit',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 10,
            'after_row' => '</div>',
        ) );

        /* PROJECTS */

        $options->add_field( array(
            'id'      => 'projects_module',
            'name'   => esc_html__( 'Projects', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable',
            'before_row' => '<div class="pwork-tab-content" data-id="projects-title">',
        ));

        $options->add_field( array(
            'name'    => esc_html__( 'Add Project', 'pwork' ),
            'desc'    => esc_html__( 'Choose which user roles are NOT allowed to add new projects.', 'pwork' ),
            'id'      => 'add_project_not_allowed_roles',
            'type'    => 'multicheck_inline',
            'options' => $roles
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Number of Projects', 'pwork' ),
            'description' => esc_html__( 'Max. number of projects to show.', 'pwork' ),
            'id'   => 'projects_limit',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 12
        ) );

        $options->add_field( array(
            'id'      => 'private_projects',
            'name'   => esc_html__( 'Private Projects', 'pwork' ),
            'description' => esc_html__( 'If enabled, only the project team can access the project (Admins can access all projects).', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'disable',
            'after_row' => '</div>',
        ));

        /* EVENTS */

        $options->add_field( array(
            'id'      => 'events_module',
            'name'   => esc_html__( 'Events', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable',
            'before_row' => '<div class="pwork-tab-content" data-id="events-title">',
        ));

        $options->add_field( array(
            'name'    => esc_html__( 'Add Event', 'pwork' ),
            'desc'    => esc_html__( 'Choose which user roles are NOT allowed to add events.', 'pwork' ),
            'id'      => 'add_event_not_allowed_roles',
            'type'    => 'multicheck_inline',
            'options' => $roles
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Default Event Color', 'pwork' ),
            'id'      => 'event_color',
            'type'    => 'colorpicker',
            'default' => '#6658ea'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Calendar Locale', 'pwork' ),
            'id'   => 'calendar_lang',
            'type' => 'select',
            'options' => array(
                'en' => esc_html__( 'English', 'pwork' ),
                'en-gb' => esc_html__( 'English (United Kingdom)', 'pwork' ),
                'en-au' => esc_html__( 'English (Australia)', 'pwork' ),
                'en-nz' => esc_html__( 'English (New Zealand)', 'pwork' ),
                'pt' => esc_html__( 'Portuguese', 'pwork' ),
                'pt-br' => esc_html__( 'Portuguese (Brazil)', 'pwork' ),
                'es' => esc_html__( 'Spanish', 'pwork' ),
                'ca' => esc_html__( 'Catalan', 'pwork' ),
                'de' => esc_html__( 'German', 'pwork' ),
                'de-at' => esc_html__( 'German (Audtria)', 'pwork' ),
                'it' => esc_html__( 'Italian', 'pwork' ),
                'fr' => esc_html__( 'French', 'pwork' ),
                'fr-ch' => esc_html__( 'French (Switzerland)', 'pwork' ),
                'sv' => esc_html__( 'Swedish', 'pwork' ),
                'pl' => esc_html__( 'Polish', 'pwork' ),
                'nl' => esc_html__( 'Dutch', 'pwork' ),
                'hu' => esc_html__( 'Hungarian', 'pwork' ),
                'cs' => esc_html__( 'Czech', 'pwork' ),
                'da' => esc_html__( 'Danish', 'pwork' ),
                'fi' => esc_html__( 'Finnish', 'pwork' ),
                'tr' => esc_html__( 'Turkish', 'pwork' ),
                'bg' => esc_html__( 'Bulgarian', 'pwork' ),
                'el' => esc_html__( 'Greek', 'pwork' ),
                'ro' => esc_html__( 'Romanian', 'pwork' ),
                'sk' => esc_html__( 'Slovak', 'pwork' ),
                'ru' => esc_html__( 'Russian', 'pwork' ),
                'ja' => esc_html__( 'Japanese', 'pwork' ),
                'zh-cn' => esc_html__( 'Chinese (S)', 'pwork' ),
                'zh-tw' => esc_html__( 'Chinese (T)', 'pwork' ),
                'ko' => esc_html__( 'Korean', 'pwork' ),
                'th' => esc_html__( 'Thai', 'pwork' ),
                'id' => esc_html__( 'Indonesian', 'pwork' ),
                'vi' => esc_html__( 'Vietnamese', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'en',
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Number of Events', 'pwork' ),
            'description' => esc_html__( 'Max. number of events to show on my events page.', 'pwork' ),
            'id'   => 'event_limit',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 5,
            'after_row' => '</div>',
        ) );

        /* USERS */

        $options->add_field( array(
            'id'      => 'user_directory_module',
            'name'   => esc_html__( 'User Directory', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable',
            'before_row' => '<div class="pwork-tab-content" data-id="users-title">',
        ));

        $options->add_field( array(
            'name'    => esc_html__( 'Exclude From Search', 'pwork' ),
            'desc'    => esc_html__( 'Choose which user roles to exclude from search results.', 'pwork' ),
            'id'      => 'excluded_roles',
            'type'    => 'multicheck_inline',
            'options' => $roles
        ) );

        $options->add_field( array(
            'id'      => 'user_avatar',
            'name'   => esc_html__( 'Avatar Image Upload', 'pwork' ),
            'desc'    => esc_html__( 'Allow users to upload their own avatar images from settings.', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'disable'
        ));

        $options->add_field( array(
            'id'      => 'show_user_roles',
            'name'   => esc_html__( 'Show Roles', 'pwork' ),
            'desc'    => esc_html__( 'Show the user role on the user profile page.', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Yes', 'pwork' ),
                'disable'   => esc_html__( 'No', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ));

        $options->add_field( array(
            'id'      => 'show_user_reg',
            'name'   => esc_html__( 'Show Registration Date', 'pwork' ),
            'desc'    => esc_html__( 'Show the user registration date on the user profile page.', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Yes', 'pwork' ),
                'disable'   => esc_html__( 'No', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ));

        $options->add_field( array(
            'name'    => esc_html__( 'User Card Info', 'pwork' ),
            'desc'    => esc_html__( 'Choose what user information to display on the Users page.', 'pwork' ),
            'id'      => 'user_info_field',
            'type' => 'select',
            'options' => array(
                'none' => esc_html__('None', 'pwork' ),
                'member_since' => esc_html__('Member since', 'pwork' ),
                'job' => esc_html__('Job title', 'pwork' ),
                'user_role' => esc_html__('User role', 'pwork' ),
                'location' => esc_html__('Location (If it exists)', 'pwork' ),
                'social_media' => esc_html__('Social media buttons (If it exists)', 'pwork' ),
                'tel' => esc_html__('Phone number (If it exists and is open to the public)', 'pwork' ),
                'email' => esc_html__( 'Email address (If it exists and is open to the public)', 'pwork' ),
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'member_since'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Number of Users', 'pwork' ),
            'description' => esc_html__( 'Max. number of users to show.', 'pwork' ),
            'id'   => 'user_limit',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 12
        ) );

        $options->add_field( array(
            'name'    => esc_html__( 'Sort Users by', 'pwork' ),
            'id'      => 'sort_users_by',
            'type' => 'select',
            'options' => array(
                'asc' => esc_html__( 'Name from A to Z', 'pwork' ),
                'desc' => esc_html__( 'Name from Z to A', 'pwork' ),
                'newest' => esc_html__('Newest', 'pwork' ),
                'oldest' => esc_html__('Oldest', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'asc',
            'after_row' => '</div>',
        ) );

        /* MESSAGES */

        $options->add_field( array(
            'id'      => 'contacts_module',
            'name'   => esc_html__( 'My Contacts', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable',
            'before_row' => '<div class="pwork-tab-content" data-id="messages-title">',
        ));

        $options->add_field( array(
            'id'      => 'messages_module',
            'name'   => esc_html__( 'Messages', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ));

        $options->add_field( array(
            'name'    => esc_html__( 'Send Message', 'pwork' ),
            'desc'    => esc_html__( 'Choose which user roles are NOT allowed to send and receive messages.', 'pwork' ),
            'id'      => 'message_not_allowed_roles',
            'type'    => 'multicheck_inline',
            'options' => $roles
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Delete Old Messages', 'pwork' ),
            'description' => esc_html__( 'By default, messages older than 365 days are automatically deleted. To disable it, enter 0.', 'pwork' ),
            'id'   => 'delete_old_messages',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 365,
            'after_row' => '</div>',
        ) );

        /* FILES */

        $options->add_field( array(
            'id'      => 'files_module',
            'name'   => esc_html__( 'File Library', 'pwork' ),
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'pwork' ),
                'disable'   => esc_html__( 'Disable', 'pwork' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable',
            'before_row' => '<div class="pwork-tab-content" data-id="files-title">',
        ));

        $options->add_field( array(
            'name'    => esc_html__( 'File Upload', 'pwork' ),
            'desc'    => esc_html__( 'Choose which user roles are NOT allowed to upload files to the library.', 'pwork' ),
            'id'      => 'upload_not_allowed_roles',
            'type'    => 'multicheck_inline',
            'options' => $roles
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Number of Files', 'pwork' ),
            'description' => esc_html__( 'Max. number of files to show.', 'pwork' ),
            'id'   => 'file_limit',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*',
                'autocomplete' => 'off'
            ),
            'default' => 10,
            'after_row' => '</div>',
        ) );
            
    }
    /**
    * Colorpicker Labels
    */
    public function colorpicker_labels( $hook ) {
        global $wp_version;
        if( version_compare( $wp_version, '5.4.2' , '>=' ) ) {
            wp_localize_script(
            'wp-color-picker',
            'wpColorPickerL10n',
            array(
                'clear'            => esc_html__( 'Clear', 'pwork' ),
                'clearAriaLabel'   => esc_html__( 'Clear color', 'pwork' ),
                'defaultString'    => esc_html__( 'Default', 'pwork' ),
                'defaultAriaLabel' => esc_html__( 'Select default color', 'pwork' ),
                'pick'             => esc_html__( 'Select Color', 'pwork' ),
                'defaultLabel'     => esc_html__( 'Color value', 'pwork' )
            )
            );
        }
    }

    /**
    * Pwork get option
    */
    static function get_option( $key = '', $default = false ) {
        if ( function_exists( 'cmb2_get_option' ) ) {
            return cmb2_get_option( 'pwork_options', $key, $default );
        }
        $opts = get_option( 'pwork_options', $default );
        $val = $default;
        if ( 'all' == $key ) {
            $val = $opts;
        } elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
            $val = $opts[ $key ];
        }
        return $val;
    }

    /**
     * CMB2 Select Multiple Custom Field Type
     * @package CMB2 Select Multiple Field Type
     */

    public function cmb2_render_select_multiple_field_type( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
        $select_multiple = '<select class="widefat" multiple name="' . $field->args['_name'] . '[]" id="' . $field->args['_id'] . '"';
        foreach ( $field->args['attributes'] as $attribute => $value ) {
            $select_multiple .= " $attribute=\"$value\"";
        }
        $select_multiple .= ' />';

        foreach ( $field->options() as $value => $name ) {
            $selected = ( $escaped_value && in_array( $value, $escaped_value ) ) ? 'selected="selected"' : '';
            $select_multiple .= '<option class="cmb2-option" value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>';
        }

        $select_multiple .= '</select>';
        $select_multiple .= $field_type_object->_desc( true );

        echo $select_multiple; // WPCS: XSS ok.
    }


    /**
     * Sanitize the selected value.
     */
    public function cmb2_sanitize_select_multiple_callback( $override_value, $value ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $key => $saved_value ) {
                $value[$key] = sanitize_text_field( $saved_value );
            }
            return $value;
        }

        return;
    }

}

/**
 * Returns the main instance of the class.
 */
function PworkSettings() {  
	return PworkSettings::instance();
}
// Global for backwards compatibility.
$GLOBALS['PworkSettings'] = PworkSettings();