<div id="latest-uploads-widget" class="pwork-widget col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3">
    <div class="card post-card">
        <div class="card-header pwork-widget-header bg-dark">
            <h6 class="d-flex align-items-center text-uppercase m-0 text-white"><?php echo esc_html__( 'Recently Shared Files', 'pwork' ); ?><i class="bx bx-move ms-auto text-white grabbing"></i></h6>
        </div>
        <?php
        $file_limit = PworkSettings::get_option('file_limit', 10);
        $file_args = array(
            'post_status' => 'publish',
            'post_type' => 'pworkfiles',
            'posts_per_page'  => 5,
            'order'  => 'DESC',
            'orderby'  => 'post_date'
        );
        $file_query = new WP_Query($file_args);
        if ( $file_query->have_posts() ) {
        ?>
            <div class="list-group list-group-flush">
                <?php while ( $file_query->have_posts() ) : $file_query->the_post(); ?>
                <?php 
                $postID = get_the_ID();
                $files_url = get_site_url() . '/' . $slug . '/?page=files';
                $file_url = get_post_meta( $postID, 'pwork_file_url', true ); 
                $filename = basename($file_url);
                $path = str_replace( site_url('/'), ABSPATH, esc_url( $file_url));
                ?>
                <div class="list-group-item">
                    <a href="<?php echo esc_url($file_url); ?>" target="_blank"><?php echo esc_html($filename); ?> (<?php Pwork::human_filesize(wp_filesize( $path )); ?>)</a>
                    <a href="<?php echo esc_url($file_url); ?>" class="btn btn-sm btn-dark ms-auto" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo esc_attr__('Download File', 'pwork'); ?>" download><span class="bx bxs-download"></span></a>
                </div>
                <?php 
                endwhile; 
                wp_reset_postdata();
                ?>
            </div>
            <?php echo '<a href="' . esc_url($files_url) . '" class="btn btn-secondary w-100 widget-btn">' . esc_html__( 'View All', 'pwork' ) . '</a>'; ?>
        <?php 
        } else {
            echo '<div class="card-body mt-4"><div class="alert alert-info m-0">' . esc_html__( 'Nothing found.', 'pwork' ) . '</div></div>';
        } ?>
    </div>
</div>