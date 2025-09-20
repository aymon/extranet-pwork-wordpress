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
$content = get_post_meta( $postID, 'pwork_article_content', true );
?>
<div id="pwork-single-kb-page" class="layout-wrapper layout-content-navbar">
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
                            ?>
                            </div>
                        </div>
                    </div>
                    <div class="pwork-card-footer justify-content-start">
                      <div class="fw-bold"><?php echo esc_html__( 'Last modified on', 'pwork' ) . ' ' . get_the_modified_date(get_option('date_format') . ' ' . get_option('time_format'), $postID) . ' ' . esc_html__( 'by', 'pwork' ) . ' <a href="' . esc_url($user_profile_url) . '">' . esc_html(get_the_author_meta('display_name',$authorID)) . '</a>' ?></div>
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