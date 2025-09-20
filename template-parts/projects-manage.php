<?php include_once('header.php'); ?>
<?php 
$slug = PworkSettings::get_option('slug', 'pwork');
$edit = '';
$title = '';
$content = '';
$excerpt = '';
$checklist = '';
$tasks = array();
$due = '';
$who_can_join = 'everyone';
$card = 'default';
$selected_tags = array();
$selected_members = array(get_current_user_id());
$widget_title = esc_html__('Add Project', 'pwork');
$btn_text = esc_html__('Publish', 'pwork');
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit = $_GET['edit'];
}
if (!empty($edit)) {
    $widget_title = esc_html__('Edit Project', 'pwork');
    $btn_text = esc_html__('Save Changes', 'pwork');
    $post_id = $_GET['edit'];
    $post = get_post($post_id);
    $title = get_the_title($post_id);
    $due = get_post_meta($post_id, 'pwork_project_due', true );
    $content = get_post_meta($post_id, 'pwork_project_desc', true );
    $excerpt = get_the_excerpt($post_id);
    $selected_members = get_post_meta($post_id, 'pwork_project_members', true );
    $who_can_join = get_post_meta($post_id, 'pwork_project_who_can_join', true );
    $checklist = get_post_meta($post_id, 'pwork_project_checklist_enable', true );
    $tasks = get_post_meta($post_id, 'pwork_project_checklist', true );
    if ($checklist == 'enable') {
        $checklist = 'checked';
    }
    $gettags = get_the_terms($post_id, 'pworkprojectstags');
    if ($gettags && !empty($gettags)) {
        foreach ($gettags as $gettag){
            array_push($selected_tags, $gettag->term_id);
        }
    }
}
?>
<div id="pwork-projects-manage-page" class="layout-wrapper layout-content-navbar">
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
                                <label for="pwork-project-title" class="form-label"><?php echo esc_html__( 'Title', 'pwork' ); ?> <span class="text-danger">*</span></label>
                                <input autocomplete="off" class="form-control" type="text" name="pwork-project-title" id="pwork-project-title" value="<?php echo esc_attr($title); ?>">
                            </div>
                            <div class="mb-3 col-12">
                                <label for="pwork-project-due" class="form-label"><?php echo esc_html__( 'Due Date', 'pwork' ); ?></label>
                                <input autocomplete="off" class="form-control" type="datetime-local" name="pwork-project-due" id="pwork-project-due" value="<?php echo esc_attr($due); ?>">
                            </div>
                            <div class="mb-3 col-12">
                                <label for="pwork-project-excerpt" class="form-label"><?php echo esc_html__( 'Excerpt', 'pwork' ); ?></label>
                                <textarea autocomplete="off" class="form-control" id="pwork-project-excerpt" name="pwork-project-excerpt" rows="2">
                                <?php echo esc_html($excerpt); ?>
                                </textarea>
                            </div>
                            <div id="pwork-project-content-wrap" class="mb-3 col-12">
                                <label for="pwork-project-content" class="form-label"><?php echo esc_html__( 'Content', 'pwork' ); ?></label>
                                <div id="pwork-project-content"><?php echo wp_kses_post(wpautop($content)); ?></div>
                            </div>
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-project-featured" class="form-label"><?php echo esc_html__( 'Featured Image', 'pwork' ); ?></label>
                                <input class="form-control mb-3" type="file" id="pwork-project-featured" name="pwork-project-featured" accept="image/png, image/jpeg, image/webp" autocomplete="off">
                                <label for="pwork-project-tags" class="form-label"><?php echo esc_html__( 'Tag(s)', 'pwork' ); ?></label>
                                <select multiple="" autocomplete="off" id="pwork-project-tags" name="pwork-project-tags" class="form-select" autocomplete="off">
                                    <?php
                                    $tags = get_terms([
                                        'taxonomy' => 'pworkprojectstags',
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
                            <div class="mb-3 col-12 col-md-6">
                                <label for="pwork-project-who" class="form-label"><?php echo esc_html__( 'Who Can Join?', 'pwork' ); ?></label>
                                <select autocomplete="off" id="pwork-project-who" name="pwork-project-who" class="form-select mb-3">
                                    <?php
                                    $whoArray = array(
                                        'everyone' => esc_html__( 'Anyone can join', 'pwork' ),
                                        'invited'   => esc_html__( 'Only those added by the author', 'pwork' ),
                                    );
                                    foreach($whoArray as $key => $item) {
                                        if ($key == $who_can_join) {
                                            echo '<option value="' . $key . '" selected>' . $item . '</option>';
                                        } else {
                                            echo '<option value="' . $key . '">' . $item . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <label for="pwork-project-members" class="form-label"><?php echo esc_html__( 'Members', 'pwork' ); ?></label>
                                <select  multiple="" autocomplete="off" id="pwork-project-members" name="pwork-project-members" class="form-select"  autocomplete="off">
                                    <?php
                                    $blocked_roles = PworkSettings::get_option('blocked_roles', array());
                                    $users = get_users(array(
                                        'role__not_in' => $blocked_roles
                                    ));
                                    foreach ( $users as $user ) {
                                        if (in_array($user->ID, $selected_members)) {
                                            echo '<option value="' . $user->ID . '" data-default="yes" selected>' . $user->display_name . '</option>';
                                        } else {
                                            echo '<option value="' . $user->ID . '" data-default="no">' . $user->display_name . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3 col-12">
                                <form class="mb-3">
                                    <label class="form-label"><?php echo esc_html__( 'Tasks', 'pwork' ); ?></label>
                                    <div id="pwork-project-tasks-input" class="repeatable-container">
                                        <?php
                                        if (!empty($tasks)) {
                                            foreach ( (array) $tasks as $key => $entry ) {
                                                $checklist_desc = $checklist_status = $checklist_completed = $checklist_inprogress = '';
                                                if ( isset( $entry['desc'] ) ) {
                                                    $checklist_desc = esc_html( $entry['desc'] );
                                                }
                                                if ( isset( $entry['status'] ) ) {
                                                    $checklist_status = esc_html( $entry['status'] );
                                                }
                                                if ($checklist_status == 'completed') {
                                                    $checklist_completed = 'selected';
                                                } else {
                                                    $checklist_inprogress = 'selected';
                                                }
                                                ?>
                                                <div class="input-group mb-3">
                                                    <input autocomplete="off" type="text" class="form-control" id="pwork_task_desc_<?php echo esc_attr__($key); ?>" name="pwork_task_desc_<?php echo esc_attr__($key); ?>" value="<?php echo esc_attr__($checklist_desc); ?>">
                                                    <select id="pwork_task_status_<?php echo esc_attr__($key); ?>" name="v<?php echo esc_attr__($key); ?>" class="form-select" autocomplete="off">
                                                        <option value="inprogress" <?php echo esc_attr($checklist_inprogress); ?>><?php echo esc_html__( 'In progress', 'pwork' ); ?></option>
                                                        <option value="completed" <?php echo esc_attr($checklist_completed); ?>><?php echo esc_html__( 'Completed', 'pwork' ); ?></option>
                                                    </select>
                                                    <button type="button" class="btn btn-danger pwork-delete-task"><i class="bx bx-trash"></i></button>
                                                </div>
                                                <?php
                                            }
                                        } 
                                        ?>
                                    </div>
                                    <button type="button" class="btn btn-secondary pwork-add-task w-100"><?php echo esc_html__('Add New Item', 'pwork'); ?></button>
                                </form>
                                <div class="form-check form-switch"> 
                                    <input class="form-check-input" type="checkbox" id="pwork-project-checklist" autocomplete="off" <?php echo esc_attr($checklist); ?>> 
                                    <label class="form-check-label fst-italic" for="pwork-project-checklist"><?php echo esc_html__( 'Enable Tasks', 'pwork' ); ?></label> 
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pwork-card-footer justify-content-end">
                        <button id="pwork-project-submit" type="button" class="btn btn-primary" data-id="<?php echo esc_attr($edit); ?>"><?php echo esc_html($btn_text); ?></button>
                    </div>
                </div>
                <?php
                if (!isset($_GET['edit']) || empty($_GET['edit'])) {
                $project_limit = PworkSettings::get_option('projects_limit', 12);
                $project_title = esc_html__('My Projects', 'pwork');
                $project_args = array(
                    'post_status' => 'publish',
                    'post_type' => 'pworkprojects',
                    'posts_per_page'  => 99999,
                    'author__in' => get_current_user_id(),
                    'order'  => 'DESC',
                    'orderby'  => 'post_date'
                );
                if (current_user_can('administrator') || current_user_can('editor')) {
                    $project_title = esc_html__('All Projects', 'pwork');
                    unset($project_args['author__in']);
                }
                $project_query = new WP_Query($project_args);
                if ( $project_query->have_posts() ) {
                ?>
                <div class="card mt-4">
                    <div class="pwork-card-header card-header">
                        <div class="pwork-card-header-title">
                            <h3><?php echo esc_html($project_title); ?></h3>
                        </div>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table id="pwork-project-table" class="table table-striped">
                            <tbody id="pwork-project-tbody" class="table-border-bottom-0 paginated" data-perpage="<?php echo esc_attr($project_limit); ?>">
                            <?php while ( $project_query->have_posts() ) : $project_query->the_post(); ?>
                                <?php 
                                $postID = get_the_ID();
                                $title = get_the_title();
                                $edit_project_url = get_site_url() . '/' . $slug . '/?page=projects-manage&edit=' . $postID;
                                $project_url = get_site_url() . '/' . $slug . '/?page=projects&projectID=' . $postID;
                                ?>
                                <tr>
                                    <td>
                                    <a href="<?php echo esc_url($project_url); ?>" class="d-block"><strong><?php echo esc_html($title); ?></strong></a>
                                        <small class="d-block mt-1"><?php echo esc_html(get_the_date(get_option('date_format'))); ?></small>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo esc_url($edit_project_url); ?>" class="btn btn-sm btn-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Edit', 'pwork'); ?>"><span class="tf-icons bx bxs-edit-alt"></span></a>
                                            <button type="button" class="btn btn-sm btn-danger pwork-delete-project" title="<?php echo esc_attr__('Delete', 'pwork'); ?>" data-id="<?php echo esc_attr($postID); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><span class="tf-icons bx bxs-trash"></span></button>
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