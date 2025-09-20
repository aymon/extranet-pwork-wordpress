<?php include_once('header.php'); ?>
<?php 
$slug = PworkSettings::get_option('slug', 'pwork');
$messages = PworkSettings::get_option('messages_module', 'enable');
?>
<div id="pwork-contacts-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-lg flex-grow-1 container-p-y">
                <div class="card">
                    <div class="pwork-card-header card-header align-items-center">
                        <div class="pwork-card-header-title">
                            <h3 class="m-0"><?php echo esc_html__('My Contacts', 'pwork'); ?></h3>
                        </div>
                        <div id="pwork-search-contact-input" class="input-group input-group-merge"> 
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" placeholder="<?php echo esc_html__('Search by name...', 'pwork'); ?>" autocomplete="off">
                        </div>
                    </div>
                        <?php
                        $contacts = get_user_meta(get_current_user_id(), 'pwork_contacts', true);
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
                            $user_query = new WP_User_Query($user_args);
                            if ($user_query->get_results()) { ?>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th class="w-100"><?php echo esc_html__('Name', 'pwork'); ?></th>
                                            <th class="d-none d-md-table-cell"><?php echo esc_html__('Phone', 'pwork'); ?></th>
                                            <th class="d-none d-md-table-cell"><?php echo esc_html__('Email', 'pwork'); ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="pwork-contacts-table" class="table-border-bottom-0">
                                    <?php 
                                    foreach ($user_query->get_results() as $user) {
                                        $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $user->ID;
                                        $send_message_url = get_site_url() . '/' . $slug . '/?page=messages&userID=' . $user->ID;
                                        $phoneCheck = get_user_meta($user->ID, 'pwork_phone_check', true);
                                        $phone = get_user_meta($user->ID, 'pwork_tel', true);
                                        $emailCheck = get_user_meta($user->ID, 'pwork_phone_check', true);
                                        $email = get_the_author_meta('user_email', $user->ID);
                                        ?>
                                        <tr data-name="<?php echo esc_attr(strtolower($user->display_name)); ?>">
                                            <td class="w-100">
                                                <a href="<?php echo esc_url($user_profile_url); ?>" class="d-flex align-items-center"><?php echo get_avatar($user->ID, 80); ?><span class="ms-2"><?php echo esc_html($user->display_name); ?></span></a>
                                                <div class="d-block d-md-none">
                                                    <small class="d-block mt-2 mb-1"><?php echo esc_html__('Phone:', 'pwork'); ?> 
                                                    <?php 
                                                    if (!empty($phoneCheck) && !empty($phone) && $phoneCheck == 'yes') {
                                                        echo '<a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a>';
                                                    } else {
                                                        echo '<i class="menu-icon tf-icons bx bx-minus"></i>';
                                                    } ?>
                                                    </small>
                                                    <small class="d-block"><?php echo esc_html__('Email:', 'pwork'); ?>
                                                        <?php
                                                        if (!empty($emailCheck) && !empty($email) && $emailCheck == 'yes') {
                                                            echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
                                                        } else {
                                                            echo '<i class="menu-icon tf-icons bx bx-minus"></i>';
                                                        } ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                            <?php 
                                            if (!empty($phoneCheck) && !empty($phone) && $phoneCheck == 'yes') {
                                                echo '<a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a>';
                                            } else {
                                                echo '<i class="menu-icon tf-icons bx bx-minus"></i>';
                                            } ?>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                            <?php
                                            if (!empty($emailCheck) && !empty($email) && $emailCheck == 'yes') {
                                                echo '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
                                            } else {
                                                echo '<i class="menu-icon tf-icons bx bx-minus"></i>';
                                            } ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group" role="group">
                                                    <?php if ($messages == 'enable') { ?>
                                                    <a href="<?php echo esc_url($send_message_url); ?>" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Send message', 'pwork'); ?>"><span class="tf-icons bx bx-envelope"></span></a>
                                                    <?php } ?>
                                                    <a href="<?php echo esc_url($user_profile_url); ?>" class="btn btn-sm btn-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('View profile', 'pwork'); ?>"><span class="tf-icons bx bx-link"></span></a>
                                                    <button type="button" class="btn btn-sm btn-danger remove-from-table-contacts" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Remove from contacts', 'pwork'); ?>" data-id="<?php echo esc_attr($user->ID); ?>"><span class="tf-icons bx bxs-trash"></span></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } else {
                            echo '<div class="card-body mt-4"><div class="alert alert-warning m-0">' . esc_html__( 'You have no contacts.', 'pwork' ) . '</div></div>';
                        }
                    } else {
                        echo '<div class="card-body mt-4"><div class="alert alert-warning m-0">' . esc_html__( 'You have no contacts.', 'pwork' ) . '</div></div>';
                    } ?>
                </div>
            </div>
            <div class="content-backdrop fade"></div>
        </div>
    </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php include_once('footer.php'); ?>