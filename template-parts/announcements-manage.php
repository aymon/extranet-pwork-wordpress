<?php include_once('header.php'); ?>
<?php 
$slug = PworkSettings::get_option('slug', 'pwork');
$edit = '';
$title = '';
$content = '';
$excerpt = '';
$card = 'default';
$widget_title = esc_html__('Add Announcement', 'pwork');
$btn_text = esc_html__('Publish', 'pwork');
$selected_tags = array();
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit = $_GET['edit'];
}
if (!empty($edit)) {
    $widget_title = esc_html__('Edit Announcement', 'pwork');
    $btn_text = esc_html__('Save Changes', 'pwork');
    $post_id = $_GET['edit'];
    $title = get_the_title($post_id);
    $excerpt = get_the_excerpt($post_id);
    $content = get_post_meta($post_id, 'pwork_announcement_content', true );
    $card = get_post_meta($post_id, 'pwork_announcement_card', true );
    $gettags = get_the_terms($post_id, 'pworkannstags');
    if ($gettags && !empty($gettags)) {
        foreach ($gettags as $gettag){
            array_push($selected_tags, $gettag->term_id);
        }
    }
}
?>
<div id="pwork-anns-manage-page" class="layout-wrapper layout-content-navbar">
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
                                <label for="pwork-ann-title" class="form-label"><?php echo esc_html__( 'Title', 'pwork' ); ?> <span class="text-danger">*</span></label>
                                <input autocomplete="off" class="form-control" type="text" name="pwork-ann-title" id="pwork-ann-title" value="<?php echo esc_attr($title); ?>">
                            </div>
                            <div class="mb-3 col-12">
                                <label for="pwork-ann-excerpt" class="form-label"><?php echo esc_html__( 'Excerpt', 'pwork' ); ?></label>
                                <textarea autocomplete="off" class="form-control" id="pwork-ann-excerpt" name="pwork-ann-excerpt" rows="2">
                                <?php echo esc_html($excerpt); ?>
                                </textarea>
                            </div>
                            <div id="pwork-ann-content-wrap" class="mb-3 col-12">
                                <label for="pwork-ann-content" class="form-label"><?php echo esc_html__( 'Content', 'pwork' ); ?></label>
                                <div id="pwork-ann-content"><?php echo wp_kses_post($content); ?></div>
                            </div>
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-ann-featured" class="form-label"><?php echo esc_html__( 'Featured Image', 'pwork' ); ?></label>
                                <input class="form-control" type="file" id="pwork-ann-featured" name="pwork-ann-featured" accept="image/png, image/jpeg, image/webp" autocomplete="off">
                                <label for="pwork-ann-card" class="form-label mt-3"><?php echo esc_html__( 'Card Style', 'pwork' ); ?></label>
                                <select autocomplete="off" id="pwork-ann-card" name="pwork-ann-card" class="form-select">
                                    <?php
                                    $cardArray = array(
                                        'default' => esc_html__( 'Default', 'pwork' ),
                                        'primary' => esc_html__( 'Primary', 'pwork' ),
                                        'secondary' => esc_html__( 'Secondary', 'pwork' ),
                                        'info' => esc_html__( 'Info', 'pwork' ),
                                        'danger' => esc_html__( 'Danger', 'pwork' ),
                                        'warning' => esc_html__( 'Warning', 'pwork' ),
                                        'success' => esc_html__( 'Success', 'pwork' ),
                                    );
                                    foreach($cardArray as $key => $item) {
                                        if ($key == $card) {
                                            echo '<option value="' . $key . '" selected>' . $item . '</option>';
                                        } else {
                                            echo '<option value="' . $key . '">' . $item . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="pwork-ann-tags" class="form-label"><?php echo esc_html__( 'Tag(s)', 'pwork' ); ?></label>
                                <select  multiple="" autocomplete="off" id="pwork-ann-tags" name="pwork-ann-tags" class="form-select">
                                    <?php
                                    $tags = get_terms([
                                        'taxonomy' => 'pworkannstags',
                                        'orderby' => 'name',
                                        'order' => 'ASC',
                                        'hide_empty' => false
                                    ]);
                                    foreach ($tags as $tag){
                                        if (in_array($tag->term_id, $selected_tags)) {
                                            echo '<option value="' . $tag->term_id . '" selected>' . $tag->name . '</option>';
                                        } else {
                                            echo '<option value="' . $tag->term_id . '">' . $tag->name . '</option>';
                                        }
                                        
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="pwork-card-footer justify-content-end">
                        <button id="pwork-ann-submit" type="button" class="btn btn-primary" data-id="<?php echo esc_attr($edit); ?>"><?php echo esc_html($btn_text); ?></button>
                    </div>
                </div>
                <?php
                if (!isset($_GET['edit']) || empty($_GET['edit'])) {
                $ann_limit = PworkSettings::get_option('ann_limit', 10);
                $anns_title = esc_html__('My Announcements', 'pwork');
                $ann_args = array(
                    'post_status' => 'publish',
                    'post_type' => 'pworkanns',
                    'posts_per_page'  => 99999,
                    'author__in' => get_current_user_id(),
                    'order'  => 'DESC',
                    'orderby'  => 'post_date'
                );
                if (current_user_can('administrator') || current_user_can('editor')) {
                    $anns_title = esc_html__('All Announcements', 'pwork');
                    unset($ann_args['author__in']);
                }
                $ann_query = new WP_Query($ann_args);
                if ( $ann_query->have_posts() ) {
                ?>
                <div class="card mt-4">
                    <div class="pwork-card-header card-header">
                        <div class="pwork-card-header-title">
                            <h3><?php echo esc_html($anns_title); ?></h3>
                        </div>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table id="pwork-anns-table" class="table table-striped">
                            <tbody id="pwork-anns-tbody" class="table-border-bottom-0 paginated" data-perpage="<?php echo esc_attr($ann_limit); ?>">
                            <?php while ( $ann_query->have_posts() ) : $ann_query->the_post(); ?>
                                <?php 
                                $postID = get_the_ID();
                                $title = get_the_title();
                                $edit_ann_url = get_site_url() . '/' . $slug . '/?page=announcements-manage&edit=' . $postID;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($title); ?></strong>
                                        <small class="d-block mt-2"><?php echo esc_html(get_the_date(get_option('date_format'))); ?></small>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo esc_url($edit_ann_url); ?>" class="btn btn-sm btn-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Edit', 'pwork'); ?>"><span class="tf-icons bx bxs-edit-alt"></span></a>
                                            <button type="button" class="btn btn-sm btn-danger pwork-delete-ann" title="<?php echo esc_attr__('Delete', 'pwork'); ?>" data-id="<?php echo esc_attr($postID); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><span class="tf-icons bx bxs-trash"></span></button>
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