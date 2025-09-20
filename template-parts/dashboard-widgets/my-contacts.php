<div id="my-contacts-widget" class="pwork-widget col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3">
    <div class="card post-card">
        <div class="card-header pwork-widget-header bg-dark">
            <h6 class="d-flex align-items-center text-uppercase m-0 text-white"><?php echo esc_html__( 'My Contacts', 'pwork' ); ?><i class="bx bx-move ms-auto text-white grabbing"></i></h6>
        </div>
        <?php 
        $user_args = array();
        $contacts = get_user_meta(get_current_user_id(), 'pwork_contacts', true);
        $contacts_url = get_site_url() . '/' . $slug . '/?page=contacts';
        if (!is_array($contacts)) {
            $contacts = array();
        }
        $user_args = array(
            'orderby'	 => 'title',
            'order'		 => 'ASC',
            'include'    => $contacts,
            'number'	 => 5
        );
        
        if (!empty($contacts)) {
            $user_query = new WP_User_Query($user_args);
            if ($user_query->get_results()) { ?>
            <div class="list-group list-group-flush">
                <?php 
                foreach ($user_query->get_results() as $user) {
                    $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $user->ID;
                    $send_message_url = get_site_url() . '/' . $slug . '/?page=messages&userID=' . $user->ID;
                    ?>
                    <div class="list-group-item">
                        <a href="<?php echo esc_url($user_profile_url); ?>" class="d-flex align-items-center">
                            <?php echo get_avatar($user->ID, 64 ); ?>
                            <?php echo esc_html($user->display_name); ?>
                        </a>
                        <div class="btn-group ms-auto" role="group"> 
                            <a href="<?php echo esc_url($send_message_url); ?>" class="btn btn-sm btn-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Send message', 'pwork'); ?>"><span class="tf-icons bx bx-envelope"></span></a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php
        echo '<a href="' . esc_url($contacts_url) . '" class="btn btn-secondary w-100 widget-btn">' . esc_html__( 'View All', 'pwork' ) . '</a>';
        } else {
            echo '<div class="card-body mt-4"><div class="alert alert-info m-0">' . esc_html__( 'You have no contacts.', 'pwork' ) . '</div></div>';
        }
    } else {
        echo '<div class="card-body mt-4"><div class="alert alert-info m-0">' . esc_html__( 'You have no contacts.', 'pwork' ) . '</div></div>';
    } ?>
    </div>
</div>