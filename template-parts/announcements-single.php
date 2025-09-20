<?php include_once('header.php'); ?>
<?php
$postID = '';
if (isset($_GET['ID']) && !empty($_GET['ID'])) {
    $postID = (int) $_GET['ID'];
} else {
    wp_die(esc_html__('The ID is required', 'pwork'));
}
$currentuser = wp_get_current_user();
$slug =  PworkSettings::get_option('slug', 'pwork');
$authorID = (int) get_post_field('post_author', $postID);
$user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $authorID;
$content = get_post_meta( $postID, 'pwork_announcement_content', true );
?>
<div id="pwork-single-ann-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-sm container-p-y">
                <div class="card">
                    <?php 
                      if (has_post_thumbnail($postID)) {
                          $thumbnail_id = get_post_thumbnail_id($postID);
                          $thumbnail_src = wp_get_attachment_image_src($thumbnail_id, 'large', true);
                          echo '<img class="card-img-top" src="' . esc_url($thumbnail_src[0]) . '">';
                      } ?>
                    <div class="card-body single-ann-body">
                        <div class="row">
                            <div class="col-12">
                            <h1><?php echo get_the_title($postID); ?></h1>
                            <?php
                            echo wp_kses_post(wpautop($content));

                            if (comments_open($postID)) {
                                echo '<hr class="mt-4 mb-4"><h4>' . esc_html__( 'Add Comment', 'pwork' ) . '</h4>';
                                echo '<div id="pwork-ann-comment"></div>';
                                echo '<button type="button" class="btn btn-primary w-100 add-ann-comment mt-4" data-id="' . esc_attr($postID) . '">' . esc_html__( 'Add Comment', 'pwork' ) . '</button>';
                                if (get_comment_count($postID)['approved'] >= 1) {
                                    $args = array(
                                        'post_id' => $postID,
                                        'status'  => 'approve',
                                        'parent' => 0
                                    );
                                    $comments = get_comments( $args );
                                    if ($comments) {
                                        echo '<div class="pwork-project-comments-inner">';
                                        echo '<hr class="mt-4 mb-4"><h4 class="mb-4">' . esc_html__( 'Comments', 'pwork' ) . ' (' . get_comment_count($postID)['approved'] . ')</h4>';
                                        foreach ( $comments as $comment ) {
                                            $comment_id = (int) $comment->comment_ID;
                                            $parent = $comment->comment_parent;
                                            $author_id = $comment->user_id;
                                            $author_name = get_the_author_meta('display_name', $author_id);
                                            $author_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $author_id;
                                            $author_content = $comment->comment_content;
                            
                                            echo '<div class="pwork-message-bubble-wrap"><a href="' . esc_url($author_url) . '">' . get_avatar($author_id, 100) . '</a><div class="pwork-message-bubble shadow">' . wp_kses_post($author_content) . '<div class="pwork-message-bubble-info">' . human_time_diff( get_comment_date('U', $comment_id) , current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago.', 'pwork' ) . '</div></div></div>';
                                        }
                                        echo '</div>';
                                    }
                                }
                            }
                            ?>
                            </div>
                        </div>
                    </div>
                    <div class="pwork-card-footer justify-content-start">
                      <div class="fw-bold"><?php echo esc_html__( 'Published on', 'pwork' ) . ' ' . get_the_date(get_option('date_format') . ' ' . get_option('time_format'), $postID) . ' ' . esc_html__( 'by', 'pwork' ) . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a>' ?></div>
                    </div>
                </div>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</div>
<?php include_once('footer.php'); ?>