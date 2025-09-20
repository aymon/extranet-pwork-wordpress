<?php include_once('header.php'); ?>
<div id="pwork-settings-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-sm container-p-y">
                <div class="card">
                    <?php
                    $custom_avatar = PworkSettings::get_option('user_avatar', 'disable');
                    $user_id = get_current_user_id();
                    ?>
                    <div class="pwork-card-header card-header align-items-center">
                        <div class="pwork-card-header-title">
                            <h3><?php echo esc_html__('Profile Details', 'pwork'); ?></h3>
                        </div>
                        <?php if ($custom_avatar == 'enable') { ?>
                            <div>
                                <div class="d-flex align-items-center">
                                    <div id="pwork-avatar-data">
                                        <?php echo get_avatar( $user_id, 160 ); ?>
                                    </div>
                                    <label id="pwork-upload-avatar-label" for="pwork-upload-avatar" class="btn btn-sm btn-secondary ms-2" tabindex="0">
                                        <span class="d-none d-sm-block"><?php echo esc_html__('Upload New', 'pwork'); ?></span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                        <input autocomplete="off" type="file" id="pwork-upload-avatar" name="pwork-upload-avatar" class="account-file-input" accept="image/png, image/jpeg, image/webp" hidden>
                                    </label>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row">
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-profile-nickname-input" class="form-label"><?php echo esc_html__( 'Nickname', 'pwork' ); ?></label>
                                <input autocomplete="off" class="form-control" type="text" name="pwork-profile-nickname-input" id="pwork-profile-nickname-input" value="<?php the_author_meta( 'nickname', $user_id ); ?>">
                            </div>
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-profile-firstname-input" class="form-label"><?php echo esc_html__( 'First Name', 'pwork' ); ?></label>
                                <input autocomplete="off" type="text" class="form-control" id="pwork-profile-firstname-input" name="pwork-profile-firstname-input" value="<?php the_author_meta( 'first_name', $user_id ); ?>">
                            </div>
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-profile-lastname-input" class="form-label"><?php echo esc_html__( 'Last Name', 'pwork' ); ?></label>
                                <input autocomplete="off" type="text" class="form-control" id="pwork-profile-lastname-input" name="pwork-profile-lastname-input" value="<?php the_author_meta( 'last_name', $user_id ); ?>">
                            </div>
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-profile-displayname-input" class="form-label"><?php echo esc_html__( 'Public Name', 'pwork' ); ?></label>
                                <select id="pwork-profile-displayname-input" name="pwork-profile-displayname-input" class="form-select" autocomplete="off">
                                    <?php 
                                    $displayValues = array(
                                        'display_nickname' => esc_html__( 'Nickname', 'pwork' ),
                                        'display_firstname' => esc_html__( 'First Name', 'pwork' ),
                                        'display_lastname' => esc_html__( 'Last Name', 'pwork' ),
                                        'display_firstlast' => esc_html__( 'First Name & Last Name', 'pwork' ),
                                        'display_lastfirst' => esc_html__( 'Last Name & First Name', 'pwork' )
                                    ); 
                                    $display = get_user_meta($user_id, 'pwork_displayname', true);
                                    if (empty($display)) {
                                        $display = 'display_nickname';
                                    }
                                    foreach ($displayValues as $key => $val) {
                                        if ($key == $display) {
                                            echo '<option value="' . esc_attr($key) . '" selected>' . esc_attr($val) . '</option>';
                                        } else {
                                            echo '<option value="' . esc_attr($key) . '">' . esc_attr($val) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-profile-location-input" class="form-label"><?php echo esc_html__( 'Location', 'pwork' ); ?></label>
                                <input autocomplete="off" type="text" class="form-control" id="pwork-profile-location-input" name="pwork-profile-location-input" value="<?php echo esc_attr(get_user_meta($user_id, 'pwork_location', true)); ?>">
                            </div>
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-profile-date-input" class="form-label"><?php echo esc_html__( 'Date of Birth', 'pwork' ); ?></label>
                                <input autocomplete="off" id="pwork-profile-date-input" name="pwork-profile-date-input" class="form-control" type="date" value="<?php echo esc_attr(get_user_meta($user_id, 'pwork_birth_date', true)); ?>">
                            </div>
                            <div class="mb-3 col-12">
                                <label for="pwork-profile-location-input" class="form-label"><?php echo esc_html__( 'Job Title', 'pwork' ); ?></label>
                                <input autocomplete="off" type="text" class="form-control" id="pwork-profile-job-input" name="pwork-profile-job-input" value="<?php echo esc_attr(get_user_meta($user_id, 'pwork_job', true)); ?>">
                            </div>
                            <div id="pwork-profile-bio-input-wrap" class="col-12">
                                <label class="form-label"><?php echo esc_html__( 'Biographical Info', 'pwork' ); ?></label>
                                <div id="pwork-profile-bio-input"><?php the_author_meta( 'description', $user_id ); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="pwork-card-footer justify-content-between">
                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="btn btn-outline-danger"><?php echo esc_html__('Reset Password', 'pwork'); ?></a>
                        <button type="button" class="btn btn-primary pwork-save-settings"><?php echo esc_html__('Save Changes', 'pwork'); ?></button>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="pwork-card-header card-header">
                        <div class="pwork-card-header-title">
                            <h3><?php echo esc_html__('Contact Details', 'pwork'); ?></h3>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row">
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-profile-email-input" class="form-label"><?php echo esc_html__( 'Email Address', 'pwork' ); ?></label>
                                <input autocomplete="off" class="form-control" type="email" id="pwork-profile-email-input" name="pwork-profile-email-input" value="<?php the_author_meta( 'user_email', $user_id ); ?>">
                                <?php 
                                $emailCheck = get_user_meta($user_id, 'pwork_email_check', true);
                                if ($emailCheck && !empty($emailCheck) && $emailCheck == 'yes') {
                                    $emailCheck = 'checked'; 
                                } else {
                                    $emailCheck = ''; 
                                }
                                ?>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="pwork-profile-email-input-check" autocomplete="off" <?php echo esc_attr($emailCheck); ?>>
                                    <label class="form-check-label fst-italic" for="pwork-profile-email-input-check"><?php echo esc_html__( 'Visible to everyone', 'pwork' ); ?></label>
                                </div>
                            </div>
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-profile-tel-input" class="form-label"><?php echo esc_html__( 'Phone Number', 'pwork' ); ?></label>
                                <input autocomplete="off" type="tel" class="form-control" id="pwork-profile-tel-input" name="pwork-profile-tel-input" value="<?php echo esc_attr(get_user_meta($user_id, 'pwork_tel', true)); ?>">
                                <?php 
                                $phoneCheck = get_user_meta($user_id, 'pwork_phone_check', true);
                                if ($phoneCheck && !empty($phoneCheck) && $phoneCheck == 'yes') {
                                    $phoneCheck = 'checked'; 
                                } else {
                                    $phoneCheck = ''; 
                                }
                                ?>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="pwork-profile-tel-input-check" autocomplete="off" <?php echo esc_attr($phoneCheck); ?>>
                                    <label class="form-check-label fst-italic" for="pwork-profile-tel-input-check"><?php echo esc_html__( 'Visible to everyone', 'pwork' ); ?></label>
                                </div>
                            </div>
                            <div class="mb-3 col-12">
                                <form>
                                    <label class="form-label"><?php echo esc_html__( 'Social Media', 'pwork' ); ?></label>
                                    <div id="pwork-profile-icons-input" class="repeatable-container">
                                        <?php
                                        $social_media_list = Pwork::social_media_list();
                                        $icons = get_user_meta($user_id, 'pwork_icons', true);
                                        if (!empty($icons)) {
                                            $icons = json_decode($icons, true);
                                            foreach($icons as $option => $value) { ?>
                                                <div class="input-group mb-3">
                                                    <select id="pwork_user_icon_<?php echo esc_attr__($option); ?>" name="pwork_user_icon_<?php echo esc_attr__($option); ?>" class="form-select" autocomplete="off">
                                                        <?php 
                                                        foreach($social_media_list as $id => $key) { 
                                                            if ($id == $option) {
                                                                echo '<option value="' . esc_attr($id) . '" selected>' . esc_attr($key) . '</option>';
                                                            } else {
                                                                echo '<option value="' . esc_attr($id) . '">' . esc_attr($key) . '</option>';
                                                            }
                                                        } ?>
                                                    </select>
                                                    <input autocomplete="off" type="text" class="form-control" id="pwork_user_url_<?php echo esc_attr__($option); ?>" name="pwork_user_url_<?php echo esc_attr__($option); ?>" value="<?php echo esc_attr__($value); ?>">
                                                    <button type="button" class="btn btn-danger pwork-delete-user-icon"><i class="bx bx-trash"></i></button>
                                                </div>
                                            <?php }
                                        } 
                                        ?>
                                    </div>
                                    <button type="button" class="btn btn-secondary pwork-add-user-icon w-100"><?php echo esc_html__('Add New Item', 'pwork'); ?></button>
                                </form>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <?php 
                                    $contactCheck = get_user_meta($user_id, 'pwork_contact_check', true);
                                    if (!$contactCheck || empty($contactCheck)) {
                                        $contactCheck = 'checked';
                                    } else if ($contactCheck == 'yes') {
                                        $contactCheck = 'checked';
                                    } else {
                                        $contactCheck = ''; 
                                    }
                                    ?>
                                    <input class="form-check-input" type="checkbox" id="pwork-profile-contact-check" autocomplete="off" <?php echo esc_attr($contactCheck); ?>>
                                    <label class="form-check-label fst-italic" for="pwork-profile-contact-check"><?php echo esc_html__( 'Allow users to add me to their contacts', 'pwork' ); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pwork-card-footer">
                        <button type="button" class="btn btn-primary pwork-save-settings"><?php echo esc_html__('Save Changes', 'pwork'); ?></button>
                    </div>
                </div>
                <?php 
                $email_module = PworkSettings::get_option('email_module', 'enable');
                if($email_module == 'enable') {
                    $user_id = get_current_user_id();
                    $customnot = get_user_meta($user_id, 'pwork_customnot', true);
                    if ($customnot && $customnot == 'yes') {
                        $customnot = 'checked';
                    } else {
                        $customnot = '';
                    }
                    $dbnot = get_user_meta($user_id, 'pwork_dbnot', true);
                    if ($dbnot && $dbnot == 'yes') {
                        $dbnot = 'checked';
                    } else {
                        $dbnot = '';
                    }
                    $newcomment = get_user_meta($user_id, 'pwork_newcommentnot', true);
                    if ($newcomment && $newcomment == 'yes') {
                        $newcomment = 'checked';
                    } else {
                        $newcomment = '';
                    }
                    $newreply = get_user_meta($user_id, 'pwork_newreplynot', true);
                    if ($newreply && $newreply == 'yes') {
                        $newreply = 'checked';
                    } else {
                        $newreply = '';
                    }
                    $projectsnot = get_user_meta($user_id, 'pwork_projectsnot', true);
                    if ($projectsnot && $projectsnot == 'yes') {
                        $projectsnot = 'checked';
                    } else {
                        $projectsnot = '';
                    }
                    $projectactivitiesnot = get_user_meta($user_id, 'pwork_projectactivitiesnot', true);
                    if ($projectactivitiesnot && $projectactivitiesnot == 'yes') {
                        $projectactivitiesnot = 'checked';
                    } else {
                        $projectactivitiesnot = '';
                    }
                    $eventsnot = get_user_meta($user_id, 'pwork_eventsnot', true);
                    if ($eventsnot && $eventsnot == 'yes') {
                        $eventsnot = 'checked';
                    } else {
                        $eventsnot = '';
                    }
                    $messagesnot = get_user_meta($user_id, 'pwork_messagesnot', true);
                    if ($messagesnot && $messagesnot == 'yes') {
                        $messagesnot = 'checked';
                    } else {
                        $messagesnot = '';
                    }
                ?>
                <div class="card mt-4">
                    <div class="pwork-card-header card-header">
                        <div class="pwork-card-header-title">
                            <h3><?php echo esc_html__('Email Notifications', 'pwork'); ?></h3>
                        </div>
                    </div>
                    <div class="table-responsive">
                      <table class="table table-striped">
                        <tbody>
                          <tr>
                            <td class="text-nowrap"><?php echo esc_html__('New announcement', 'pwork'); ?></td>
                            <td>
                              <div class="form-check d-flex justify-content-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input ms-0" type="checkbox" id="pwork-custom-notifications" autocomplete="off" <?php echo esc_attr($customnot); ?>>
                                </div>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td class="text-nowrap"><?php echo esc_html__('New topic', 'pwork'); ?></td>
                            <td>
                              <div class="form-check d-flex justify-content-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input ms-0" type="checkbox" id="pwork-db-notifications" autocomplete="off" <?php echo esc_attr($dbnot); ?>>
                                </div>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td class="text-nowrap"><?php echo esc_html__('New comment on my topic', 'pwork'); ?></td>
                            <td>
                              <div class="form-check d-flex justify-content-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input ms-0" type="checkbox" id="pwork-newcomment-notifications" autocomplete="off" <?php echo esc_attr($newcomment); ?>>
                                </div>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td class="text-nowrap"><?php echo esc_html__('New reply on my comment', 'pwork'); ?></td>
                            <td>
                              <div class="form-check d-flex justify-content-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input ms-0" type="checkbox" id="pwork-newreply-notifications" autocomplete="off" <?php echo esc_attr($newreply); ?>>
                                </div>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td class="text-nowrap"><?php echo esc_html__('New project', 'pwork'); ?></td>
                            <td>
                              <div class="form-check d-flex justify-content-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input ms-0" type="checkbox" id="pwork-projects-notifications" autocomplete="off" <?php echo esc_attr($projectsnot); ?>>
                                </div>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td class="text-nowrap"><?php echo esc_html__('New activities in the projects I participated in', 'pwork'); ?></td>
                            <td>
                              <div class="form-check d-flex justify-content-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input ms-0" type="checkbox" id="pwork-projectactivities-notifications" autocomplete="off" <?php echo esc_attr($projectactivitiesnot); ?>>
                                </div>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td class="text-nowrap"><?php echo esc_html__('New event', 'pwork'); ?></td>
                            <td>
                              <div class="form-check d-flex justify-content-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input ms-0" type="checkbox" id="pwork-events-notifications" autocomplete="off" <?php echo esc_attr($eventsnot); ?>>
                                </div>
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td class="text-nowrap"><?php echo esc_html__('New message', 'pwork'); ?></td>
                            <td>
                              <div class="form-check d-flex justify-content-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input ms-0" type="checkbox" id="pwork-messages-notifications" autocomplete="off" <?php echo esc_attr($messagesnot); ?>>
                                </div>
                              </div>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div class="pwork-card-footer border-top-0">
                        <button type="button" class="btn btn-primary pwork-save-settings"><?php echo esc_html__('Save Changes', 'pwork'); ?></button>
                    </div>
                </div>
                <?php } ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</div>
<?php include_once('footer.php'); ?>