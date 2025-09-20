<?php include_once('header.php'); ?>
<div id="pwork-dashboard-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div id="pwork-page-loader"></div>
            <div class="container-fluid flex-grow-1 container-p-y">
                <div class="pwork-page-header d-flex align-items-center justify-content-between flex-wrap w-100">
                    <h4 class="fw-bold mb-4">
                    <?php
                    $widgets = Pwork::get_valid_widgets();
                    $user_id = get_current_user_id();
                    $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $user_id;
                    $display_name = get_the_author_meta( 'display_name', $user_id);
                    echo  '<a href="' . esc_url($user_profile_url) . '">' . get_avatar( $user_id, 300 ) . '</a>';
                    echo esc_html__('Welcome,', 'pwork') . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html($display_name) . '</a>';
                    ?>
                    </h4>
                    <div class="mb-4" title="<?php echo esc_attr__('Manage Widgets', 'pwork'); ?>">
                        <button type="button" class="btn btn-icon btn-primary" data-bs-toggle="offcanvas" data-bs-target="#pwork-widget-settings" aria-controls="pwork-widget-settings"><span class="tf-icons bx bxs-cog"></span></button>
                    </div>
                    <div class="offcanvas offcanvas-end" tabindex="-1" id="pwork-widget-settings" aria-hidden="true">
                        <div class="offcanvas-header">
                        <h4 class="offcanvas-title"><?php echo esc_html__('Widgets', 'pwork'); ?></h4>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="<?php echo esc_attr__('Close', 'pwork'); ?>"></button>
                        </div>
                        <div id="pwork-widget-settings-list" class="offcanvas-body mx-0 flex-grow-0">
                            <div class="pwork-widget-settings-list">
                                <?php foreach ($widgets as $key => $widget) { ?>
                                <div class="form-check d-flex justify-content-between align-items-center w-100">
                                    <label for="<?php echo esc_attr($key); ?>-switch" class="m-0"><strong><?php echo esc_html($widget[0]); ?></strong></label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input m-0" type="checkbox" id="<?php echo esc_attr($key); ?>-switch" data-key="<?php echo esc_attr($key); ?>" data-widget="<?php echo esc_attr($key); ?>-widget" autocomplete="off" checked>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            <button id="pwork-reset-dashboard" type="button" class="btn btn-outline-danger w-100 mt-2"><?php echo esc_html__('Reset', 'pwork'); ?></button>
                        </div>
                    </div>
                </div>
                <div id="pworkDashboard" class="row">
                <div class="grid-sizer col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3"></div>
                <?php
                foreach ($widgets as $widget) {
                    include_once($widget[1]);
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