<?php include_once('header.php'); ?>
<?php 
$slug = PworkSettings::get_option('slug', 'pwork');
$tagID = '';
if (isset($_GET['tagID']) && !empty($_GET['tagID'])) {
    $tagID = (int) $_GET['tagID'];
} else {
    wp_die(esc_html__('Tag ID is required', 'pwork'));
}
?>
<div id="pwork-file-tag-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-lg flex-grow-1 container-p-y">
                <div class="input-group input-group-merge mb-3">
                    <input id="pwork-file-search-input" type="text" class="form-control form-control-lg" placeholder="<?php echo esc_attr__( 'Search by title...', 'pwork' ); ?>" autocomplete="off">
                    <div id="pwork-file-search-input-clear" class="input-group-text d-none" title="<?php echo esc_attr__( 'Clear', 'pwork' ); ?>">
                        <i class="bx bx-x cursor-pointer text-danger"></i>
                    </div>
                    <select id="pwork-file-search-folder" class="form-select" autocomplete="off" disabled>
                        <?php
                        $tag = get_term_by('term_id', $tagID, 'pworkfolders');
                        $files_url = get_site_url() . '/' . $slug . '/?page=files';
                        echo '<option value="' . $tagID . '" selected>' . $tag->name . '</option>';
                        ?>
                    </select>
                    <button id="pwork-file-search" type="button" class="btn btn-lg btn-primary"><span class="tf-icons bx bx-search"></span></button>
                </div>
                <h3 class="fw-bold py-3">
                    <span class="text-muted fw-light"><?php echo esc_html__('File Library', 'pwork'); ?> /</span> <span class="text-muted fw-light"><a class="text-muted fw-light" href="<?php echo esc_url($files_url); ?>"><?php echo esc_html__('All Files', 'pwork'); ?></a> /</span> <?php echo esc_html($tag->name); ?>
                </h3>
                <?php
                $file_limit = PworkSettings::get_option('file_limit', 10);
                $file_args = array(
                    'post_status' => 'publish',
                    'post_type' => 'pworkfiles',
                    'posts_per_page'  => 99999,
                    'order'  => 'DESC',
                    'orderby'  => 'post_date',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'pworkfolders',
                            'field' => 'term_id',
                            'terms' => $tagID,
                        ),
                    )
                );
                $file_query = new WP_Query($file_args);
                if ( $file_query->have_posts() ) {
                ?>
                <div class="card">
                    <div class="table-responsive text-nowrap">
                        <table id="pwork-files-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th><div class="d-none d-md-block"><?php echo esc_html__('File', 'pwork'); ?></div><div class="d-block d-md-none"><?php echo esc_html__('Files', 'pwork'); ?></div></th>
                                    <th class="d-none d-md-table-cell"><?php echo esc_html__('Info', 'pwork'); ?></th>
                                    <th class="d-none d-md-table-cell"><?php echo esc_html__('File Size', 'pwork'); ?></th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody id="pwork-files-tbody" class="table-border-bottom-0 paginated" data-perpage="<?php echo esc_attr($file_limit); ?>">
                            <?php while ( $file_query->have_posts() ) : $file_query->the_post(); ?>
                            <?php 
                            $postID = get_the_ID();
                            $file_url = get_post_meta( $postID, 'pwork_file_url', true ); 
                            $filename = basename($file_url);
                            $authorID = (int) get_post_field('post_author', $postID);
                            $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $authorID;
                            $path = str_replace( site_url('/'), ABSPATH, esc_url( $file_url) );
                            $terms = get_the_terms($postID, 'pworkfolders'); 
                            $badges = '';
                            if ($terms) {
                                foreach($terms as $term) {
                                    $badges = '<span class="badge bg-secondary me-1 mt-1"><a href="' . esc_url(get_site_url() . '/' . $slug . '/?page=files-tag&tagID=' . $term->term_id) . '">' . esc_html($term->name) . '</a></span>' . $badges;
                                }
                            }
                            ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url($file_url); ?>" target="_blank"><strong class="pwork-file-title text-truncate"><?php echo esc_html($filename); ?></strong></a>
                                       <?php echo wp_kses_post($badges); ?> 
                                        <div class="d-block d-md-none">
                                            <small class="d-block mt-2 mb-1"><?php echo esc_html__('Uploaded by', 'pwork') . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a> ' . esc_html__('on', 'pwork') . ' ' . esc_html(get_the_date(get_option('date_format'))); ?></small>
                                            <small class="d-block"><?php echo esc_html__('File Size:', 'pwork'); ?> <?php Pwork::human_filesize(wp_filesize( $path )); ?></small>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                    <small><?php echo esc_html__('Uploaded by', 'pwork') . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a><br>' . esc_html__('on', 'pwork') . ' ' . esc_html(get_the_date(get_option('date_format'))); ?></small>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?php Pwork::human_filesize(wp_filesize( $path )); ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group"> 
                                            <button type="button" class="btn btn-sm btn-secondary pwork-copy-url" title="<?php echo esc_attr__('Copy url', 'pwork'); ?>" data-url="<?php echo esc_url($file_url); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><span class="tf-icons bx bxs-copy"></span></button>
                                            <a href="<?php echo esc_url($file_url); ?>" class="btn btn-sm btn-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Download File', 'pwork'); ?>" download><span class="tf-icons bx bxs-download"></span></a>
                                            <?php if (current_user_can('administrator') || current_user_can('editor') || ($authorID === get_current_user_id())) { ?>
                                            <button type="button" class="btn btn-sm btn-danger pwork-delete-file" title="<?php echo esc_attr__('Delete file', 'pwork'); ?>" data-id="<?php echo esc_attr($postID); ?>" data-bs-toggle="tooltip" data-bs-placement="top"><span class="tf-icons bx bxs-trash"></span></button>
                                            <?php } ?>
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
                <?php 
                
            } else {
                echo '<div class="alert alert-warning mt-4 mb-0">' . esc_html__( 'Nothing found.', 'pwork' ) . '</div>';
            } ?>
            </div>
            <div class="content-backdrop fade"></div>
        </div>
    </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php include_once('footer.php'); ?>