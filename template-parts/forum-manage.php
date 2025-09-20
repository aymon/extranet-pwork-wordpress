<?php include_once('header.php'); ?>
<?php 
$slug = PworkSettings::get_option('slug', 'pwork');
$edit = '';
$title = '';
$content = '';
$card = 'default';
$selected_tags = array();
$widget_title = esc_html__('Add Topic', 'pwork');
$btn_text = esc_html__('Publish', 'pwork');
$status = 'checked';
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit = $_GET['edit'];
}
if (!empty($edit)) {
    $widget_title = esc_html__('Edit Topic', 'pwork');
    $btn_text = esc_html__('Save Changes', 'pwork');
    $post_id = $_GET['edit'];
    $post = get_post($post_id);
    $title = get_the_title($post_id);
    $content = get_post_meta($post_id, 'pwork_forum_content', true );
    $status = $post->comment_status;
    $gettags = get_the_terms($post_id, 'pworkforumtags');
    if ($gettags && !empty($gettags)) {
        foreach ($gettags as $gettag){
            array_push($selected_tags, $gettag->term_id);
        }
    } 
}
?>
<div id="pwork-forum-manage-page" class="layout-wrapper layout-content-navbar">
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
                                <label for="pwork-topic-title" class="form-label"><?php echo esc_html__( 'Title', 'pwork' ); ?> <span class="text-danger">*</span></label>
                                <input autocomplete="off" class="form-control" type="text" name="pwork-topic-title" id="pwork-topic-title" value="<?php echo esc_attr($title); ?>">
                            </div>
                            <div id="pwork-topic-content-wrap" class="mb-3 col-12">
                                <label for="pwork-topic-content" class="form-label"><?php echo esc_html__( 'Content', 'pwork' ); ?></label>
                                <div id="pwork-topic-content"><?php echo wp_kses_post(wpautop($content)); ?></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="pwork-topic-tag" class="form-label"><?php echo esc_html__( 'Tag(s)', 'pwork' ); ?></label>
                                <select  multiple="" autocomplete="off" id="pwork-topic-tag" name="pwork-topic-tag" class="form-select">
                                    <?php
                                    $tags = get_terms([
                                        'taxonomy' => 'pworkforumtags',
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
                            <div class="col-12 col-md-6">
                                <label for="pwork-comments-status" class="form-label"><?php echo esc_html__( 'Status', 'pwork' ); ?></label>
                                <select autocomplete="off" id="pwork-comments-status" name="pwork-comments-status" class="form-select">
                                    <option value="open" <?php if ($status == 'open') { echo 'selected'; } ?>><?php echo esc_html__('Open', 'pwork'); ?></option>
                                    <option value="closed" <?php if ($status == 'closed') { echo 'selected'; } ?>><?php echo esc_html__('Closed', 'pwork'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="pwork-card-footer justify-content-end">
                        <button id="pwork-topic-submit" type="button" class="btn btn-primary" data-id="<?php echo esc_attr($edit); ?>"><?php echo esc_html($btn_text); ?></button>
                    </div>
                </div>
                <?php
                if (!isset($_GET['edit']) || empty($_GET['edit'])) {
                $topic_limit = PworkSettings::get_option('topic_limit', 10);
                $topic_title = esc_html__('My Topics', 'pwork');
                $topic_args = array(
                    'post_status' => 'publish',
                    'post_type' => 'pworkforum',
                    'posts_per_page'  => 99999,
                    'author__in' => get_current_user_id(),
                    'order'  => 'DESC',
                    'orderby'  => 'post_date'
                );
                if (current_user_can('administrator') || current_user_can('editor')) {
                    $topic_title = esc_html__('All Topics', 'pwork');
                    unset($topic_args['author__in']);
                }
                $topic_query = new WP_Query($topic_args);
                if ( $topic_query->have_posts() ) {
                ?>
                <div class="card mt-4">
                    <div class="pwork-card-header card-header">
                        <div class="pwork-card-header-title">
                            <h3><?php echo esc_html($topic_title); ?></h3>
                        </div>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table id="pwork-topic-table" class="table table-striped">
                            <tbody id="pwork-topic-tbody" class="table-border-bottom-0 paginated" data-perpage="<?php echo esc_attr($topic_limit); ?>">
                            <?php while ( $topic_query->have_posts() ) : $topic_query->the_post(); ?>
                                <?php 
                                $postID = get_the_ID();
                                $title = get_the_title();
                                $edit_topic_url = get_site_url() . '/' . $slug . '/?page=forum-manage&edit=' . $postID;
                                $topic_url = get_site_url() . '/' . $slug . '/?page=forum&topicID=' . $postID;
                                ?>
                                <tr>
                                    <td>
                                    <a href="<?php echo esc_url($topic_url); ?>" class="d-block"><strong><?php echo esc_html($title); ?></strong></a>
                                        <small class="d-block mt-1"><?php echo esc_html(get_the_date(get_option('date_format'))); ?></small>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo esc_url($edit_topic_url); ?>" class="btn btn-sm btn-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Edit', 'pwork'); ?>"><span class="tf-icons bx bxs-edit-alt"></span></a>
                                            <button type="button" class="btn btn-sm btn-danger pwork-delete-topic" title="<?php echo esc_attr__('Delete', 'pwork'); ?>" data-id="<?php echo esc_attr($postID); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><span class="tf-icons bx bxs-trash"></span></button>
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