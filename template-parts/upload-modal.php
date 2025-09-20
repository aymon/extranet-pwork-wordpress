<div class="col pwork-file-field mb-4 text-end">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pwork-upload-modal"><i class="bx bx-upload me-0 me-md-2"></i><span class="d-none d-md-inline-block"><?php echo esc_html__('Upload File', 'pwork'); ?></span></button>
    <div class="modal fade text-start" id="pwork-upload-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h3 class="modal-title"><?php echo esc_html__('Upload File', 'pwork'); ?></h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'pwork'); ?>"></button>
            </div>
            <div class="modal-body">
            <div class="row">
                <div class="col-12 mb-4">
                <label for="pwork-file-upload" class="form-label"><?php echo esc_html__('Choose File', 'pwork'); ?></label>
                <input class="form-control" type="file" id="pwork-file-upload" name="pwork-file-upload">
                </div>
                <div class="col-12 mb-4">
                    <label for="pwork-selected-tags" class="form-label"><?php echo esc_html__('Choose Tag(s)', 'pwork'); ?></label>
                    <select multiple="" class="form-select" id="pwork-selected-tags" name="pwork-selected-tags">
                    <?php
                    $selected_folders = get_terms([
                        'taxonomy' => 'pworkfolders',
                        'orderby' => 'name',
                        'order' => 'ASC',
                        'hide_empty' => false,
                    ]);
                    foreach ($selected_folders as $folder){
                        echo '<option value="' . $folder->term_id . '">' . $folder->name . '</option>';
                    }
                    ?>
                    </select>
                </div>
                <div class="col-12 mb-4">
                    <hr class="m-0 p-0">
                </div>
                <div class="col-12">
                    <label for="pwork-upload-members" class="form-label mb-0"><?php echo esc_html__( 'Accessibility', 'pwork' ); ?></label>
                    <p class="mt-0 mb-2 fst-italic"><?php echo esc_html__( 'Select which user(s) can access the file. Leave blank to upload a public file.', 'pwork' ); ?></p>
                    <select  multiple="" autocomplete="off" id="pwork-upload-members" name="pwork-upload-members" class="form-select">
                        <?php
                        $blocked_roles = PworkSettings::get_option('blocked_roles', array());
                        $users = get_users(array(
                            'role__not_in' => $blocked_roles
                        ));
                        foreach ( $users as $user ) {
                            echo '<option value="' . $user->ID . '" data-default="no">' . $user->display_name . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            </div>
            <div class="modal-footer">
            <button id="pwork-cancel-upload-btn" type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo esc_html__('Cancel', 'pwork'); ?></button>
            <button id="pwork-file-upload-btn" type="button" class="btn btn-primary"><?php echo esc_html__('Upload', 'pwork'); ?></button>
            </div>
        </div>
        </div>
    </div>
</div>