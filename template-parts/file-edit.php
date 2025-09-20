<?php include_once('header.php'); ?>
<?php 
$slug = PworkSettings::get_option('slug', 'pwork'); 
$edit = '';
$selected_tags = array();
$selected_members = array();
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $edit = $_GET['id'];
}
if (empty($edit)) {
    echo esc_html__('File ID required.', 'pwork');
    exit();
}
$post_id = $edit;
$post = get_post($post_id);
if (empty($post)) {
    echo esc_html__('No file found.', 'pwork');
    exit();
}
$title = get_the_title($post_id);
$selected_members = get_post_meta($post_id, 'pwork_file_members', true );
$gettags = get_the_terms($post_id, 'pworkfolders');
if ($gettags && !empty($gettags)) {
    foreach ($gettags as $gettag){
        array_push($selected_tags, $gettag->term_id);
    }
} 
?>
<div id="pwork-edit-file-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-sm flex-grow-1 container-p-y">
                <div class="card">
                    <div class="pwork-card-header card-header">
                        <div class="pwork-card-header-title">
                            <h3><?php echo esc_html__('Edit File', 'pwork'); ?></h3>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <div class="row">
                            <div class="col-12 mb-4">
                                <label for="pwork-file-name-edit" class="form-label"><?php echo esc_html__('File Name', 'pwork'); ?></label>
                                <input autocomplete="off" class="form-control" type="text" name="pwork-file-name-edit" id="pwork-file-name-edit" value="<?php echo esc_attr($title); ?>">
                            </div>
                            <div class="col-12 mb-4">
                                <label for="pwork-selected-tags-edit" class="form-label"><?php echo esc_html__('Choose Tag(s)', 'pwork'); ?></label>
                                <select multiple="" class="form-select" id="pwork-selected-tags-edit" name="pwork-selected-tags-edit" autocomplete="off">
                                <?php
                                $selected_folders = get_terms([
                                    'taxonomy' => 'pworkfolders',
                                    'orderby' => 'name',
                                    'order' => 'ASC',
                                    'hide_empty' => false,
                                ]);
                                foreach ($selected_folders as $folder){
                                    if (in_array($folder->term_id, $selected_tags)) {
                                        echo '<option value="' . $folder->term_id . '" selected>' . $folder->name . '</option>';
                                    } else {
                                        echo '<option value="' . $folder->term_id . '">' . $folder->name . '</option>';
                                    }
                                }
                                ?>
                                </select>
                            </div>
                            <div class="col-12 mb-4">
                                <hr class="m-0 p-0">
                            </div>
                            <div class="col-12">
                                <label for="pwork-upload-members-edit" class="form-label mb-0"><?php echo esc_html__( 'Accessibility', 'pwork' ); ?></label>
                                <p class="mt-0 mb-2 fst-italic"><?php echo esc_html__( 'Select which user(s) can access the file. Leave blank to upload a public file.', 'pwork' ); ?></p>
                                <select multiple="" autocomplete="off" id="pwork-upload-members-edit" name="pwork-upload-members-edit" class="form-select"  autocomplete="off">
                                    <?php
                                    $blocked_roles = PworkSettings::get_option('blocked_roles', array());
                                    $users = get_users(array(
                                        'role__not_in' => $blocked_roles
                                    ));
                                    if (!empty($selected_members) && is_array($selected_members)) {
                                        foreach ( $users as $user ) {
                                            if (in_array($user->ID, $selected_members)) {
                                                echo '<option value="' . $user->ID . '" data-default="yes" selected>' . $user->display_name . '</option>';
                                            } else {
                                                echo '<option value="' . $user->ID . '" data-default="no">' . $user->display_name . '</option>';
                                            }
                                        }
                                    } else {
                                        foreach ( $users as $user ) {
                                            echo '<option value="' . $user->ID . '" data-default="no">' . $user->display_name . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="pwork-card-footer justify-content-end"> 
                        <button id="pwork-file-edit" type="button" class="btn btn-primary" data-id="<?php echo esc_attr($post_id); ?>"><?php echo esc_html__('Save Changes', 'pwork'); ?></button> 
                    </div>
                </div>
            </div>
            <div class="content-backdrop fade"></div>
        </div>
    </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php include_once('footer.php'); ?>