<div id="my-projects-widget" class="pwork-widget col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3">
    <div class="card post-card">
        <div class="card-header pwork-widget-header bg-dark">
            <h6 class="d-flex align-items-center text-uppercase m-0 text-white"><?php echo esc_html__( 'My Projects', 'pwork' ); ?><i class="bx bx-move ms-auto text-white grabbing"></i></h6>
        </div>
        <?php
        $args = array(
            'post_status' => 'publish',
            'post_type' => 'pworkprojects',
            'posts_per_page'  => 5,
            'order'  => 'DESC',
            'orderby'  => 'post_date',
            'meta_query' => array(
                array(
                    'key' => 'pwork_project_members',
                    'value' => '"(' . get_current_user_id() . ')"',
                    'compare' => 'REGEXP'
                ),
            ),
        );
        $query = new WP_Query($args);
        if ( $query->have_posts() ) {
            echo '<div class="list-group list-group-flush">';
            while ( $query->have_posts() ) : $query->the_post();
                $postID = get_the_ID();
                $projects_url = get_site_url() . '/' . $slug . '/?page=projects';
                $project_url = get_site_url() . '/' . $slug . '/?page=projects-single&ID=' . $postID;
                $terms = get_the_terms($postID, 'pworkprojectstags');
                if ($terms) {
                    $color = get_term_meta( $terms[0]->term_id, 'pwork_tag_color', true );
                    if (empty($color)) {
                        $color = '#8592a3';
                    }
                    echo '<a href="' . esc_url($project_url) . '" class="list-group-item">' . esc_html(get_the_title()) . '<span class="badge rounded-pill ms-auto" style="background-color:' . esc_attr($color) . '">' . esc_html($terms[0]->name) . '</span></a>';
                } else {
                    echo '<a href="' . esc_url($project_url) . '" class="list-group-item">' . esc_html(get_the_title()) . '</a>';
                }
            endwhile;
            echo '</div>';
            echo '<a href="' . esc_url($projects_url) . '" class="btn btn-secondary w-100 widget-btn">' . esc_html__( 'View All', 'pwork' ) . '</a>';
            wp_reset_postdata();
        } else {
            echo '<div class="card-body mt-4"><div class="alert alert-info m-0">' . esc_html__( 'No projects found.', 'pwork' ) . '</div></div>';
        }
        ?>
    </div>
</div>