<?php
defined( 'ABSPATH' ) || exit;

class Pwork {
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
        $custom_avatar = PworkSettings::get_option('user_avatar', 'disable');
        add_action('init', array($this, 'init'), 1);
        add_action('init', array($this, 'load_app'), 2);
        add_action('pwork_head', array($this, 'styles'));
        add_action('pwork_head', 'wp_site_icon');
        add_action('pwork_body_end', array($this, 'scripts'));
        add_action('wp_login', array($this, 'user_last_login'), 10, 2);
        if ($custom_avatar == 'enable') {
            add_filter('get_avatar', array($this, 'get_avatar'), 10, 5);
        }
        add_action('cmb2_init', array($this, 'additional_user_fields'));
        add_filter( 'user_search_columns', function( $search_columns ) {
            $search_columns[] = 'display_name';
            return $search_columns;
        } );
        add_action('wp_ajax_pworkSaveSettings', array($this, 'save_user_settings'));
        add_action('wp_ajax_pworkSearchUsers', array($this, 'search_users'));
        add_action('wp_ajax_pworkLoadMoreUsers', array($this, 'load_more_users'));
        add_action('wp_ajax_pworkAddContact', array($this, 'add_contact'));
        add_action('wp_ajax_pworkRemoveContact', array($this, 'remove_contact'));
        add_action('wp_ajax_pworkGetNotifications', array($this, 'get_notifications'));
    }

    /**
	 * Init
	 */
    public function init() {
        load_plugin_textdomain( 'pwork', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        register_nav_menus(
            array(
                'pwork-side-menu' => esc_html__( 'Pwork - Sidebar Menu', 'pwork' )
            )
        );
        remove_filter('pre_user_description', 'wp_filter_kses');
        add_filter('pre_user_description', 'wp_kses_post', 10);
    }

    /**
	 * Add plugin links to plugins page on the admin dashboard
	 */
    public function plugin_links($links_array, $plugin_file_name, $plugin_data, $status) {
        if ( strpos( $plugin_file_name, 'pwork.php' ) !== false ) {
            $links_array[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=pwork_options') ) .'">' . esc_html__( 'Settings', 'pwork' ) . '</a>';
            $links_array[] = '<a href="https://palleon.website/pwork/documentation/" target="_blank">' . esc_html__( 'Documentation', 'pwork' ) . '</a>';
        }
        return $links_array;
    }

    /**
	 * HTML Compress
	 */
    public static function ob_html_compress($buf){
        return preg_replace(array('/<!--(.*)-->/Uis',"/[[:blank:]]+/"),array('',' '),str_replace(array("\n","\r","\t"),'',$buf));
    }

    /**
	 * Get valid pages
	 */
    public static function get_valid_pages(){
        $user = wp_get_current_user();
        $slug = PworkSettings::get_option('slug', 'pwork');
        $user_directory = PworkSettings::get_option('user_directory_module', 'enable');
        $events = PworkSettings::get_option('events_module', 'enable');
        $files = PworkSettings::get_option('files_module', 'enable');
        $forum = PworkSettings::get_option('forum_module', 'enable');
        $anns = PworkSettings::get_option('announcements_module', 'enable');
        $kb = PworkSettings::get_option('kb_module', 'enable');
        $messages = PworkSettings::get_option('messages_module', 'enable');
        $contacts = PworkSettings::get_option('contacts_module', 'enable');
        $projects = PworkSettings::get_option('projects_module', 'enable');
        $upload_roles = PworkSettings::get_option('upload_not_allowed_roles', array());
        $event_roles = PworkSettings::get_option('add_event_not_allowed_roles', array());
        $ann_roles = PworkSettings::get_option('add_ann_not_allowed_roles', array());
        $forum_roles = PworkSettings::get_option('add_forum_not_allowed_roles', array());
        $message_roles = PworkSettings::get_option('message_not_allowed_roles', array());
        $project_roles = PworkSettings::get_option('add_project_not_allowed_roles', array());
        $private_projects = PworkSettings::get_option('private_projects', 'disable');

        $pages = apply_filters('pworkGetAllowedPages',array('dashboard','profile','settings'));

        if ($user_directory == 'enable') {
            array_push($pages, 'users');
        }
        if ($events == 'enable') {
            array_push($pages, 'events');
            if (!array_intersect( $event_roles, $user->roles )) {
                array_push($pages, 'events-manage');
            }
        }
        if ($files == 'enable') {
            array_push($pages, 'files');
            array_push($pages, 'file-edit');
            array_push($pages, 'files-tag');
            if (!array_intersect( $upload_roles, $user->roles )) {
                array_push($pages, 'files-my');
            }
        }
        if ($anns == 'enable') {
            array_push($pages, 'announcements');
            array_push($pages, 'announcements-single');
            if (!array_intersect( $ann_roles, $user->roles )) {
                array_push($pages, 'announcements-manage');
            }
        }
        if ($kb == 'enable') {
            array_push($pages, 'knowledgebase');
            array_push($pages, 'knowledgebase-single');
            array_push($pages, 'knowledgebase-search');
        }
        if ($projects == 'enable') {
            if ($private_projects == 'disable' || current_user_can('administrator')) {
                array_push($pages, 'projects');
            }
            array_push($pages, 'projects-my');
            array_push($pages, 'projects-single');
            if (!array_intersect( $project_roles, $user->roles )) {
                array_push($pages, 'projects-manage');
            }
        }
        if ($forum == 'enable') {
            array_push($pages, 'forum');
            array_push($pages, 'forum-tag');
            if (!array_intersect( $forum_roles, $user->roles )) {
                array_push($pages, 'forum-manage');
            }
        }
        if ($messages == 'enable') {
            if (!array_intersect( $message_roles, $user->roles )) {
                array_push($pages, 'messages');
            }
        }
        if ($contacts == 'enable') {
            if (!array_intersect( $message_roles, $user->roles )) {
                array_push($pages, 'contacts');
            }
        }

        return $pages;
    }

    /**
	 * Get pages
	 */
    public static function get_pages(){
        $user = wp_get_current_user();
        $slug = PworkSettings::get_option('slug', 'pwork');
        $events =  PworkSettings::get_option('events_module', 'enable');
        $files =  PworkSettings::get_option('files_module', 'enable');
        $user_directory =  PworkSettings::get_option('user_directory_module', 'enable');
        $forum =  PworkSettings::get_option('forum_module', 'enable');
        $messages = PworkSettings::get_option('messages_module', 'enable');
        $contacts = PworkSettings::get_option('contacts_module', 'enable');
        $projects = PworkSettings::get_option('projects_module', 'enable');
        $announcements =  PworkSettings::get_option('announcements_module', 'enable');
        $kb =  PworkSettings::get_option('kb_module', 'enable');
        $upload_roles =  PworkSettings::get_option('upload_not_allowed_roles', array());
        $event_roles =  PworkSettings::get_option('add_event_not_allowed_roles', array());
        $ann_roles =  PworkSettings::get_option('add_ann_not_allowed_roles', array());
        $forum_roles =  PworkSettings::get_option('add_forum_not_allowed_roles', array());
        $message_roles =  PworkSettings::get_option('message_not_allowed_roles', array());
        $project_roles = PworkSettings::get_option('add_project_not_allowed_roles', array());
        $private_projects = PworkSettings::get_option('private_projects', 'disable');
        $userID = get_current_user_id();
        $app_url = get_site_url() . '/' . $slug . '/';

        $pages = apply_filters('pworkGetPages',array(
            'dashboard' => array(esc_html__('Dashboard', 'pwork'), 'bxs-dashboard', esc_url($app_url), '', array()),
            'announcements' => array(esc_html__('News', 'pwork'), 'bxs-megaphone', esc_url(get_site_url() . '/' . $slug . '/?page=announcements'), '', array()),
            'forum' => array(esc_html__('Forum', 'pwork'), 'bx-conversation', esc_url($app_url . '?page=forum'), '', array()),
            'projects' => array(esc_html__('Projects', 'pwork'), 'bx-rocket', esc_url($app_url . '?page=projects'), '', array()),
            'events' => array(esc_html__('Events', 'pwork'), 'bx-calendar', esc_url($app_url . '?page=events'), '', array()),
            'users' => array(esc_html__('User Directory', 'pwork'), 'bx-group', esc_url($app_url . '?page=users'), '', array()),
            'messages' => array(esc_html__('Messages', 'pwork'), 'bx-message-dots', esc_url($app_url . '?page=messages'), '', array()),
            'files' => array(esc_html__('File Library', 'pwork'), 'bxs-server', esc_url($app_url . '?page=files'), '', array()),
            'knowledgebase' => array(esc_html__('Knowledge Base', 'pwork'), 'bx-help-circle', esc_url(get_site_url() . '/' . $slug . '/?page=knowledgebase'), '', array()),
            'profile' => array(esc_html__('My Profile', 'pwork'), 'bx-user', esc_url($app_url . '?page=profile&userID=' . $userID), esc_html__('My Account', 'pwork'), array()),
            'contacts' => array(esc_html__('My Contacts', 'pwork'), 'bxs-contact', esc_url($app_url . '?page=contacts'), '', array()),
            'settings' => array(esc_html__('Settings', 'pwork'), 'bx-cog', esc_url($app_url . '?page=settings'), '', array()),
        ));
        if (!array_intersect( $upload_roles, $user->roles )) { 
            $pages['files'][4] = array(
                array('files', esc_html__('All Files', 'pwork'), esc_url($app_url . '?page=files')),
                array('files-my', esc_html__('My Files', 'pwork'), esc_url($app_url . '?page=files-my')),
            );
        }
        if (!array_intersect( $event_roles, $user->roles )) { 
            $pages['events'][4] = array(
                array('events', esc_html__('Events Calendar', 'pwork'), esc_url($app_url . '?page=events')),
                array('events-manage', esc_html__('Manage Events', 'pwork'), esc_url($app_url . '?page=events-manage')),
            );
        }
        if (!array_intersect( $ann_roles, $user->roles )) { 
            $pages['announcements'][4] = array(
                array('announcements', esc_html__('All Posts', 'pwork'), esc_url($app_url . '?page=announcements')),
                array('announcements-manage', esc_html__('Manage Posts', 'pwork'), esc_url($app_url . '?page=announcements-manage')),
            );
        }
        if (!array_intersect( $forum_roles, $user->roles )) { 
            $pages['forum'][4] = array(
                array('forum', esc_html__('All Topics', 'pwork'), esc_url($app_url . '?page=forum')),
                array('forum-manage', esc_html__('Manage Topics', 'pwork'), esc_url($app_url . '?page=forum-manage'))
            );
        }
        if (!array_intersect( $project_roles, $user->roles )) { 
            if ($private_projects == 'disable' || current_user_can('administrator')) {
                $pages['projects'][4] = array(
                    array('projects', esc_html__('All Projects', 'pwork'), esc_url($app_url . '?page=projects')),
                    array('projects-my', esc_html__('My Projects', 'pwork'), esc_url($app_url . '?page=projects-my')),
                    array('projects-manage', esc_html__('Manage Projects', 'pwork'), esc_url($app_url . '?page=projects-manage'))
                );
            } else {
                $pages['projects'][4] = array(
                    array('projects-my', esc_html__('My Projects', 'pwork'), esc_url($app_url . '?page=projects-my')),
                    array('projects-manage', esc_html__('Manage Projects', 'pwork'), esc_url($app_url . '?page=projects-manage'))
                );
            }
        } else {
            if ($private_projects == 'disable' || current_user_can('administrator')) {
                $pages['projects'][4] = array(
                    array('projects', esc_html__('All Projects', 'pwork'), esc_url($app_url . '?page=projects')),
                    array('projects-my', esc_html__('My Projects', 'pwork'), esc_url($app_url . '?page=projects-my'))
                );
            } else {
                $pages['projects'][4] = array(
                    array('projects-my', esc_html__('My Projects', 'pwork'), esc_url($app_url . '?page=projects-my'))
                );
            }
        }
        if ($user_directory != 'enable') {
            unset($pages['users']);
        }
        if ($events != 'enable') {
            unset($pages['events']);
        }
        if ($files != 'enable') {
            unset($pages['files']);
        }
        if ($announcements != 'enable') {
            unset($pages['announcements']);
        }
        if ($kb != 'enable') {
            unset($pages['knowledgebase']);
        }
        if ($forum != 'enable') {
            unset($pages['forum']);
        }
        if ($projects != 'enable') {
            unset($pages['projects']);
        }
        if ($messages != 'enable' || array_intersect( $message_roles, $user->roles )) {
            unset($pages['messages']);
        }
        if ($contacts != 'enable') {
            unset($pages['contacts']);
        }
        return $pages;
    }

    /**
	 * Page Output
	 */
    public static function page_output($page){
        if (empty($page)) {
            include(__DIR__ . '/template-parts/dashboard.php');
        } else {
            $pages = self::get_valid_pages();
            if (in_array($page, $pages)) {
                include(__DIR__ . '/template-parts/' . $page . '.php');
            } else {
                include(__DIR__ . '/template-parts/404.php');
            }
        }
    }

    /**
     * Catches our url slug. If it’s there, we’ll stop the
     * rest of WordPress from loading and load the app
     */
    public function load_app() {
        $page = 'dashboard';
        $user = wp_get_current_user();
        $slug =  PworkSettings::get_option('slug', 'pwork');
        $blocked_roles =  PworkSettings::get_option('blocked_roles', array());
        $requestURI = $_SERVER['REQUEST_URI'];
        if($requestURI && !empty($requestURI) && !is_admin() && !wp_doing_ajax()) {
            $pieces = explode("/", $requestURI );
            if (in_array($slug, $pieces)) {
                do_action('pwork_load_app');
                if (is_user_logged_in()) {
                    if (array_intersect( $blocked_roles, $user->roles )) { 
                        wp_die(esc_html__('You are not allowed to access this page.', 'pwork'));
                    } else {
                        if(isset($_GET['page']) && !empty($_GET['page'])) {
                            $page = $_GET['page'];
                        }
                        ob_start(array($this,'ob_html_compress'));
                        $this->page_output($page);
                        ob_end_flush();
                    }
                } else {
                    wp_redirect(wp_login_url(get_site_url() . '/' . $slug . '/'));
                }
                exit();
            }
        } else {
            return;
        }
    }

    /**
	 * Scripts and Styles.
     * Since we've created a bare-bones separate page for the app, there is no point to use "enqueue_styles" or "enqueue_scripts".
     * Please see "load_app" function above.
	 */

    /**
	 * Get Stylesheets
	 */
    public static function get_stylesheets(){
        $styles = apply_filters('pworkStylesheets',array(
            'google-fonts' => array('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;1,400;1,500&display=swap', ''),
            'bootstrap' => array(PWORK_PLUGIN_URL . 'css/bootstrap.css', PWORK_VERSION),
            'plugins' => array(PWORK_PLUGIN_URL . 'css/plugins.min.css', PWORK_VERSION),
            'style' => array(PWORK_PLUGIN_URL . 'css/style.css', PWORK_VERSION)
        ));
        return $styles;
    }

    /**
	 * Print Styles
	 */
    public function styles(){
        $stylesheets = self::get_stylesheets();
        foreach($stylesheets as $id => $key) { 
            $ver = '';
            if (!empty($key[1])) {
                $ver = '?ver=' . $key[1];
            }
            echo '<link id="pwork-' . esc_attr($id) . '-css" href="' . esc_url($key[0] . $ver) . '" rel="stylesheet" type="text/css">';
        }
        $primary_color = PworkSettings::get_option('primary_color','#6658ea');
        $secondary_color = PworkSettings::get_option('secondary_color','#5546e8');
        $bg_color = PworkSettings::get_option('bg_color','#f5f5f9');
        $bg_img = PworkSettings::get_option('bg_img', '');
        $custom_css = PworkSettings::get_option('custom_css','');
        $inline_style = '';

        if (!empty($primary_color) && !empty($secondary_color)) {
            $inline_style .= ':root {--pw-primary: ' . $primary_color . ';--pw-secondary: ' . $secondary_color . ';}';
        }

        if (!empty($bg_color) && $bg_color != '#f5f5f9') {
            $inline_style .= 'body {background-color: ' . $bg_color . ';}';
        }

        if (!empty($bg_img)) {
            $inline_style .= 'body {background-image:url(' . $bg_img . ');}';
        }

        if (!empty($custom_css)) {
            $inline_style .= $custom_css;
        }
        echo '<style>' . $inline_style . '</style>';
    }

    /**
	 * Get Scripts
	 */
    public static function get_scripts(){
        $calendar_lang = PworkSettings::get_option('calendar_lang','en');
        $demo_mode = PworkSettings::get_option('demo_mode','off');
        $scripts = array(
            'jquery' => array(PWORK_PLUGIN_URL . 'js/jquery.js', '3.6.3'),
            'plugins' => array(PWORK_PLUGIN_URL . 'js/plugins.min.js', PWORK_VERSION),
            'bootstrap' => array(PWORK_PLUGIN_URL . 'js/bootstrap.js', PWORK_VERSION),
            'init' => array(PWORK_PLUGIN_URL . 'js/init.js', PWORK_VERSION),
            'main' => array(PWORK_PLUGIN_URL . 'js/main.js', PWORK_VERSION),
        );
        if ($demo_mode == 'on' && !current_user_can('administrator')) {
            $scripts['demo'] = array(PWORK_PLUGIN_URL . 'js/demo-mode.js', PWORK_VERSION);
        }
        if (!isset($_GET['page']) || (isset($_GET['page']) && $_GET['page'] == 'events')) {
            $scripts['fullcalendar'] = array(PWORK_PLUGIN_URL . 'js/fullcalendar.min.js', PWORK_VERSION);
            if ($calendar_lang != 'en') {
                $scripts['fullcalendarlocale'] = array(PWORK_PLUGIN_URL . 'js/fullcalendar-locale.min.js', PWORK_VERSION);
            }
        }
        if (!isset($_GET['page']) || (isset($_GET['page']) && ($_GET['page'] == 'projects') ||  $_GET['page'] == 'projects-my') ||  $_GET['page'] == 'announcements') {
            $scripts['masonry'] = array(PWORK_PLUGIN_URL . 'js/masonry.min.js', PWORK_VERSION);
        }
        if (!isset($_GET['page'])) {
            $scripts['sortable'] = array(PWORK_PLUGIN_URL . 'js/sortable.min.js', PWORK_VERSION);
            $scripts['dashboard'] = array(PWORK_PLUGIN_URL . 'js/dashboard.js', PWORK_VERSION);
        }
        if (isset($_GET['page']) && $_GET['page'] == 'announcements') {
            $scripts['announcements'] = array(PWORK_PLUGIN_URL . 'js/announcements.js', PWORK_VERSION);
        }
        if (isset($_GET['page']) && $_GET['page'] == 'events') {
            $scripts['events'] = array(PWORK_PLUGIN_URL . 'js/events.js', PWORK_VERSION);
        }
        if (isset($_GET['page']) && ($_GET['page'] == 'projects' || $_GET['page'] == 'projects-my')) {
            $scripts['projects'] = array(PWORK_PLUGIN_URL . 'js/projects.js', PWORK_VERSION);
        }
        $scripts = apply_filters('pworkScripts', $scripts);
        return $scripts;
    }

    /**
	 * Print Scripts
	 */
    public function scripts(){
        $user_id = get_current_user_id();
        $scripts = self::get_scripts();
        $social_media_list = self::social_media_list();
        $calendar_lang = PworkSettings::get_option('calendar_lang','en');
        $event_color = PworkSettings::get_option('event_color', '#6658ea');
        $live_notifications = PworkSettings::get_option('live_notifications','disable');
        ?>
        <script>
        /* <![CDATA[ */
        var pworkParams = {
            "baseURL": "<?php echo PWORK_PLUGIN_URL; ?>",
            "calendarLocale": "<?php echo esc_js($calendar_lang); ?>",
            "liveNotifications": "<?php echo esc_js($live_notifications); ?>",
            "eventColor": "<?php echo esc_js($event_color); ?>",
            "userid": "<?php echo esc_js(get_current_user_id()); ?>",
            "ajaxurl":"<?php echo admin_url( 'admin-ajax.php' ); ?>",
            "nonce":"<?php echo wp_create_nonce('pwork-nonce'); ?>",
            "demoTitle": "<?php echo esc_html__('Demo mode is active.', 'pwork'); ?>",
            "demoContent": "<?php echo esc_html__('Save and publish functions are disabled for the demo.', 'pwork'); ?>",
            "demoContentAlt": "<?php echo esc_html__('Add to contacts and remove from contacts functions are disabled for the demo.', 'pwork'); ?>",
            "error": "<?php echo esc_html__('Error', 'pwork'); ?>",
            "success": "<?php echo esc_html__('Success', 'pwork'); ?>",
            "settingsaved": "<?php echo esc_html__('Settings saved.', 'pwork'); ?>",
            "titlereq": "<?php echo esc_html__('Title is required.', 'pwork'); ?>",
            "nicknamereq": "<?php echo esc_html__('Nickname is required.', 'pwork'); ?>",
            "firstnamereq": "<?php echo esc_html__('First name is required.', 'pwork'); ?>",
            "lastnamereq": "<?php echo esc_html__('Last name is required.', 'pwork'); ?>",
            "emailreq": "<?php echo esc_html__('Email is required.', 'pwork'); ?>",
            "emailnotvalid": "<?php echo esc_html__('The email you entered is not valid.', 'pwork'); ?>",
            "error1": "<?php echo esc_html__('The message field cannot be left blank.', 'pwork'); ?>",
            "error4": "<?php echo esc_html__('Maximum allowed image size is 512x512px.', 'pwork'); ?>",
            "error5": "<?php echo esc_html__('Avatar must be a square image.', 'pwork'); ?>",
            "loading": "<?php echo esc_html__('LOADING...', 'pwork'); ?>",
            "loadmore": "<?php echo esc_html__('LOAD MORE', 'pwork'); ?>",
            "wrong": "<?php echo esc_html__('Something went wrong.', 'pwork'); ?>",
            "nothing": "<?php echo esc_html__('Nothing found.', 'pwork'); ?>",
            "answer":"<?php echo esc_html__('Are you sure you want to delete the file permanently?', 'pwork'); ?>",
            "answer2":"<?php echo esc_html__('Are you sure you want to delete the event permanently?', 'pwork'); ?>",
            "answer3":"<?php echo esc_html__('Are you sure you want to delete the announcement permanently?', 'pwork'); ?>",
            "answer4":"<?php echo esc_html__('Are you sure you want to delete the topic permanently?', 'pwork'); ?>",
            "answer5":"<?php echo esc_html__('Are you sure you want to close the task?', 'pwork'); ?>",
            "answer6":"<?php echo esc_html__('Are you sure you want to reopen the task?', 'pwork'); ?>",
            "answer7":"<?php echo esc_html__('Are you sure you want to join the project?', 'pwork'); ?>",
            "answer8":"<?php echo esc_html__('Are you sure you want to leave the project?', 'pwork'); ?>",
            "uploaded": "<?php echo esc_html__('The file has been uploaded.', 'pwork'); ?>",
            "selectfile": "<?php echo esc_html__('Please select a file.', 'pwork'); ?>",
            "eventAdded": "<?php echo esc_html__('New event added.', 'pwork'); ?>",
            "eventEdited": "<?php echo esc_html__('The event has been edited successfully.', 'pwork'); ?>",
            "fillAll": "<?php echo esc_html__('Please fill in all required fields.', 'pwork'); ?>",
            "annsAdded": "<?php echo esc_html__('New announcement added.', 'pwork'); ?>",
            "annsEdited": "<?php echo esc_html__('The announcement has been edited successfully.', 'pwork'); ?>",
            "hideReplies": "<?php echo esc_html__('Hide Replies', 'pwork'); ?>",
            "showReplies": "<?php echo esc_html__('Show Replies', 'pwork'); ?>",
            "topicDeleted": "<?php echo esc_html__('This topic has been deleted.', 'pwork'); ?>",
            "topicAdded": "<?php echo esc_html__('New topic added.', 'pwork'); ?>",
            "topicEdited": "<?php echo esc_html__('The topic has been edited successfully.', 'pwork'); ?>",
            "projectDeleted": "<?php echo esc_html__('This project has been deleted.', 'pwork'); ?>",
            "projectAdded": "<?php echo esc_html__('New project added.', 'pwork'); ?>",
            "projectEdited": "<?php echo esc_html__('The project has been edited successfully.', 'pwork'); ?>",
            "nocomments": "<?php echo esc_html__('No comments found.', 'pwork'); ?>",
            "commentSent": "<?php echo esc_html__('Your comment has been sent.', 'pwork'); ?>",
            "fileEdited": "<?php echo esc_html__('The file has been edited successfully.', 'pwork'); ?>",
        };
        /* ]]> */
        </script>
        <?php
        foreach($scripts as $id => $key) { 
            $ver = '';
            if (!empty($key[1])) {
                $ver = '?ver=' . $key[1];
            }
            echo '<script id="pwork-' . esc_attr($id) . '-js" src="' . esc_url($key[0] . $ver) . '"></script>';
        }
        if (isset($_GET['page']) && $_GET['page'] == 'settings') {
        ?>
        <script type="text/template" id="pwork-user-icons-template">
            <div class="input-group mb-3">
                <select id="pwork_user_icon_{?}" name="pwork_user_icon_{?}" class="form-select" autocomplete="off">
                    <?php 
                    foreach($social_media_list as $id => $key) { 
                        echo '<option value="' . esc_attr($id) . '">' . esc_attr($key) . '</option>';
                    } ?>
                </select>
                <input autocomplete="off" type="text" class="form-control" id="pwork_user_url_{?}" name="pwork_user_url_{?}" value="" placeholder="<?php echo esc_attr__('Enter URL', 'pwork'); ?>">
                <button type="button" class="btn btn-danger pwork-delete-user-icon"><i class="bx bx-trash"></i></button>
            </div>
        </script>
        <?php } 
        }

    /**
	 * Social media list
	 */
    public static function social_media_list(){
        $array = array(
            'facebook' => esc_html__( 'Facebook', 'pwork' ),
            'instagram' => esc_html__( 'Instagram', 'pwork' ),
            'linkedin' => esc_html__( 'Linkedin', 'pwork' ),
            'youtube' => esc_html__( 'YouTube', 'pwork' ),
            'vimeo' => esc_html__( 'Vimeo', 'pwork' ),
            'whatsapp' => esc_html__( 'Whatsapp', 'pwork' ),
            'skype' => esc_html__( 'Skype', 'pwork' ),
            'tiktok' => esc_html__( 'Tiktok', 'pwork' ),
            'twitter' => esc_html__( 'Twitter', 'pwork' ),
            'discord' => esc_html__( 'Discord', 'pwork' ),
            'behance' => esc_html__( 'Behance', 'pwork' ),
            'pinterest' => esc_html__( 'Pinterest', 'pwork' ),
            'deviantart' => esc_html__( 'Deviantart', 'pwork' ),
            'twitch' => esc_html__( 'Twitch', 'pwork' ),
            'reddit' => esc_html__( 'Reddit', 'pwork' ),
            'dribbble' => esc_html__( 'Dribbble', 'pwork' ),
            'medium' => esc_html__( 'Medium', 'pwork' ),
            'github' => esc_html__( 'Github', 'pwork' ),
            'quora' => esc_html__( 'Quora', 'pwork' ),
            'stack-overflow' => esc_html__( 'Stack Overflow', 'pwork' ),
            'blogger' => esc_html__( 'Blogger', 'pwork' ),
            'tumblr' => esc_html__( 'Tumblr', 'pwork' ),
            'slack' => esc_html__( 'Slack', 'pwork' ),
            'wordpress' => esc_html__( 'WordPress', 'pwork' ),
            'spotify' => esc_html__( 'Spotify', 'pwork' ),
            'dailymotion' => esc_html__( 'Dailymotion', 'pwork' ),
            'paypal' => esc_html__( 'Paypal', 'pwork' ),
            'amazon' => esc_html__( 'Amazon', 'pwork' ),
            'product-hunt' => esc_html__( 'Product Hunt', 'pwork' ),
        );
        asort($array);
        return $array;
    }

    /**
	 * Save settings
	 */
    public function save_user_settings() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $current_user = wp_get_current_user();

        if (isset($_POST['email']) && !empty($_POST['email'])) {
            if (!is_email($_POST['email'])) {
                echo esc_html__('The email you entered is not valid.', 'pwork');
                wp_die();
            } else if(email_exists(sanitize_email( $_POST['email'] )) && (sanitize_email( $_POST['email'] ) != $current_user->user_email)) {
                echo esc_html__('This email is already used by another user. Try a different one.', 'pwork');
                wp_die();
            } else {
                wp_update_user( array ('ID' => $current_user->ID, 'user_email' => sanitize_email( $_POST['email'] )));
            }
        }

        if (isset($_POST['displayname'])) {
            $display_name = get_user_meta($current_user->ID, 'nickname', true);
            if ($_POST['displayname'] == 'display_firstname') {
                $display_name = get_user_meta($current_user->ID, 'first_name', true);
            } else if ($_POST['displayname'] == 'display_lastname') {
                $display_name = get_user_meta($current_user->ID, 'last_name', true);
            } else if ($_POST['displayname'] == 'display_firstlast') {
                $display_name = get_user_meta($current_user->ID, 'first_name', true) . ' ' . get_user_meta($current_user->ID, 'last_name', true);
            } else if ($_POST['displayname'] == 'display_lastfirst') {
                $display_name = get_user_meta($current_user->ID, 'last_name', true) . ' ' . get_user_meta($current_user->ID, 'first_name', true);
            }
            wp_update_user(array('ID' => $current_user->ID, 'display_name' => esc_attr( $display_name )));
            update_user_meta($current_user->ID, 'display_name' , esc_attr( $display_name ));
            update_user_meta($current_user->ID, 'pwork_displayname', sanitize_text_field( $_POST['displayname'] ) );
        }

        if (isset($_POST['nickname']) && !empty($_POST['nickname'])) {
            update_user_meta($current_user->ID, 'nickname', sanitize_text_field( $_POST['nickname'] ) );
        } 

        if (isset($_POST['firstname'])) {
            update_user_meta($current_user->ID, 'first_name', sanitize_text_field( $_POST['firstname'] ) );
        }

        if (isset($_POST['lastname'])) {
            update_user_meta($current_user->ID, 'last_name', sanitize_text_field( $_POST['lastname'] ) );
        }

        if (isset($_POST['bio'])) {
            update_user_meta($current_user->ID, 'description', wp_kses_post( $_POST['bio'] ));
        }

        if (isset($_POST['avatar']) && !empty($_POST['avatar'])) {
            update_user_meta($current_user->ID, 'pwork_user_avatar_data', sanitize_text_field( $_POST['avatar'] ) );
        }

        if (isset($_POST['location'])) {
            update_user_meta($current_user->ID, 'pwork_location', sanitize_text_field( $_POST['location'] ) );
        }

        if (isset($_POST['job'])) {
            update_user_meta($current_user->ID, 'pwork_job', sanitize_text_field( $_POST['job'] ) );
        }

        if (isset($_POST['date'])) {
            update_user_meta($current_user->ID, 'pwork_birth_date', sanitize_text_field( $_POST['date'] ) );
        }

        if (isset($_POST['tel'])) {
            update_user_meta($current_user->ID, 'pwork_tel', sanitize_text_field( $_POST['tel'] ) );
        }

        if (isset($_POST['icons'])) {
            update_user_meta($current_user->ID, 'pwork_icons', sanitize_text_field( $_POST['icons'] ) );
        }

        if (isset($_POST['phonecheck'])) {
            update_user_meta($current_user->ID, 'pwork_phone_check', sanitize_text_field( $_POST['phonecheck'] ) );
        }

        if (isset($_POST['emailcheck'])) {
            update_user_meta($current_user->ID, 'pwork_email_check', sanitize_text_field( $_POST['emailcheck'] ) );
        }

        if (isset($_POST['contactcheck'])) {
            update_user_meta($current_user->ID, 'pwork_contact_check', sanitize_text_field( $_POST['contactcheck'] ) );
        }

        if (isset($_POST['customnot'])) {
            update_user_meta($current_user->ID, 'pwork_customnot', sanitize_text_field( $_POST['customnot'] ) );
        }

        if (isset($_POST['dbnot'])) {
            update_user_meta($current_user->ID, 'pwork_dbnot', sanitize_text_field( $_POST['dbnot'] ) );
        }

        if (isset($_POST['newcomment'])) {
            update_user_meta($current_user->ID, 'pwork_newcommentnot', sanitize_text_field( $_POST['newcomment'] ) );
        }

        if (isset($_POST['newreply'])) {
            update_user_meta($current_user->ID, 'pwork_newreplynot', sanitize_text_field( $_POST['newreply'] ) );
        }

        if (isset($_POST['projectsnot'])) {
            update_user_meta($current_user->ID, 'pwork_projectsnot', sanitize_text_field( $_POST['projectsnot'] ) );
        }

        if (isset($_POST['projectactivitiesnot'])) {
            update_user_meta($current_user->ID, 'pwork_projectactivitiesnot', sanitize_text_field( $_POST['projectactivitiesnot'] ) );
        }

        if (isset($_POST['eventsnot'])) {
            update_user_meta($current_user->ID, 'pwork_eventsnot', sanitize_text_field( $_POST['eventsnot'] ) );
        }

        if (isset($_POST['messagesnot'])) {
            update_user_meta($current_user->ID, 'pwork_messagesnot', sanitize_text_field( $_POST['messagesnot'] ) );
        }

        echo 'done';
        wp_die();
    }

    /* Custom Avatar */
    public function get_avatar($avatar, $id_or_email, $size, $default, $alt) {
        $email = is_object( $id_or_email ) ? $id_or_email->comment_author_email : $id_or_email;
        if( is_email( $email ) && ! email_exists( $email ) ) {
            return $avatar;
        }
        $custom_avatar = get_user_meta($id_or_email, 'pwork_user_avatar_data', true);
        if ($custom_avatar) {
            $return = '<img class="avatar" src="' . $custom_avatar . '" width="' . $size . '" height="' . $size . '" alt="' . $alt . '" >';
        } else if ($avatar) {
            $return = $avatar;
        } else {
            $return = '<img class="avatar" src="' . $default . '" width="' . $size . '" height="' . $size . '" alt="' . $alt . '" >';
        }
        return $return;
    }

    // Update user last login date
    public function user_last_login( $user_login, $user ) {
        update_user_meta( $user->ID, 'pwork_last_login', time());
    }

    /* Add additional user profile fields to dashboard */
    public function additional_user_fields() {
        $cmb2 = new_cmb2_box( array(
            'id' => 'pwork_additional_fields',
            'title' => esc_html__( 'Additional Fields', 'pwork'),
            'object_types' => array( 'user' ),
            'show_names' => true,
            'cmb_styles' => true
        ));

        $cmb2->add_field( array(
            'name' => esc_html__( 'Pwork Profile Settings', 'pwork'),
            'id'   => 'pwork_user_settings_title',
            'type' => 'title',
            'before_row' => '<hr style="margin-top:30px;margin-bottom:20px;">'
        ));

        $cmb2->add_field( array(
            'name' => esc_html__( 'Job Title', 'pwork'),
            'id'   => 'pwork_job',
            'type' => 'text',
            'attributes' => array(
                'autocomplete' => 'off'
            ),
        ));

        $cmb2->add_field( array(
            'name' => esc_html__( 'Phone number', 'pwork'),
            'id'   => 'pwork_tel',
            'type' => 'text',
            'attributes' => array(
                'autocomplete' => 'off'
            ),
        ));

        $cmb2->add_field( array(
            'name' => esc_html__( 'Location', 'pwork'),
            'id'   => 'pwork_location',
            'type' => 'text',
            'attributes' => array(
                'autocomplete' => 'off'
            ),
        ));

        $cmb2->add_field( array(
            'name' => esc_html__( 'Date of Birth', 'pwork'),
            'id'   => 'pwork_birth_date',
            'type' => 'text',
            'attributes' => array(
                'type' => 'date',
                'autocomplete' => 'off'
            ),
        ));
    }

    /**
	 * Get user table rows
	 */
    public static function list_users($user_query) {
        $selected_info = PworkSettings::get_option('user_info_field', 'member_since');
        $show_roles = PworkSettings::get_option('show_user_roles', 'enable');
        $contacts_module = PworkSettings::get_option('contacts_module', 'enable');
        foreach ($user_query->get_results() as $user) {
            $slug = PworkSettings::get_option('slug', 'pwork'); 
            $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $user->ID;
            $location = get_user_meta($user->ID, 'pwork_location', true);
            $phoneCheck = get_user_meta($user->ID, 'pwork_phone_check', true);
            $phone = get_user_meta($user->ID, 'pwork_tel', true);
            $emailCheck = get_user_meta($user->ID, 'pwork_email_check', true);
            $email = get_the_author_meta('user_email', $user->ID);
            $icons = get_user_meta($user->ID, 'pwork_icons', true);
            $contacts = get_user_meta(get_current_user_id(), 'pwork_contacts', true);
            if (!is_array($contacts)) {
                $contacts = array();
            }
            $contact_btn_class = 'btn-secondary add-to-contacts';
            $contact_icon = 'plus';
            $contact_title = esc_html__( 'Add to contacts', 'pwork' );
            if (in_array($user->ID, $contacts)) {
                $contact_btn_class = 'btn-danger remove-from-contacts';
                $contact_icon = 'minus';
                $contact_title = esc_html__( 'Remove from contacts', 'pwork' );
            }
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4 mb-4">
        <div class="card pwork-user-card h-100">
            <div class="card-body">
                <a href="<?php echo esc_url($user_profile_url); ?>" class="card-user-avatar"><?php echo get_avatar($user->ID, 160 ); ?></a>
                <h5><?php echo esc_html($user->display_name); ?></h5>
                <?php 
                if ($selected_info == 'member_since') {
                    echo '<div class="pwork-user-card-info"><span>' . esc_html__( 'Member since', 'pwork' ) . '</span><br>' . esc_html(date(get_option('date_format'), strtotime($user->user_registered))) . '</div>';
                } else if ($selected_info == 'user_role' && $show_roles == 'enable') {
                    echo '<div class="pwork-user-card-info">';
                    $user_roles = $user->roles;
                    foreach($user_roles as $role) {
                        $role_class = 'badge bg-secondary';
                        if ($role == 'administrator'){
                            $role_class = 'badge bg-primary'; 
                        } 
                        $role_name = $role ? wp_roles()->get_names()[ $role ] : '';
                        echo '<span class="' . $role_class . '">' . esc_html($role_name) . '</span>';
                    }
                    echo '</div>';
                } else if ($selected_info == 'location' && $location && !empty($location)) {
                    echo '<div class="pwork-user-card-info d-flex align-items-center"><i class="bx bxs-map"></i>' . esc_html($location) . '</div>';
                } else if ($selected_info == 'tel' && !empty($phoneCheck) && !empty($phone) && $phoneCheck == 'yes') {
                    echo '<div class="pwork-user-card-info d-flex align-items-center"><a href="tel:' . esc_attr($phone) . '"><i class="bx bxs-phone"></i>' . esc_html($phone) . '</a></div>';
                } else if ($selected_info == 'email' && !empty($emailCheck) && !empty($email) && $emailCheck == 'yes') {
                    echo '<div class="pwork-user-card-info d-flex align-items-center mt-3"><a href="mailto:' . esc_attr($email) . '" class="btn btn-sm btn-primary"><i class="bx bxs-envelope"></i>' . esc_html__( 'Send Email', 'pwork' ) . '</a></div>';
                } else if ($selected_info == 'social_media' && $icons && !empty($icons)) {
                    echo '<div class="pwork-user-card-info d-flex flex-wrap justify-content-center align-items-center">';
                    $social_media_list = Pwork::social_media_list();
                    $icons = json_decode($icons, true);
                    foreach($icons as $option => $value) {
                        echo '<a href="' . $value  . '" class="btn rounded-pill btn-icon btn-dark" title="' . $social_media_list[$option] . '" target="_blank"><i class="tf-icons bx bxl-' . $option  . '"></i></a>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
            <div class="btn-group" role="group">
                <?php 
                if ($user->ID != get_current_user_id()) {
                $contactCheck = get_user_meta($user->ID, 'pwork_contact_check', true);
                if ($contactCheck != 'no' && $contacts_module == 'enable') {
                ?>
                <button type="button" class="btn btn-icon rounded-0 <?php echo esc_attr($contact_btn_class); ?>" data-id="<?php echo esc_attr($user->ID); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr($contact_title); ?>"><span class="tf-icons bx bxs-user-<?php echo esc_attr($contact_icon); ?>"></span></button>
                <?php }
                } ?>
                <a href="<?php echo esc_url($user_profile_url); ?>" class="btn btn-icon btn-dark rounded-0"><span class="tf-icons bx bx-link"></span></a>
            </div>
        </div>
        </div>
        <?php }
    }

    /**
	 * Search Users
	 */
    public function search_users() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $roles = PworkSettings::get_option('excluded_roles', array());
        $user_limit = (int) PworkSettings::get_option('user_limit', 12);
        $sortby = PworkSettings::get_option('sort_users_by', 'asc');
        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $user_args = array(
                'search'         => '*' . sanitize_text_field($_POST['search']) . '*',
                'search_columns' => array(
                    'user_login',
                    'user_nicename',
                    'user_email',
                    'display_name'
                ),
                'role__not_in' => $roles,
                'number'	 => $user_limit,
                'offset'     => 0
            );  
        } else {
            $user_args = array(
                'number'	 => $user_limit,
                'role__not_in' => $roles,
                'offset'     => 0
            );
        }
        if ($sortby == 'asc') {
            $user_args + array(
                'orderby'	 => 'title',
                'order'		 => 'ASC',
            );
        } else if ($sortby == 'desc') {
            $user_args + array(
                'orderby'	 => 'title',
                'order'		 => 'DESC',
            );
        } else if ($sortby == 'newest') {
            $user_args + array(
                'orderby'	 => 'user_registered',
                'order'		 => 'DESC',
            );
        } else if ($sortby == 'oldest') {
            $user_args + array(
                'orderby'	 => 'user_registered',
                'order'		 => 'ASC',
            );
        } 
        $user_query = new WP_User_Query($user_args);
        if ($user_query->get_results()) {
            $total_query = $user_query->get_total();
            self::list_users($user_query);
           if ($total_query > $user_limit) { ?>
            <div class="col-12 mt-2">
                <button id="pwork-load-more-users" type="button" class="btn btn-lg btn-primary w-100" data-page="1"><?php echo esc_html__( 'LOAD MORE', 'pwork' ); ?></button>
            </div>
            <?php } ?>
        <?php } else {
            echo '<div class="alert alert-warning">' . esc_html__( 'No users found.', 'pwork' ) . '</div>';
        }
        wp_die();
    }
    
    /**
	 * Load More Users
	 */
    public function load_more_users() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $user_limit = (int) PworkSettings::get_option('user_limit', 12);
        $sortby = PworkSettings::get_option('sort_users_by', 'asc');
        $offset = (int) $_POST['page'] * $user_limit;
        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $user_args = array(
                'search'         => '*' . sanitize_text_field($_POST['search']) . '*',
                'search_columns' => array(
                    'user_login',
                    'user_nicename',
                    'user_email',
                    'display_name'
                ),
                'number'	 => $user_limit,
                'offset'     => $offset,
            );  
        } else {
            $user_args = array(
                'number'	 => $user_limit,
                'offset'     => $offset
            );
        }
        if ($sortby == 'asc') {
            $user_args + array(
                'orderby'	 => 'title',
                'order'		 => 'ASC',
            );
        } else if ($sortby == 'desc') {
            $user_args + array(
                'orderby'	 => 'title',
                'order'		 => 'DESC',
            );
        } else if ($sortby == 'newest') {
            $user_args + array(
                'orderby'	 => 'user_registered',
                'order'		 => 'DESC',
            );
        } else if ($sortby == 'oldest') {
            $user_args + array(
                'orderby'	 => 'user_registered',
                'order'		 => 'ASC',
            );
        }
        $user_query = new WP_User_Query($user_args);
        if ($user_query->get_results()) {
            $total_query = $user_query->get_total();
            self::list_users($user_query);
            if ($total_query > $user_limit) { ?>
            <div class="col-12 mt-2">
                <button id="pwork-load-more-users" type="button" class="btn btn-lg btn-primary w-100" data-page="<?php echo esc_attr((int)$_POST['page'] + 1); ?>"><?php echo esc_html__( 'LOAD MORE', 'pwork' ); ?></button>
            </div>
            <?php } ?>
        <?php }
        wp_die();
    }

    /**
	 * Add Contact
	 */
    public function add_contact() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $current_user_id = get_current_user_id();
            $user_id = (int) $_POST['id'];
            $contacts = get_user_meta($current_user_id, 'pwork_contacts', true);
            if (!is_array($contacts)) {
                $contacts = array();
            }
            array_push($contacts, $user_id);
            update_user_meta($current_user_id, 'pwork_contacts', $contacts);

            $user_contacts = get_user_meta($user_id, 'pwork_contacts', true);
            if (!is_array($user_contacts)) {
                $user_contacts = array();
            }
            array_push($user_contacts, $current_user_id);
            update_user_meta($user_id, 'pwork_contacts', $user_contacts);
        }
        wp_die();
    }

    /**
	 * Remove Contact
	 */
    public function remove_contact() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $current_user_id = get_current_user_id();
            $user_id = (int) $_POST['id'];
            $contacts = get_user_meta($current_user_id, 'pwork_contacts', true);
            if (!is_array($contacts)) {
                $contacts = array();
            }
            if (($key = array_search($user_id, $contacts)) !== false) {
                unset($contacts[$key]);
            }
            update_user_meta($current_user_id, 'pwork_contacts', $contacts);

            $user_contacts = get_user_meta($user_id, 'pwork_contacts', true);
            if (!is_array($user_contacts)) {
                $user_contacts = array();
            }
            if (($key = array_search($current_user_id, $user_contacts)) !== false) {
                unset($user_contacts[$key]);
            }
            update_user_meta($user_id, 'pwork_contacts', $user_contacts);
            PworkMessages::delete_messages_between_users($current_user_id, $user_id);
        }
        wp_die();
    }

    /**
	 * Human readable filesize
	 */
    public static function human_filesize($size, $precision = 2) {
        static $units = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
        $step = 1024;
        $i = 0;
        while (($size / $step) > 0.9) {
            $size = $size / $step;
            $i++;
        }
        echo round($size, $precision) . $units[$i];
    }

    /**
	 * Add notification
	 */
    public static function add_notification($type, $userid) {
        if (!$userid || empty($userid)) {
            $userid = $user->ID;
        }
        $blocked_roles = PworkSettings::get_option('blocked_roles', array());
        $users = get_users(array(
            'role__not_in' => $blocked_roles
        ));
        foreach ( $users as $user ) {
            if ($type == 'new_announcement') {
                $new_ann = get_user_meta($userid, 'pwork_ntf_new_announcement', true);
                if ($new_ann && !empty($new_ann)) {
                    $new_ann = (int) $new_ann + 1;
                    update_user_meta($userid, 'pwork_ntf_new_announcement', $new_ann);
                } else {
                    update_user_meta($userid, 'pwork_ntf_new_announcement', 1);
                }
            } else if ($type == 'new_event') {
                $new_event = get_user_meta($userid, 'pwork_ntf_new_event', true);
                if ($new_event && !empty($new_event)) {
                    $new_event = (int) $new_event + 1;
                    update_user_meta($userid, 'pwork_ntf_new_event' , $new_event);
                } else {
                    update_user_meta($userid, 'pwork_ntf_new_event' , 1);
                }
            } else if ($type == 'new_topic') {
                $new_topic = get_user_meta($userid, 'pwork_ntf_new_topic', true);
                if ($new_topic && !empty($new_topic)) {
                    $new_topic = (int) $new_topic + 1;
                    update_user_meta($userid, 'pwork_ntf_new_topic' , $new_topic);
                } else {
                    update_user_meta($userid, 'pwork_ntf_new_topic' , 1);
                }
            } else if ($type == 'new_message') {
                $new_message = get_user_meta($userid, 'pwork_ntf_new_message', true);
                if ($new_message && !empty($new_message)) {
                    $new_message = (int) $new_message + 1;
                    update_user_meta($userid, 'pwork_ntf_new_message' , $new_message);
                } else {
                    update_user_meta($userid, 'pwork_ntf_new_message' , 1);
                }
            } else if ($type == 'new_project') {
                $new_project = get_user_meta($userid, 'pwork_ntf_new_project', true);
                if ($new_project && !empty($new_project)) {
                    $new_project = (int) $new_project + 1;
                    update_user_meta($userid, 'pwork_ntf_new_project' , $new_project);
                } else {
                    update_user_meta($userid, 'pwork_ntf_new_project' , 1);
                }
            }
        }
    }

    /**
	 * Get notifications
	 */
    public function get_notifications() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $user_id = get_current_user_id();
        $new_ann = get_user_meta($user_id , 'pwork_ntf_new_announcement', true);
        $new_event = get_user_meta($user_id , 'pwork_ntf_new_event', true);
        $new_topic = get_user_meta($user_id , 'pwork_ntf_new_topic', true);
        $new_message = get_user_meta($user_id , 'pwork_ntf_new_message', true);
        $new_project = get_user_meta($user_id , 'pwork_ntf_new_project', true);
        $response = '{"newAnn": "' . esc_js($new_ann) . '","newEvent": "' . esc_js($new_event) . '","newTopic": "' . esc_js($new_topic) . '","newMessage": "' . esc_js($new_message) . '","newProject": "' . esc_js($new_project) . '"}';
        echo $response;
        wp_die();
    }

    /**
	 * Remove notification
	 */
    public static function remove_notification($type) {
        $user_id = get_current_user_id();
        if ($type == 'announcements') {
            $anns = get_user_meta($user_id, 'pwork_ntf_new_announcement', true);
            if ($anns && !empty($anns) && $anns !== 0) {
                update_user_meta($user_id, 'pwork_ntf_new_announcement', 0);
            }
        } else if ($type == 'events') {
            $events = get_user_meta($user_id, 'pwork_ntf_new_event', true);
            if ($events && !empty($events) && $events !== 0) {
                update_user_meta($user_id, 'pwork_ntf_new_event', 0);
            }
        } else if ($type == 'topics') {
            $topics = get_user_meta($user_id, 'pwork_ntf_new_topic', true);
            if ($topics && !empty($topics) && $topics !== 0) {
                update_user_meta($user_id, 'pwork_ntf_new_topic', 0);
            }
        } else if ($type == 'messages') {
            $messages = get_user_meta($user_id, 'pwork_ntf_new_message', true);
            if ($messages && !empty($messages) && $messages !== 0) {
                update_user_meta($user_id, 'pwork_ntf_new_message', 0);
            }
        } else if ($type == 'projects') {
            $projects = get_user_meta($user_id, 'pwork_ntf_new_project', true);
            if ($projects && !empty($projects) && $projects !== 0) {
                update_user_meta($user_id, 'pwork_ntf_new_project', 0);
            }
        }
    }

    /**
	 * Send notification e-mails
	 */
    public static function send_email($type, $subject, $message, $single) {
        $email_module = PworkSettings::get_option('email_module', 'enable'); 
        $blocked_roles = PworkSettings::get_option('blocked_roles', array());
        if ($email_module == 'enable') {
            $users_array = array();
            $users = array();
            $headers = array('Content-Type: text/html; charset=UTF-8');
            if ($single && !empty($single)) {
                if ($type == 'new_comment') {
                    $newcomment = get_user_meta((int) $single, 'pwork_newcommentnot', true);
                    if ($newcomment && $newcomment == 'yes') {
                        $users = get_user_by('id', (int) $single);
                        $users = array($users);
                    }
                } else if ($type == 'new_reply') {
                    $newreply = get_user_meta((int) $single, 'pwork_newreplynot', true);
                    if ($newreply && $newreply == 'yes') {
                        $users = get_user_by('id', (int) $single);
                        $users = array($users);
                    }
                } else if ($type == 'new_message') {
                    $newmsg = get_user_meta((int) $single, 'pwork_messagesnot', true);
                    if ($newmsg && $newmsg == 'yes') {
                        $users = get_user_by('id', (int) $single);
                        $users = array($users);
                    }
                } else if ($type == 'new_project_activity') {
                    $users = get_users(array(
                        'include' => $single,
                        'meta_key' => 'pwork_projectactivitiesnot',
                        'meta_value' => 'yes'
                    ));
                }
            } else {
                if ($type == 'new_event') {
                    $users = get_users(array(
                        'meta_key' => 'pwork_eventsnot',
                        'meta_value' => 'yes'
                    ));
                } else if ($type == 'new_announcement') {
                    $users = get_users(array(
                        'meta_key' => 'pwork_customnot',
                        'meta_value' => 'yes'
                    ));
                } else if ($type == 'new_topic') {
                    $users = get_users(array(
                        'meta_key' => 'pwork_dbnot',
                        'meta_value' => 'yes'
                    ));
                } else if ($type == 'new_project') {
                    $users = get_users(array(
                        'meta_key' => 'pwork_dbnot',
                        'meta_value' => 'yes'
                    ));
                }
            }
            
            if ($users && !empty($users)) {
                foreach ( $users as $user ) {
                    if (array_intersect( $blocked_roles, $user->roles )) { 
                        return;
                    } else {
                        array_push($users_array, $user->user_email);
                    }
                }
                if (!empty($users_array)) {
                    wp_mail($users_array, $subject, $message, $headers);
                }
            }    
        } else {
            return;
        }
    }

    /**
	 * Get valid widgets
	 */
    public static function get_valid_widgets(){
        $events = PworkSettings::get_option('events_module', 'enable');
        $files = PworkSettings::get_option('files_module', 'enable');
        $forum = PworkSettings::get_option('forum_module', 'enable');
        $anns = PworkSettings::get_option('announcements_module', 'enable');
        $projects = PworkSettings::get_option('projects_module', 'enable');
        $contacts = PworkSettings::get_option('contacts_module', 'enable');

        $widgets = apply_filters('pworkWidgets',array(
            "site-statistics" => array(esc_html__( "Site Statistics", 'pwork' ), "dashboard-widgets/site-statistics.php"),
            "my-statistics" => array(esc_html__( "My Statistics", 'pwork' ), "dashboard-widgets/my-statistics.php"),
            "event-calendar" => array(esc_html__( "This Month's Events", 'pwork' ), "dashboard-widgets/event-calendar.php"),
            "new-anns" => array(esc_html__( 'New Announcements', 'pwork' ), "dashboard-widgets/new-anns.php"),
            "recent-topics" => array(esc_html__( 'Recent Topics', 'pwork' ), "dashboard-widgets/recent-topics.php"),
            "my-projects" => array(esc_html__( 'My Projects', 'pwork' ), "dashboard-widgets/my-projects.php"),
            "my-contacts" => array(esc_html__( 'My Contacts', 'pwork' ), "dashboard-widgets/my-contacts.php"),
            "latest-uploads" => array(esc_html__( 'Recently Shared Files', 'pwork' ), "dashboard-widgets/latest-uploads.php"),
        ));

        if ($events != 'enable') {
            unset($widgets['event-calendar']);
        }
        if ($files != 'enable') {
            unset($widgets['latest-uploads']);
        }
        if ($forum != 'enable') {
            unset($widgets['recent-topics']);
        }
        if ($anns != 'enable') {
            unset($widgets['new-anns']);
        }
        if ($projects != 'enable') {
            unset($widgets['my-projects']);
        }
        if ($contacts != 'enable') {
            unset($widgets['my-contacts']);
        }

        return $widgets;
    }

    /**
	 * Count contacts
	 */
    public static function count_contacts($userID){
        $contacts = get_user_meta(get_current_user_id(), 'pwork_contacts', true);
        if ($contacts && !empty($contacts)) {
            return count($contacts);
        } else {
            return '0';
        }
    }

    /**
	 * Count users
	 */
    public static function count_users(){
        $blocked_roles =  PworkSettings::get_option('blocked_roles', array());
        $users = get_users(array(
            'role__not_in' => $blocked_roles
        ));
        return count($users);
    }
}

/**
 * Returns the main instance of the class
 */
function Pwork() {  
	return Pwork::instance();
}
// Global for backwards compatibility
$GLOBALS['Pwork'] = Pwork();