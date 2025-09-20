<?php include_once('header.php'); ?>
<?php Pwork::remove_notification('projects'); ?>
<?php 
$currentuser = wp_get_current_user();
$slug = PworkSettings::get_option('slug', 'pwork');
$tagID = '';
if (isset($_GET['tagID']) && !empty($_GET['tagID'])) {
    $tagID = (int) $_GET['tagID'];
}
?>
<div id="pwork-projects-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-xl flex-grow-1 container-p-y">
                <div class="pwork-page-header d-flex justify-content-between align-items-center flex-wrap w-100 mb-4">
                    <h2 class="col-12 col-sm-5 fw-bold mb-4">
                        <?php echo esc_html__('Projects', 'pwork'); ?>
                    </h2>
                    <div class="col-12 col-sm-7">
                        <div class="input-group input-group-merge mb-4">
                            <input id="pwork-project-search-input" type="text" class="form-control form-control-lg" placeholder="<?php echo esc_attr__( 'Search by title...', 'pwork' ); ?>" autocomplete="off">
                            <div id="pwork-project-search-input-clear" class="input-group-text d-none" title="<?php echo esc_attr__( 'Clear', 'pwork' ); ?>">
                                <i class="bx bx-x cursor-pointer text-danger"></i>
                            </div>
                            <select id="pwork-project-search-tag" class="form-select" autocomplete="off">
                                <option value="" selected><?php echo esc_html__('All Tags', 'pwork'); ?></option>
                                <?php
                                $tags = get_terms([
                                    'taxonomy' => 'pworkprojectstags',
                                    'orderby' => 'name',
                                    'order' => 'ASC',
                                    'hide_empty' => true,
                                ]);
                                foreach ($tags as $tag){
                                    if (!empty($tagID) && $tag->term_id == $tagID) {
                                        echo '<option value="' . $tag->term_id . '" selected>' . $tag->name . ' (' . $tag->count . ')</option>';
                                    } else {
                                        echo '<option value="' . $tag->term_id . '">' . $tag->name . ' (' . $tag->count . ')</option>';
                                    }
                                }
                                ?>
                            </select>
                            <button id="pwork-project-search" type="button" class="btn btn-lg btn-primary"><span class="tf-icons bx bx-search"></span></button>
                        </div>
                    </div>
                </div>
                <div id="pwork-projects-wrap" class="row pwork-hide-row" data-userid="">
                    <div class="grid-sizer col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3"></div>
                    <?php
                    $limit = PworkSettings::get_option('projects_limit', 12);
                    $args = array(
                        'post_status' => 'publish',
                        'post_type' => 'pworkprojects',
                        'posts_per_page'  => $limit,
                        'order'  => 'DESC',
                        'orderby'  => 'post_date'
                    );
                    if (!empty($tagID)) {
                        $args['tax_query'] = array(
                            array(
                                'taxonomy' => 'pworkprojectstags',
                                'field' => 'term_id',
                                'terms' => (int) $tagID,
                            ),
                        );
                    }
                    $query = new WP_Query($args);
                    $visible_posts = $query->post_count;
                    if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) : $query->the_post();
                    $postID = get_the_ID();
                    $authorID = (int) get_post_field('post_author', $postID);
                    $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $authorID;
                    $project_url = get_site_url() . '/' . $slug . '/?page=projects-single&ID=' . $postID;
                    $content = get_post_meta( $postID, 'pwork_project_desc', true );
                    $due = get_post_meta( $postID, 'pwork_project_due', true );
                    $members = get_post_meta( $postID, 'pwork_project_members', true );
                    $who_can_join = get_post_meta( $postID, 'pwork_project_who_can_join', true );
                    $checklist_check = get_post_meta( $postID, 'pwork_project_checklist_enable', true );
                    $checklist = get_post_meta( $postID, 'pwork_project_checklist', true );
                    $terms = get_the_terms( $postID, 'pworkprojectstags' );
                    ?>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3">
                        <div class="card post-card mb-4">
                            <?php 
                            if (has_post_thumbnail()) {
                                $thumbnail_id = get_post_thumbnail_id();
                                $thumbnail_src = wp_get_attachment_image_src($thumbnail_id, 'large', true);
                                echo '<a href="' . esc_url($project_url) . '"><img class="card-img-top" src="' . esc_url($thumbnail_src[0]) . '"></a>';
                            } ?>
                            <div class="card-body">
                            <span class="card-date"><a href="<?php echo esc_url($project_url); ?>" style="color:inherit"><?php echo get_the_date(get_option('date_format') . ' ' . get_option('time_format')); ?></a></span>
                            <h4 class="card-title"><a href="<?php echo esc_url($project_url); ?>" style="color:inherit"><?php the_title(); ?></a></h4>
                            <p class="card-text"><?php the_excerpt(); ?></p>
                            <?php if (!empty($members) && is_array($members)) { ?>
                            <ul class="list-unstyled users-list avatar-group d-flex align-items-center me-0 ms-0 mt-3 mb-0">
                                <?php 
                                foreach($members as $member_id) {
                                    $member = get_user_by('id', $member_id);
                                    $member_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $member_id;
                                    echo '<li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar avatar-sm pull-up" title="" data-bs-original-title="' . esc_attr($member->display_name) . '"><a href="' . esc_url($member_profile_url) . '" target="_blank">' . get_avatar($member_id, 100) . '</a></li>';
                                }
                                ?>
                            </ul>
                            <?php } ?>
                            </div>
                            <?php if (!empty($terms) && is_array($terms)) { ?>
                            <div class="pwork-card-footer justify-content-start">
                            <?php
                            $badges = '';
                            if ($terms) {
                                foreach($terms as $term) {
                                    $color = get_term_meta( $term->term_id, 'pwork_tag_color', true );
                                    if (empty($color )) {
                                        $color = '#8592a3';
                                    }
                                    $badges = '<span class="badge me-1 mt-1" style="background-color:' . esc_attr($color) . '"><a href="' . esc_url(get_site_url() . '/' . $slug . '/?page=projects&tagID=' . $term->term_id) . '">' . esc_html($term->name) . '</a></span>' . $badges;
                                }
                            }
                            echo wp_kses_post($badges);
                            ?>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                    endwhile;
                    if ($limit == $visible_posts) { ?>
                        <div class="col-12 mt-2">
                            <button id="pwork-load-more-projects" type="button" class="btn btn-lg btn-primary w-100" data-offset="<?php echo esc_attr($limit); ?>"><?php echo esc_html__( 'LOAD MORE', 'pwork' ); ?></button>
                        </div>
                        <?php }
                        wp_reset_postdata();
                    } else {
                        echo '<div class="col-12"><div class="alert alert-warning">' . esc_html__('Nothing found.', 'pwork') . '</div></div>';
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