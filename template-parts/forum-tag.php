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
<div id="pwork-forum-tag-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-lg flex-grow-1 container-p-y">
                <div class="input-group input-group-merge mb-3">
                    <input id="pwork-topic-search-input" type="text" class="form-control form-control-lg" placeholder="<?php echo esc_attr__( 'Search by topic title...', 'pwork' ); ?>" autocomplete="off">
                    <div id="pwork-topic-search-input-clear" class="input-group-text d-none" title="<?php echo esc_attr__( 'Clear', 'pwork' ); ?>">
                        <i class="bx bx-x cursor-pointer text-danger"></i>
                    </div>
                    <select id="pwork-topic-search-tag" class="form-select" autocomplete="off" disabled>
                        <?php
                        $tag = get_term_by('term_id', $tagID, 'pworkforumtags');
                        $topics_url = get_site_url() . '/' . $slug . '/?page=forum';
                        echo '<option value="' . $tagID . '" selected>' . $tag->name . '</option>';
                        ?>
                    </select>
                    <button id="pwork-topic-search" type="button" class="btn btn-lg btn-primary"><span class="tf-icons bx bx-search"></span></button>
                </div>
                <h3 class="fw-bold py-3">
                    <span class="text-muted fw-light"><?php echo esc_html__('Forum', 'pwork'); ?> /</span> <span class="text-muted fw-light"><a class="text-muted fw-light" href="<?php echo esc_url($topics_url); ?>"><?php echo esc_html__('Topics', 'pwork'); ?></a> /</span> <?php echo esc_html($tag->name); ?>
                </h3>
                <?php
                $forum_limit = PworkSettings::get_option('forum_limit', 10);
                $topic_args = array(
                    'post_status' => 'publish',
                    'post_type' => 'pworkforum',
                    'posts_per_page'  => 99999,
                    'order'  => 'DESC',
                    'orderby'  => 'post_date',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'pworkforumtags',
                            'field' => 'term_id',
                            'terms' => $tagID,
                        ),
                    )
                );
                $topic_query = new WP_Query($topic_args);
                if ( $topic_query->have_posts() ) {
                ?>
                <div class="card">
                    <div class="table-responsive text-nowrap">
                        <table id="pwork-topics-table" class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="w-100"><?php echo esc_html__('Topic', 'pwork'); ?></th>
                                    <th class="text-center"><?php echo esc_html__('Replies', 'pwork'); ?></th>
                                    <th class="d-none d-md-table-cell text-center"><?php echo esc_html__('Activity', 'pwork'); ?></th>
                                    <th class="d-none d-md-table-cell"></th>
                                </tr>
                            </thead>
                            <tbody id="pwork-topics-tbody" class="table-border-bottom-0 paginated" data-perpage="<?php echo esc_attr($forum_limit); ?>">
                            <?php while ( $topic_query->have_posts() ) : $topic_query->the_post(); ?>
                            <?php 
                            $postID = get_the_ID();
                            $authorID = (int) get_post_field('post_author', $postID);
                            $topic_url = get_site_url() . '/' . $slug . '/?page=forum&topicID=' . $postID;
                            $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $authorID;
                            $last_activity = get_post_meta($postID, 'pwork_last_activity', true );
                            $terms = get_the_terms($postID, 'pworkforumtags'); 
                            $badges = '';
                            if ($terms) {
                                foreach($terms as $term) {
                                    $color = get_term_meta( $term->term_id, 'pwork_tag_color', true );
                                    if (empty($color )) {
                                        $color = '#8592a3';
                                    }
                                    $badges = '<span class="badge me-1 mt-1" style="background-color:' . esc_attr($color) . '"><a href="' . esc_url(get_site_url() . '/' . $slug . '/?page=forum-tag&tagID=' . $term->term_id) . '">' . esc_html($term->name) . '</a></span>' . $badges;
                                }
                            }
                            ?>
                                <tr>
                                    <td class="w-100">
                                        <a href="<?php echo esc_url($topic_url); ?>" class="d-block">
                                            <strong class="pwork-topic-title text-truncate">
                                                <?php the_title(); ?>
                                                <?php if (!comments_open($postID)) {
                                                     echo '<span>('  . esc_html__('Closed', 'pwork') .')</span>';
                                                } ?>
                                            </strong>
                                        </a>
                                       <?php echo wp_kses_post($badges); ?> 
                                        <div class="d-block d-md-none">
                                            <small class="d-block mt-2 mb-1"><?php echo esc_html__('Posted by', 'pwork') . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a> ' . esc_html__('on', 'pwork') . ' ' . esc_html(get_the_date(get_option('date_format'))); ?></small>
                                            <small class="d-block">
                                            <?php
                                                if ($last_activity && !empty($last_activity)) { 
                                                    echo esc_html__('Last Activity:', 'pwork') . ' ' . human_time_diff( $last_activity, current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'pwork' );
                                                } else {
                                                    echo esc_html__('Last Activity:', 'pwork') . ' ' . human_time_diff( get_the_date('U'), current_time( 'U' ) ) . ' ' . esc_html__( 'ago', 'pwork' );
                                                }
                                                ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                    <?php echo esc_html(get_comment_count($postID)['approved']); ?>
                                    </td>
                                    <td class="d-none d-md-table-cell text-center">
                                        <?php
                                        if ($last_activity && !empty($last_activity)) { 
                                            echo human_time_diff( $last_activity, current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'pwork' );
                                        } else {
                                            echo human_time_diff( get_the_date('U'), current_time( 'U' ) ) . ' ' . esc_html__( 'ago', 'pwork' );
                                        }
                                        ?>
                                    </td>
                                    <td class="d-none d-md-table-cell text-end">
                                        <small><?php echo esc_html__('Posted by', 'pwork') . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a><br>' . esc_html__('on', 'pwork') . ' ' . esc_html(get_the_date(get_option('date_format'))); ?></small>
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
                echo '<div class="alert alert-warning m-0">' . esc_html__( 'Nothing found.', 'pwork' ) . '</div>';
                } ?>
            </div>
            <div class="content-backdrop fade"></div>
        </div>
    </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php include_once('footer.php'); ?>