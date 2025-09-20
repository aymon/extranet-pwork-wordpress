<?php include_once('header.php'); ?>
<?php
Pwork::remove_notification('messages'); 
$currentUserID = get_current_user_id();
$slug = PworkSettings::get_option('slug', 'pwork'); 
?>
<div id="pwork-messages-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-xl flex-grow-1 p-0 m-0">
                <div class="pwork-messages-wrap">
                    <div class="pwork-messages-sidebar">
                        <div id="pwork-messages-search" class="input-group input-group-merge"> 
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input id="pwork-messages-search-input" type="text" class="form-control" placeholder="<?php echo esc_attr__('Search by name...', 'pwork'); ?>" autocomplete="off">
                        </div>
                        <div class="pwork-messages-sidebar-chats">
                        <?php
                        $contacts = get_user_meta($currentUserID, 'pwork_contacts', true);
                        if (!is_array($contacts)) {
                            $contacts = array();
                        }
                        $user_args = array(
                            'orderby'	 => 'title',
                            'order'		 => 'ASC',
                            'include'    => $contacts,
                            'number'	 => 999
                        );
                        if (!empty($contacts)) {
                            $messages_array = array();
                            $timestamps = array();
                            $user_query = new WP_User_Query($user_args);
                            if ($user_query->get_results()) {
                                foreach ($user_query->get_results() as $user) {
                                    $display_name = $user->display_name;
                                    $chat = PworkMessages::get_chat($currentUserID, $user->ID, 'DESC', 1, 0);
                                    $excerpt = '...';
                                    $last_activity = strtotime("01 January 2000");
                                    if ($chat) {
                                        $excerpt = $chat[0]->content;
                                        $last_activity = strtotime($chat[0]->sent_at);
                                    }
                                    array_push($messages_array, array($user->ID, $display_name, $excerpt, $last_activity));
                                }

                                foreach ($messages_array as $key => $node) {
                                    $timestamps[$key]  = $node[3];
                                }
                                array_multisort($timestamps, SORT_DESC, $messages_array);
                                $active = 'active';
                                $selected = 'true';

                                foreach ($messages_array as $key => $item) { ?>
                                    <div class="pwork-messages-sidebar-chat <?php echo esc_attr($active); ?>" data-name="<?php echo esc_attr(strtolower($item[1])); ?>" data-contact="<?php echo esc_attr($item[0]); ?>" data-target="#pwork-messages-user-<?php echo esc_attr($item[0]); ?>">
                                        <div class="pwork-messages-sidebar-chat-left">
                                        <?php echo get_avatar($item[0], 80 ); ?>
                                        </div>
                                        <div class="pwork-messages-sidebar-chat-content">
                                            <h5 class="m-0"><?php echo esc_html($item[1]); ?></h5>
                                            <p class="mb-0 mt-1 text-truncate"><?php echo wp_strip_all_tags($item[2], true); ?></p>
                                        </div>
                                    </div>
                                    <?php
                                    $active = '';
                                    $selected = 'false';
                                }
                            } else {
                                echo '<div class="alert alert-primary">' . esc_html__('You have no connections. You can only send messages to your contacts.', 'pwork') . '</div>';
                            }
                        } 
                        ?>
                        </div>
                    </div>
                    <div class="pwork-single-message">
                    <?php 
                    $active = 'd-flex';
                    foreach ($messages_array as $key => $item) { ?>
                        <div id="pwork-messages-user-<?php echo esc_attr($item[0]); ?>" class="pwork-single-message-wrap <?php echo esc_attr($active); ?>">
                            <div class="pwork-single-message-header">
                                <h4 class="m-0"><?php echo esc_html($item[1]); ?></h4>
                            </div>
                            <div class="pwork-single-message-content">
                            <?php
                            $chat = PworkMessages::get_chat($currentUserID, $item[0], 'DESC', 20, 0);
                            if (!empty($chat) && is_array($chat)) {
                                $chat = array_reverse($chat);
                                $number_of_msgs = count($chat);
                                if ($number_of_msgs == 20) {
                                    echo '<div class="pwork-load-more-msgs"><button type="button" class="btn btn-sm btn-secondary load-more-msgs" data-offset="5" data-userid="' . $item[0] . '">' . esc_html__( 'Load Old Messages', 'pwork' ) . '</button></div>';
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
                            } else {
                                echo '<div class="alert alert-secondary">' . esc_html__('No messages yet.', 'pwork') . '</div>';
                            }
                            ?>
                            </div>
                            <div class="pwork-single-message-input">
                                <div class="pwork-single-message-textarea"></div>
                                <button type="button" class="btn btn-primary btn-icon pwork-send-message ms-3" data-sender="<?php echo esc_attr($currentUserID); ?>" data-contact="<?php echo esc_attr($item[0]); ?>" title="<?php echo esc_attr__( 'Send message', 'pwork' ); ?>"><i class="bx bxs-send"></i></button>
                            </div>
                        </div>
                    <?php
                    $active = 'd-none';
                    } ?>
                    </div>
                </div>
            </div>
            <div class="content-backdrop fade"></div>
        </div>
    </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php include_once('footer.php'); ?>