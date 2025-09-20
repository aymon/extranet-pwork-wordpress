<?php include_once('header.php'); ?>
<?php 
$slug = PworkSettings::get_option('slug', 'pwork');
$event_color = PworkSettings::get_option('event_color', '#6658ea'); 
$edit = '';
$title = '';
$start = '';
$end = '';
$url = '';
$allDayYes = '';
$widget_title = esc_html__('Add Event', 'pwork');
$btn_text = esc_html__('Publish', 'pwork');
$allDayNo = 'checked';
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit = $_GET['edit'];
}
if (!empty($edit)) {
    $widget_title = esc_html__('Edit Event', 'pwork');
    $btn_text = esc_html__('Save Changes', 'pwork');
    $post_id = $_GET['edit'];
    $event_color = get_post_meta($post_id, 'pwork_event_color', true );
    $title = get_the_title($post_id);
    $start = get_post_meta($post_id, 'pwork_event_start', true );
    $end = get_post_meta($post_id, 'pwork_event_end', true );
    $url = get_post_meta($post_id, 'pwork_event_url', true );
    $allday = get_post_meta($post_id, 'pwork_event_all_day', true );
    if ($allday == 'true') {
        $allDayYes = 'checked';
        $allDayNo = '';
    }
}
?>
<div id="pwork-events-manage-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-sm container-p-y">
                <div class="card">
                    <div class="pwork-card-header card-header">
                        <div class="pwork-card-header-title">
                            <h3><?php echo esc_html($widget_title); ?></h3>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row">
                            <div class="mb-3 col-12">
                                <label for="pwork-event-title" class="form-label"><?php echo esc_html__( 'Title', 'pwork' ); ?> <span class="text-danger">*</span></label>
                                <input autocomplete="off" class="form-control" type="text" name="pwork-event-title" id="pwork-event-title" value="<?php echo esc_attr($title); ?>">
                            </div>
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-event-start" class="form-label"><?php echo esc_html__( 'Start Date', 'pwork' ); ?> <span class="text-danger">*</span></label>
                                <input autocomplete="off" class="form-control" type="datetime-local" name="pwork-event-start" id="pwork-event-start" value="<?php echo esc_attr($start); ?>">
                            </div>
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-event-end" class="form-label"><?php echo esc_html__( 'End Date', 'pwork' ); ?> <span class="text-danger">*</span></label>
                                <input autocomplete="off" class="form-control" type="datetime-local" name="pwork-event-end" id="pwork-event-end" value="<?php echo esc_attr($end); ?>">
                            </div>
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-event-color" class="form-label"><?php echo esc_html__( 'Color', 'pwork' ); ?> <span class="text-danger">*</span></label>
                                <input autocomplete="off" class="form-control" type="color" name="pwork-event-color" id="pwork-event-color" value="<?php echo esc_attr($event_color); ?>">
                            </div>
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-event-url" class="form-label"><?php echo esc_html__( 'URL (Optional)', 'pwork' ); ?></label>
                                <input autocomplete="off" class="form-control" type="url" name="pwork-event-url" id="pwork-event-url"  value="<?php echo esc_attr($url); ?>">
                            </div>
                            <div class="mb-3 col-12">
                                <label class="form-label d-block"><?php echo esc_html__( 'All Day Event', 'pwork' ); ?></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="allDayEvent" id="pwork-event-all-day-yes" value="true" <?php echo esc_attr($allDayYes); ?>>
                                    <label class="form-check-label" for="pwork-event-all-day-yes"><?php echo esc_html__( 'Yes', 'pwork' ); ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="allDayEvent" id="pwork-event-all-day-no" value="false" <?php echo esc_attr($allDayNo); ?>>
                                    <label class="form-check-label" for="pwork-event-all-day-no"><?php echo esc_html__( 'No', 'pwork' ); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pwork-card-footer justify-content-end">
                        <button id="pwork-event-submit" type="button" class="btn btn-primary" data-id="<?php echo esc_attr($edit); ?>"><?php echo esc_html($btn_text); ?></button>
                    </div>
                </div>
                <?php
                if (!isset($_GET['edit']) || empty($_GET['edit'])) {
                $event_limit = PworkSettings::get_option('event_limit', 10);
                $events_title = esc_html__('My Events', 'pwork');
                $event_args = array(
                    'post_status' => 'publish',
                    'post_type' => 'pworkevents',
                    'posts_per_page'  => 99999,
                    'author__in' => get_current_user_id(),
                    'order'  => 'DESC',
                    'orderby'  => 'post_date'
                );
                if (current_user_can('administrator') || current_user_can('editor')) {
                    $events_title = esc_html__('All Events', 'pwork');
                    unset($event_args['author__in']);
                }
                $event_query = new WP_Query($event_args);
                if ( $event_query->have_posts() ) {
                ?>
                <div class="card mt-4">
                    <div class="pwork-card-header card-header">
                        <div class="pwork-card-header-title">
                            <h3><?php echo esc_html($events_title); ?></h3>
                        </div>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table id="pwork-events-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo esc_html__('Event', 'pwork'); ?></th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody id="pwork-events-tbody" class="table-border-bottom-0 paginated" data-perpage="<?php echo esc_attr($event_limit); ?>">
                            <?php while ( $event_query->have_posts() ) : $event_query->the_post(); ?>
                            <?php 
                            $postID = get_the_ID();
                            $title = get_the_title();
                            $start = get_post_meta( $postID, 'pwork_event_start', true ); 
                            $start = date(get_option('date_format') . ' ' .  get_option('time_format'), strtotime($start));
                            $end = get_post_meta( $postID, 'pwork_event_end', true );
                            $end = date(get_option('date_format')  . ' ' . get_option('time_format'), strtotime($end));
                            $authorID = (int) get_post_field('post_author', $postID);
                            $edit_event_url = get_site_url() . '/' . $slug . '/?page=events-manage&edit=' . $postID;
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($title); ?></strong>
                                        <small class="d-block mt-1"><?php echo esc_html__( 'Start:', 'pwork' ) . ' ' . esc_html($start); ?></small>
                                        <small class="d-block"><?php echo esc_html__( 'End:', 'pwork' ) . ' ' . esc_html($end); ?></small>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo esc_url($edit_event_url); ?>" class="btn btn-sm btn-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Edit event', 'pwork'); ?>"><span class="tf-icons bx bxs-edit-alt"></span></a>
                                            <button type="button" class="btn btn-sm btn-danger pwork-delete-event" title="<?php echo esc_attr__('Delete event', 'pwork'); ?>" data-id="<?php echo esc_attr($postID); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><span class="tf-icons bx bxs-trash"></span></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                            endwhile; 
                            wp_reset_postdata(); 
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php }
                } ?>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</div>
<?php include_once('footer.php'); ?>