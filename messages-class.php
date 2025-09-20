<?php
defined( 'ABSPATH' ) || exit;

class PworkMessages {
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
        $delete_threshold_days = PworkSettings::get_option('delete_old_messages', 365);
        if ($delete_threshold_days !== 0) {
            add_action('weekly', array($this, 'delete_old_messages'));
        }
        add_action('wp_loaded', array($this, 'create_table'));
        add_action('wp_ajax_pworkSendMessage', array($this, 'send_message'));
        add_action('wp_ajax_pworkLoadOldMsg', array($this, 'load_old_messages'));
        add_action('wp_ajax_pworkMarkAsRead', array($this, 'mark_message_as_read'));
    }

    /**
	 * Create Table
	 */
    public function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'pwork_message_table';
      
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          message_id int unsigned NOT NULL AUTO_INCREMENT,
          sender_id bigint unsigned NOT NULL,
          recipient_id bigint unsigned NOT NULL,
          content text NOT NULL,
          sent_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
          is_read tinyint(1) DEFAULT 0 NOT NULL,
          PRIMARY KEY (message_id),
          FOREIGN KEY (sender_id) REFERENCES {$wpdb->prefix}users(ID),
          FOREIGN KEY (recipient_id) REFERENCES {$wpdb->prefix}users(ID)
        ) $charset_collate;";
      
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
      
    /**
	 * Send Message
	 */
    public function send_message() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        if (empty($_POST['content'])) {
            echo esc_html__('The message field cannot be left blank.', 'pwork');
            exit();
        }
        global $wpdb;
        $slug = PworkSettings::get_option('slug', 'pwork');
        $sender = (int) $_POST['sender'];
        $contact = (int) $_POST['contact'];
        $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $sender;
        $data = array(
          'sender_id' => $sender,
          'recipient_id' => $contact,
          'content' => wp_kses_post($_POST['content']),
          'sent_at' => current_time('mysql'),
          'is_read' => 0,
        );
        $insert = $wpdb->insert($wpdb->prefix . 'pwork_message_table', $data);
        if ($insert) {
            echo '<div class="pwork-message-bubble-wrap me"><div class="pwork-message-bubble shadow">' . wp_kses_post($_POST['content']) . '<div class="pwork-message-bubble-info">' . esc_html__( 'now', 'pwork' ) . '<i class="bx bx-check-double ms-1 text-mute"></i></div></div><a href="' . esc_url($user_profile_url) . '">' . get_avatar($_POST['sender'], 80 ) . '</a></div>';
            $this->send_notification($contact);
        } else {
            echo 'error';
        }
        wp_die();
    }

    /**
	 * Get Messages
	 */
    public static function get_messages($user_id) {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}pwork_message_table 
                WHERE sender_id = %d OR recipient_id = %d 
                ORDER BY sent_at DESC";
        $prepared = $wpdb->prepare($sql, array($user_id, $user_id));
        return $wpdb->get_results($prepared);
    }

    /**
	 * Get Chat
	 */
    public static function get_chat($user_id1, $user_id2, $order, $limit = 20, $offset = 0) {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}pwork_message_table 
                WHERE (sender_id = %d AND recipient_id = %d) 
                OR (sender_id = %d AND recipient_id = %d) 
                ORDER BY sent_at {$order}
                LIMIT %d OFFSET %d";
        $prepared = $wpdb->prepare($sql, array($user_id1, $user_id2, $user_id2, $user_id1, $limit, $offset));
      
        return $wpdb->get_results($prepared);
      }
      
    /**
	 * Get Unread Message Count
	 */
    public static function get_unread_message_count($user_id) {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}pwork_message_table 
                WHERE recipient_id = %d AND is_read = 0";
        $prepared = $wpdb->prepare($sql, array($user_id));
        return $wpdb->get_var($prepared);
    }
      
    /**
	 * Mark Message As Read
	 */
    public static function mark_message_as_read() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        global $wpdb;
        $currentUserID = get_current_user_id();
        $contact = (int) $_POST['contact'];
        $data = array('is_read' => 1);
        $where = array(
            'recipient_id' => $currentUserID,
            'sender_id' => $contact
        );
        $updated_rows = $wpdb->update($wpdb->prefix . 'pwork_message_table', $data, $where);
        if ($updated_rows >= 1) {
            echo 'done';
        } else {
            echo esc_html__('There is no unread messages.', 'pwork');
        }
        wp_die();
    }

    /**
	 * Delete All Messages Between Two Users
	 */
    public static function delete_messages_between_users($user_id1, $user_id2) {
        global $wpdb;
        $sql = "DELETE FROM {$wpdb->prefix}pwork_message_table 
                WHERE (sender_id = %d AND recipient_id = %d) 
                OR (sender_id = %d AND recipient_id = %d)";
        $prepared = $wpdb->prepare($sql, array($user_id1, $user_id2, $user_id2, $user_id1));
        return $wpdb->query($prepared);
    }

    /**
	 * Delete Message From Database
	 */
    public function delete_message_from_db($message_id) {
        global $wpdb;
        $sql = "DELETE FROM {$wpdb->prefix}pwork_message_table 
                WHERE message_id = %d";
        $prepared = $wpdb->prepare($sql, array($message_id));
        return $wpdb->query($prepared);
    }
      
    /**
	 * Delete Message
	 */
    public function delete_message($message_id) {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $deleted_rows = $this->delete_message($message_id);
        if ($deleted_rows === 1) {
            echo 'done';
        } else {
            echo esc_html__('Message might not exist or deletion failed.', 'pwork');
        }
        wp_die();
    }

    /**
	 * Delete Old Messages delete_old_messages
	 */
    public function delete_old_messages() {
        global $wpdb;
        $delete_threshold_days = PworkSettings::get_option('delete_old_messages', 365);
        $delete_threshold_seconds = $delete_threshold_days * DAY_IN_SECONDS;
        $delete_before = date('Y-m-d H:i:s', strtotime('-' . $delete_threshold_seconds . ' seconds'));
        $sql = "DELETE FROM {$wpdb->prefix}pwork_message_table 
                WHERE sent_at < '%s'";
        $prepared = $wpdb->prepare($sql, array($delete_before));
        $wpdb->query($prepared);
    }

    /**
	 * Load Old Messages
	 */
    public function load_old_messages() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pwork-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'pwork'));
        }
        $currentUserID = get_current_user_id();
        $slug = PworkSettings::get_option('slug', 'pwork'); 
        $offset = (int) $_POST['offset'];
        $userid = (int) $_POST['userid'];
        $chat = PworkMessages::get_chat($currentUserID,$userid,'DESC',20,$offset);
        $newoffset = $offset + 20;
        if (!empty($chat) && is_array($chat)) {
            $chat = array_reverse($chat);
            $number_of_msgs = count($chat);
            if ($number_of_msgs == 20) {
                echo '<div class="pwork-load-more-msgs"><button type="button" class="btn btn-sm btn-secondary load-more-msgs" data-offset="' . esc_attr($offset) . '">' . esc_html__( 'Load Old Messages', 'pwork' ) . '</button></div>';
            }
            foreach ($chat as $msg) {
                $sender_id = $msg->sender_id;
                $recipient_id = $msg->recipient_id;
                $content = $msg->content;
                $sent_at = strtotime($msg->sent_at);
                $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $sender_id;
                if ($sender_id == $currentUserID) {
                    $is_read = $msg->is_read;
                    $icon_class = 'text-mute';
                    if ($is_read == '1') {
                        $icon_class = 'text-success';
                    }
                    echo '<div class="pwork-message-bubble-wrap me"><div class="pwork-message-bubble shadow">' . wp_kses_post($content) . '<div class="pwork-message-bubble-info">' . human_time_diff( $sent_at, current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'pwork' ) . '<i class="bx bx-check-double ms-1 ' . esc_attr($icon_class) . '"></i></div></div><a href="' . esc_url($user_profile_url) . '">' . get_avatar($sender_id, 80 ) . '</a></div>';
                } else {
                    echo '<div class="pwork-message-bubble-wrap"><a href="' . esc_url($user_profile_url) . '">' . get_avatar($sender_id, 80 ) . '</a><div class="pwork-message-bubble shadow">' . wp_kses_post($content) . '<div class="pwork-message-bubble-info">' . human_time_diff( $sent_at, current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'pwork' ) . '</div></div></div>';
                }
            }
        }
        wp_die();
    }

    /**
	 * Is user online
	 */
    public function is_user_online($lastLoginTime) {
        $currentTime = time();
        $timeDifference = $currentTime - $lastLoginTime;
        $tolerance = 10;
        return ($timeDifference <= 3600 + $tolerance) && ($timeDifference >= 0);
      }
      

    /**
	 * Send notification
	 */
    public function send_notification($userid) {
        $last_login = get_user_meta($user_id, 'pwork_last_login', true);
        $slug = PworkSettings::get_option('slug', 'pwork'); 
        $blog_name = esc_html(get_bloginfo( 'name' ));
        $url = get_site_url() . '/' . $slug . '/?page=messages';
        $subject = esc_html__('New message at', 'pwork') . ' ' . $blog_name;
        $message = '<p><strong>' . esc_html__('You have a new message!', 'pwork') . '</strong></p><p><a href="' . esc_url($url) . '"><strong>' . esc_html__('Click here to view your messages.', 'pwork') . '</strong></a></p>';

        Pwork::add_notification('new_message', $userid);

        if ($last_login && !empty($last_login)) {
            if ($this->is_user_online($lastLoginTime)) {
                exit();
            } else {
                Pwork::send_email('new_message', $subject, $message, $userid);
            }
        }
    }
}

/**
 * Returns the main instance of the class
 */
function PworkMessages() {  
	return PworkMessages::instance();
}
// Global for backwards compatibility
$GLOBALS['PworkMessages'] = PworkMessages();