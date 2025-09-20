<?php include_once('header.php'); ?>
<?php
$private_projects = PworkSettings::get_option('private_projects', 'disable');
$postID = '';
if (isset($_GET['ID']) && !empty($_GET['ID'])) {
    $postID = (int) $_GET['ID'];
} else {
    wp_die(esc_html__('Project ID is required', 'pwork'));
}
$currentuser = wp_get_current_user();
$slug =  PworkSettings::get_option('slug', 'pwork');
$authorID = (int) get_post_field('post_author', $postID);
$user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $authorID;
$content = get_post_meta( $postID, 'pwork_project_desc', true );
$due = get_post_meta( $postID, 'pwork_project_due', true );
$members = get_post_meta( $postID, 'pwork_project_members', true );
$who_can_join = get_post_meta( $postID, 'pwork_project_who_can_join', true );
$checklist_check = get_post_meta( $postID, 'pwork_project_checklist_enable', true );
$checklist = get_post_meta( $postID, 'pwork_project_checklist', true );
$terms = get_the_terms( $postID, 'pworkprojectstags' );

if ($private_projects != 'disable' && !current_user_can('administrator')) {
    if (!empty($members) && is_array($members)) {
        if (!in_array($currentuser->ID, $members)) {
            wp_die(esc_html__('You are not allowed to view this project.', 'pwork'));
        }
    }
}
?>
<div id="pwork-single-project-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-lg container-p-y">
                <div class="card">
                    <div class="pwork-card-header card-header align-items-center">
                        <div class="pwork-card-header-title d-block d-md-flex align-items-center justify-content-between w-100">
                            <?php if (!empty($due)) { ?>
                            <h2 class="text-center text-md-left mb-4 mb-md-0"><?php echo get_the_title($postID);?></h2>
                            <div>
                                <div id="pwork-project-countdown" data-due="<?php echo esc_attr($due); ?>">
                                    <div id="pwork-due-countdown" class="d-flex align-items-start justify-content-center" data-finished="<?php echo esc_attr__( 'The project due date has arrived.', 'pwork' ); ?>">
                                        <div>
                                        <label class="form-label d-block mb-1 mt-0 fw-normal text-dark"><?php echo esc_html__( 'days', 'pwork' ); ?></label>
                                        <span id="due-days" class="fw-bold text-primary fs-4">0</span>
                                        </div>
                                        <div>
                                        <label class="form-label d-block mb-1 mt-0 fw-normal text-dark"><?php echo esc_html__( 'hours', 'pwork' ); ?></label>
                                        <span id="due-hours" class="fw-bold text-primary fs-4">0</span>
                                        </div>
                                        <div>
                                        <label class="form-label d-block mb-1 mt-0 fw-normal text-dark"><?php echo esc_html__( 'mins', 'pwork' ); ?></label>
                                        <span id="due-minutes" class="fw-bold text-primary fs-4">0</span>
                                        </div>
                                        <div>
                                        <label class="form-label d-block mb-1 mt-0 fw-normal text-dark"><?php echo esc_html__( 'secs', 'pwork' ); ?></label>
                                        <span id="due-seconds" class="fw-bold text-primary fs-4">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php } else { ?>
                            <h2><?php echo get_the_title($postID);?></h2>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row">
                            <div class="col-12 col-lg-7 order-2 order-lg-1 mt-4 mt-lg-0">
                            <?php 
                            echo wp_kses_post(wpautop($content));

                            if ($checklist_check == 'enable' && is_array($checklist)) {
                                echo '<hr class="mt-4 mb-4"><h4>' . esc_html__( 'Tasks', 'pwork' ) . '</h4>';
                                $checklist_total = count($checklist);
                                $checklist_completed = 0;
                                foreach ( (array) $checklist as $key => $entry ) {
                                    if ( isset( $entry['status'] ) ) {
                                        if ($entry['status'] == 'completed') {
                                            $checklist_completed = $checklist_completed + 1;
                                        }
                                    }
                                }
                                PworkProjects::progress_bar($checklist_completed, $checklist_total);
                                $checklist_i = 0;
                                $checklist_disabled = 'disabled';
                                if (in_array($currentuser->ID, $members)) {
                                    $checklist_disabled = '';
                                }
                                echo '<div class="tasks-list">';
                                foreach ( (array) $checklist as $key => $entry ) {
                                    $checklist_desc = $checklist_status = '';
                                    if ( isset( $entry['desc'] ) ) {
                                        $checklist_desc = esc_html( $entry['desc'] );
                                    }
                                    if ( isset( $entry['status'] ) ) {
                                        $checklist_status = esc_html( $entry['status'] );
                                    }
                                    if ($checklist_status == 'completed') {
                                        $checklist_status = 'checked';
                                    } else {
                                        $checklist_status = '';
                                    }
                                    echo '<div class="form-check"><input class="form-check-input" type="checkbox" autocomplete="off" value="" id="pwork-project-checkbox-' . esc_attr($checklist_i) . '" name="pwork-project-checkbox" data-item="' . esc_attr($checklist_i) . '" data-id="' . $postID . '" ' . esc_attr($checklist_status) . ' ' . esc_attr($checklist_disabled) . '><label class="form-check-label fw-bold" for="pwork-project-checkbox-' . esc_attr($checklist_i) . '"> ' . esc_html($checklist_desc) . '</label></div>';
                                    $checklist_i ++;
                                }
                                echo '</div>';
                            }
                            if (comments_open($postID)) {
                                echo '<hr class="mt-4 mb-4"><h4>' . esc_html__( 'Activity', 'pwork' ) . '</h4>';
                                if (!empty($members) && is_array($members) && in_array($currentuser->ID, $members)) {
                                    echo '<div id="pwork-project-comment"></div>';
                                    echo '<button type="button" class="btn btn-primary w-100 add-project-comment mt-4" data-id="' . esc_attr($postID) . '">' . esc_html__( 'Add Comment', 'pwork' ) . '</button>';
                                } else {
                                    echo '<div class="alert alert-warning">' . esc_html__( 'You must join the project to comment.', 'pwork' ) . '</div>';
                                }
                                if (get_comment_count($postID)['approved'] >= 1) {
                                    $args = array(
                                        'post_id' => $postID,
                                        'status'  => 'approve',
                                        'parent' => 0
                                    );
                                    $comments = get_comments( $args );
                                    if ($comments) {
                                        echo '<div class="pwork-project-comments-inner">';
                                        echo '<hr class="mt-4 mb-4"><h4 class="mb-4">' . esc_html__( 'Activities', 'pwork' ) . ' (' . get_comment_count($postID)['approved'] . ')</h4>';
                                        foreach ( $comments as $comment ) {
                                            $comment_id = (int) $comment->comment_ID;
                                            $parent = $comment->comment_parent;
                                            $author_id = $comment->user_id;
                                            $author_name = get_the_author_meta('display_name', $author_id);
                                            $author_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $author_id;
                                            $author_content = $comment->comment_content;
                            
                                            echo '<div class="pwork-message-bubble-wrap"><a href="' . esc_url($author_url) . '">' . get_avatar($author_id, 100) . '</a><div class="pwork-message-bubble shadow">' . wp_kses_post($author_content) . '<div class="pwork-message-bubble-info">' . human_time_diff( get_comment_date('U', $comment_id) , current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago.', 'pwork' ) . '</div></div></div>';
                                        }
                                        echo '</div>';
                                    }
                                }
                            }
                            ?>
                            
                            </div>
                            <div class="col-12 col-lg-5 order-1 order-lg-2 ps-0 ps-lg-4">
                            <?php 
                            if (has_post_thumbnail($postID)) {
                                $thumbnail_id = get_post_thumbnail_id($postID);
                                $thumbnail_src = wp_get_attachment_image_src($thumbnail_id, 'full', true);
                                echo '<img class="img-fluid rounded" src="' . esc_url($thumbnail_src[0]) . '">';
                            } ?>
                            <ul class="list-group mt-4 bg-lightest project-info-list">
                                <?php if (!empty($due)) { ?>
                                <li class="list-group-item d-flex align-items-center">
                                    <div>
                                    <label class="form-label d-block m-0 text-dark"><?php echo esc_html__( 'Due Date', 'pwork' ); ?></label>
                                    <span class="text-primary fw-bold"><?php echo esc_html(date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($due))); ?></span>
                                    </div>
                                </li>
                                <?php } ?>
                                <?php 
                                if (get_comment_count($postID)['approved'] >= 1) {
                                    $last_activity_args = array(
                                        'post_id' => $postID,
                                        'orderby' => array('comment_date'),
                                        'order' => 'DESC',
                                        'number' => 1
                                    );
                                    $last_activity = get_comments( $last_activity_args );
                                    $last_activity_id = $last_activity[0]->comment_ID;
                                ?>
                                <li class="list-group-item d-flex align-items-center">
                                    <div>
                                    <label class="form-label d-block m-0 text-dark"><?php echo esc_html__( 'Last Activity', 'pwork' ); ?></label>
                                    <span class="text-primary fw-bold"><?php echo esc_html(human_time_diff( get_comment_date('U', $last_activity_id) , current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'pwork' )); ?></span>
                                    </div>
                                </li>
                                <?php } ?>   
                                <?php if (!empty($members) && is_array($members)) { ?>
                                <li class="list-group-item d-flex align-items-center">
                                    <div>
                                    <label class="form-label d-block mb-1 text-dark"><?php echo esc_html__( 'Members', 'pwork' ); ?></label>
                                    <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center flex-wrap">
                                        <?php 
                                        foreach($members as $member_id) {
                                            $member = get_user_by('id', $member_id);
                                            $member_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $member_id;
                                            echo '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar pull-up" title="" data-bs-original-title="' . esc_attr($member->display_name) . '"><a href="' . esc_url($member_profile_url) . '" target="_blank">' . get_avatar($member_id, 100) . '</a></li>';
                                        }
                                        if ($who_can_join == 'everyone' && !in_array($currentuser->ID, $members)) {
                                            echo '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar pull-up" title="" data-bs-original-title="' . esc_attr__( 'Join', 'pwork' ) . '"><button type="button" class="btn btn-icon btn-primary rounded-pill join-project" data-id="' . $postID . '"><i class="bx bxs-user-plus"></i></button></li>';
                                        }
                                        ?>
                                    </ul>
                                    </div>
                                </li>
                                <?php }
                                if (!empty($terms) && is_array($terms)) {
                                    echo '<li class="list-group-item d-flex align-items-center"><div>';
                                    echo '<label class="form-label d-block mb-1 text-dark">' . esc_html__( 'Tags', 'pwork' ) . '</label>';
                                    foreach($terms as $term) {
                                        $color = get_term_meta( $term->term_id, 'pwork_tag_color', true );
                                        if (empty($color )) {
                                            $color = '#8592a3';
                                        }
                                        echo '<span class="badge me-1" style="background-color:' . esc_attr($color) . '"><a href="' . esc_url(get_site_url() . '/' . $slug . '/?page=projects&tagID=' . $term->term_id) . '">' . esc_html($term->name) . '</a></span>';
                                    }
                                    echo '</li>';
                                }
                                ?>
                            </ul>
                            </div>
                        </div>
                        <div class="col-12 modal-author order-3">
                            <?php echo esc_html__( 'Published on', 'pwork' ) . ' ' . get_the_date(get_option('date_format') . ' ' . get_option('time_format'), $postID) . ' ' . esc_html__( 'by', 'pwork' ) . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a>' ?>
                        </div>
                    </div>
                </div>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</div>
<?php include_once('footer.php'); ?>