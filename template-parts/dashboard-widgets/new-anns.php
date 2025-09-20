<div id="new-anns-widget" class="pwork-widget col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3">
    <div class="card post-card">
        <div class="card-header pwork-widget-header bg-dark">
            <h6 class="d-flex align-items-center text-uppercase m-0 text-white"><?php echo esc_html__( 'Recent Announcements', 'pwork' ); ?><i class="bx bx-move ms-auto text-white grabbing"></i></h6>
        </div>
        <?php
        $args = array(
            'post_status' => 'publish',
            'post_type' => 'pworkanns',
            'posts_per_page'  => 5,
            'order'  => 'DESC',
            'orderby'  => 'post_date'
        );
        $query = new WP_Query($args);
        if ( $query->have_posts() ) {
            echo '<div class="list-group list-group-flush">';
            while ( $query->have_posts() ) : $query->the_post();
                $postID = get_the_ID();
                $anns_url = get_site_url() . '/' . $slug . '/?page=announcements';
                $ann_url = get_site_url() . '/' . $slug . '/?page=announcements-single&ID=' . $postID;
                $style = get_post_meta( $postID, 'pwork_announcement_card', true );
                $icon_color = '';
                if ($style == 'primary') {
                    $icon_color = 'text-primary';
                  } else if ($style == 'secondary') {
                    $icon_color = 'text-secondary';
                  } else if ($style == 'info') {
                    $icon_color = 'text-info';
                  } else if ($style == 'danger') {
                    $icon_color = 'text-danger';
                  } else if ($style == 'warning') {
                    $icon_color = 'text-warning';
                  } else if ($style == 'success') {
                    $icon_color = 'text-success';
                  } else if ($style == 'default') {
                    $icon_color = 'text-muted';
                  }

                echo '<a href="' . esc_url($ann_url) . '"  class="list-group-item"><i class="bx bxs-circle me-2 ' . esc_attr($icon_color) . '"></i>' . esc_html(get_the_title()) . '</a>';
            endwhile;
            echo '</div>';
            echo '<a href="' . esc_url($anns_url) . '" class="btn btn-secondary w-100 widget-btn">' . esc_html__( 'View All', 'pwork' ) . '</a>';
            wp_reset_postdata();
        } else {
          echo '<div class="card-body mt-4"><div class="alert alert-info m-0">' . esc_html__( 'No announcements found.', 'pwork' ) . '</div></div>';
        }
        ?>
    </div>
</div>