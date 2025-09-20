<?php include_once('header.php'); ?>
<div id="pwork-users-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-lg flex-grow-1 container-p-y">
                <div class="pwork-page-header d-flex justify-content-between align-items-center mb-4">
                    <h2 class="col fw-bold mb-4">
                        <?php echo esc_html__('User Directory', 'pwork'); ?>
                    </h2>
                    <div class="col input-group input-group-merge mb-4">
                        <input id="pwork-search-input" type="text" class="form-control form-control-lg" placeholder="<?php echo esc_attr__( 'Search by name...', 'pwork' ); ?>" autocomplete="off">
                        <div id="pwork-search-input-clear" class="input-group-text d-none" title="<?php echo esc_attr__( 'Clear', 'pwork' ); ?>">
                            <i class="bx bx-x cursor-pointer text-danger"></i>
                        </div>
                        <button id="pwork-search-user" type="button" class="btn btn-lg btn-primary"><span class="tf-icons bx bx-search"></span></button>
                    </div>
                </div>
                <?php
                $roles = PworkSettings::get_option('excluded_roles', array());
                $user_limit = PworkSettings::get_option('user_limit', 12);
                $sortby = PworkSettings::get_option('sort_users_by', 'asc');
                $users = get_users(array(
                    'role__not_in' => $roles
                ));
                $total_users = count($users);

                if ($sortby == 'asc') {
                    $user_args = array(
                        'orderby'	 => 'title',
                        'order'		 => 'ASC',
                        'role__not_in' => $roles,
                        'number'	 => $user_limit
                    );
                } else if ($sortby == 'desc') {
                    $user_args = array(
                        'orderby'	 => 'title',
                        'order'		 => 'DESC',
                        'role__not_in' => $roles,
                        'number'	 => $user_limit
                    );
                } else if ($sortby == 'newest') {
                    $user_args = array(
                        'orderby'	 => 'user_registered',
                        'order'		 => 'DESC',
                        'role__not_in' => $roles,
                        'number'	 => $user_limit
                    );
                } else if ($sortby == 'oldest') {
                    $user_args = array(
                        'orderby'	 => 'user_registered',
                        'order'		 => 'ASC',
                        'role__not_in' => $roles,
                        'number'	 => $user_limit
                    );
                }
                
                $user_query = new WP_User_Query($user_args);
                if ($user_query->get_results()) {
                ?>
                <div id="pwork-users-grid" class="row">
                    <?php
                    $total_query = count($user_query->get_results());
                    $selected_info = PworkSettings::get_option('user_info_field', 'member_since');
                    $show_roles = PworkSettings::get_option('show_user_roles', 'enable');
                    $contacts_module = PworkSettings::get_option('contacts_module', 'enable');

                    foreach ($user_query->get_results() as $user) {
                        $slug = PworkSettings::get_option('slug', 'pwork'); 
                        $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $user->ID;
                        $location = get_user_meta($user->ID, 'pwork_location', true);
                        $job = get_user_meta($user->ID, 'pwork_job', true);
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
                                    } else if ($role == 'editor') {
                                        $role_class = 'badge bg-dark'; 
                                    }
                                    $role_name = $role ? wp_roles()->get_names()[ $role ] : '';
                                    echo '<span class="' . $role_class . '">' . esc_html($role_name) . '</span>';
                                }
                                echo '</div>';
                            } else if ($selected_info == 'job' && $job && !empty($job)) {
                                echo '<div class="pwork-user-card-info d-flex align-items-center">' . esc_html($job) . '</div>';
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
                            <a href="<?php echo esc_url($user_profile_url); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__( 'View profile', 'pwork' ); ?>" class="btn btn-icon btn-dark rounded-0"><span class="tf-icons bx bx-link"></span></a>
                        </div>
                    </div>
                    </div>
                    <?php } ?>
                    <?php if ($total_users > $total_query) { ?>
                    <div class="col-12 mt-2">
                        <button id="pwork-load-more-users" type="button" class="btn btn-lg btn-primary w-100" data-page="1"><?php echo esc_html__( 'LOAD MORE', 'pwork' ); ?></button>
                    </div>
                    <?php } ?>
                </div>
                <?php } else { ?>
                    <div class="alert alert-warning"><?php echo esc_html__( 'No users found.', 'pwork' ); ?></div>
                <?php } ?>
            </div>
            <div class="content-backdrop fade"></div>
        </div>
    </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php include_once('footer.php'); ?>