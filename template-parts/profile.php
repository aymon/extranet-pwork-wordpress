<?php include_once('header.php'); ?>
<?php 
$slug = PworkSettings::get_option('slug', 'pwork');
$messages = PworkSettings::get_option('messages_module', 'enable');
$contacts_module = PworkSettings::get_option('contacts_module', 'enable');
?>
<div id="pwork-profile-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-lg flex-grow-1 container-p-y">
                <?php
                if(isset($_GET['userID']) && !empty($_GET['userID'])) {
                    $user_id = $_GET['userID'];
                    $user = get_user_by('ID', $user_id);
                    if ($user) { ?>
                    <div class="card">
                    <?php
                    $user = get_user_by( 'ID', $user_id );
                    $last_login = get_user_meta($user_id, 'pwork_last_login', true);
                    $show_roles = PworkSettings::get_option('show_user_roles', 'enable'); 
                    $show_reg = PworkSettings::get_option('show_user_reg', 'enable');
                    $contacts = get_user_meta(get_current_user_id(), 'pwork_contacts', true);
                    if (!is_array($contacts)) {
                        $contacts = array();
                    }
                    $contact_btn_class = 'btn-secondary add-to-contacts';
                    $contact_icon = 'plus';
                    $contact_title = esc_html__( 'Add to Contacts', 'pwork' );
                    if (in_array($user->ID, $contacts)) {
                        $contact_btn_class = 'btn-danger remove-from-contacts';
                        $contact_icon = 'minus';
                        $contact_title = esc_html__( 'Remove from Contacts', 'pwork' );
                    }
                    ?>
                    <div class="card-body pt-4 pt-md-5 pb-4 pb-md-5">
                        <div class="row">
                            <div class="col-12 col-md-4 order-2 order-md-1">
                                <div class="pwork-user-info-list">
                                    <?php 
                                    if ($show_roles == 'enable') {
                                        $user_roles = $user->roles;
                                    ?>
                                        <div>
                                            <label class="form-label"><?php echo esc_html__( 'User Role', 'pwork' ); ?></label>
                                            <strong>
                                                <?php 
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
                                                ?>
                                            </strong>
                                        </div>
                                    <?php } ?>
                                    <?php if ($show_reg == 'enable') { ?>
                                    <div>
                                        <label class="form-label"><?php echo esc_html__( 'Member since', 'pwork' ); ?></label>
                                        <strong><?php echo esc_html(date(get_option('date_format'), strtotime($user->user_registered))); ?></strong>
                                    </div>
                                    <?php } ?>
                                    <div>
                                        <label class="form-label"><?php echo esc_html__( 'Full Name', 'pwork' ); ?></label>
                                        <strong><?php the_author_meta( 'first_name', $user_id); ?> <?php the_author_meta( 'last_name', $user_id); ?></strong>
                                    </div>
                                    <?php
                                    $job = get_user_meta($user_id, 'pwork_job', true);
                                    if ($job && !empty($job)) {
                                    ?>
                                    <div>
                                        <label class="form-label"><?php echo esc_html__( 'Job Title', 'pwork' ); ?></label>
                                        <strong><?php echo esc_html($job);  ?></strong>
                                    </div>
                                    <?php } ?>
                                    <?php
                                    $location = get_user_meta($user_id, 'pwork_location', true);
                                    if ($location && !empty($location)) {
                                    ?>
                                    <div>
                                        <label class="form-label"><?php echo esc_html__( 'Location', 'pwork' ); ?></label>
                                        <strong><?php echo esc_html($location); ?></strong>
                                    </div>
                                    <?php } ?>
                                    <?php
                                    $birth_date = get_user_meta($user_id, 'pwork_birth_date', true);
                                    if ($birth_date && !empty($birth_date)) {
                                    ?>
                                    <div>
                                        <label class="form-label"><?php echo esc_html__( 'Date of Birth', 'pwork' ); ?></label>
                                        <?php $formattedDate = date(get_option('date_format'), strtotime($birth_date)); ?>
                                        <strong><?php echo esc_html($formattedDate); ?></strong>
                                    </div>
                                    <?php } ?>
                                    <?php 
                                    $emailCheck = get_user_meta($user_id, 'pwork_email_check', true);
                                    $email = get_the_author_meta('user_email', $user_id);
                                    if (!empty($emailCheck) && !empty($email) && $emailCheck == 'yes') { 
                                    ?>
                                    <div>
                                        <label class="form-label"><?php echo esc_html__( 'Email address', 'pwork' ); ?></label>
                                        <strong><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></strong>
                                    </div>
                                    <?php } ?>
                                    <?php 
                                    $phoneCheck = get_user_meta($user_id, 'pwork_phone_check', true);
                                    $phone = get_user_meta($user_id, 'pwork_tel', true);
                                    if (!empty($phoneCheck) && !empty($phone) && $phoneCheck == 'yes') { 
                                    ?>
                                    <div>
                                        <label class="form-label"><?php echo esc_html__( 'Phone number', 'pwork' ); ?></label>
                                        <strong><a href="tel:<?php echo esc_attr($phone); ?>"><?php echo esc_html($phone); ?></a></strong>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-12 col-md-8 order-1 order-md-2">
                                <div class="pwork-user-card-top">
                                    <div class="big-avatar shadow"><?php echo get_avatar( $user_id, 300 ); ?></div>
                                    <div>
                                        <h2>
                                            <?php the_author_meta( 'display_name', $user_id); ?>
                                            <?php 
                                            if ($last_login && !empty($last_login)) { 
                                                echo '<span class="d-block"><i class="bx bx-log-in-circle align-middle"></i>' . esc_html__( 'Last logged in', 'pwork' ) . ' ' . human_time_diff( $last_login, current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'pwork' ) . '.</span>';
                                            }
                                            ?>
                                        </h2>
                                        <?php if ($user_id != get_current_user_id()) { ?>
                                        <div class="pwork-user-card-top-btns">
                                            <?php 
                                            $send_message_url = get_site_url() . '/' . $slug . '/?page=messages&userID=' . $user_id;
                                            $contactCheck = get_user_meta($user_id, 'pwork_contact_check', true);
                                            if ($contactCheck != 'no') {
                                            ?>
                                            <?php if ($contacts_module == 'enable') { ?>
                                            <button type="button" class="btn btn-icon <?php echo esc_attr($contact_btn_class); ?>" data-id="<?php echo esc_attr($user_id); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_html($contact_title); ?>"><span class="tf-icons bx bxs-user-<?php echo esc_attr($contact_icon); ?>"></span></button>
                                            <?php } ?>
                                            <?php if ($messages == 'enable' && $contact_icon == 'minus') { ?>
                                            <a href="<?php echo esc_url($send_message_url); ?>" class="btn btn-dark"><span class="me-1 bx bx-envelope"></span><?php echo esc_html(esc_html__( 'Send Message', 'pwork' )); ?></a>
                                            <?php } ?>
                                            <?php } ?>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php if (!empty(get_the_author_meta( 'description', $user_id))) { ?>
                                <div class="pwork-user-card-bottom">
                                    <?php the_author_meta( 'description', $user_id); ?>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    $icons = get_user_meta($user_id, 'pwork_icons', true);
                    if ($icons && !empty($icons)) {
                    ?>
                    <div class="pwork-card-footer">
                    <?php 
                    $icons = get_user_meta($user_id, 'pwork_icons', true);
                    $social_media_list = Pwork::social_media_list();
                    if ($icons && !empty($icons)) {
                        $icons = json_decode($icons, true);
                        foreach($icons as $option => $value) {
                            echo '<a href="' . $value  . '" class="btn rounded-pill btn-icon btn-primary" data-bs-toggle="tooltip" data-bs-placement="top"  title="' . $social_media_list[$option] . '"><span class="tf-icons bx bxl-' . $option  . '"></span></a>';
                        }
                    }
                    ?>
                    </div>
                    <?php } ?>
                </div>
                    <?php } else {
                        echo '<div class="alert alert-danger">' . esc_html__('No users found.', 'pwork') . '</div>';
                    }
                }
                ?>
            </div>
            <div class="content-backdrop fade"></div>
        </div>
    </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php include_once('footer.php'); ?>