<?php Pwork::remove_notification('announcements'); ?>
<?php include_once('header.php'); ?>
<?php
$tagID = '';
if (isset($_GET['tagID']) && !empty($_GET['tagID'])) {
    $tagID = (int) $_GET['tagID'];
}
?>
<div id="pwork-anns-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-xl flex-grow-1 container-p-y">
              <div class="pwork-page-header d-flex justify-content-between align-items-center flex-wrap w-100 mb-4">
                  <h2 class="col-12 col-sm-5 fw-bold mb-4">
                  <?php echo esc_html__('News & Announcements', 'pwork'); ?>
                  </h2>
                  <div class="col-12 col-sm-7">
                      <div class="input-group input-group-merge mb-4">
                          <input id="pwork-ann-search-input" type="text" class="form-control form-control-lg" placeholder="<?php echo esc_attr__( 'Search by title...', 'pwork' ); ?>" autocomplete="off">
                          <div id="pwork-ann-search-input-clear" class="input-group-text d-none" title="<?php echo esc_attr__( 'Clear', 'pwork' ); ?>">
                              <i class="bx bx-x cursor-pointer text-danger"></i>
                          </div>
                          <select id="pwork-ann-search-tag" class="form-select" autocomplete="off">
                              <option value="" selected><?php echo esc_html__('All Tags', 'pwork'); ?></option>
                              <?php
                              $tags = get_terms([
                                  'taxonomy' => 'pworkannstags',
                                  'orderby' => 'name',
                                  'order' => 'ASC',
                                  'hide_empty' => true,
                              ]);
                              foreach ($tags as $tag){
                                  if (!empty($tagID) && $tag->term_id == $tagID) {
                                      echo '<option value="' . $tag->term_id . '" selected>' . $tag->name . ' (' . $tag->count . ')</option>';
                                  } else {
                                      echo '<option value="' . $tag->term_id . '">' . $tag->name . ' (' . $tag->count . ')</option>';
                                  }
                              }
                              ?>
                          </select>
                          <button id="pwork-ann-search" type="button" class="btn btn-lg btn-primary"><span class="tf-icons bx bx-search"></span></button>
                      </div>
                  </div>
              </div>
              <div id="pwork-anns-wrap" class="row pwork-hide-row">
                <div class="grid-sizer col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3"></div>
                <?php
                $slug =  PworkSettings::get_option('slug', 'pwork');
                $limit = PworkSettings::get_option('anns_limit', 12);
                $args = array(
                    'post_status' => 'publish',
                    'post_type' => 'pworkanns',
                    'posts_per_page'  => $limit,
                    'order'  => 'DESC',
                    'orderby'  => 'post_date'
                );
                if (isset($tagID) && !empty($tagID)) {
                  $args['tax_query'] = array(
                      array(
                          'taxonomy' => 'pworkannstags',
                          'field' => 'term_id',
                          'terms' => (int) $tagID,
                      ),
                  );
                }
                $query = new WP_Query($args);
                $visible_posts = $query->post_count;
                if ( $query->have_posts() ) {
                  while ( $query->have_posts() ) : $query->the_post();
                  $postID = get_the_ID();
                  $authorID = (int) get_post_field('post_author', $postID);
                  $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $authorID;
                  $ann_url = get_site_url() . '/' . $slug . '/?page=announcements-single&ID=' . $postID;
                  $terms = get_the_terms( $postID, 'pworkannstags' );
                  $style = get_post_meta( $postID, 'pwork_announcement_card', true );
                  $text_color = '';
                  $bg_color = '';
                  if ($style == 'primary') {
                    $text_color = 'text-white';
                    $bg_color = 'bg-primary';
                  } else if ($style == 'secondary') {
                    $text_color = 'text-white';
                    $bg_color = 'bg-secondary';
                  } else if ($style == 'info') {
                    $text_color = 'text-white';
                    $bg_color = 'bg-info';
                  } else if ($style == 'danger') {
                    $text_color = 'text-white';
                    $bg_color = 'bg-danger';
                  } else if ($style == 'warning') {
                    $text_color = 'text-dark';
                    $bg_color = 'bg-warning';
                  } else if ($style == 'success') {
                    $text_color = 'text-white';
                    $bg_color = 'bg-success';
                  }
                  $content = get_post_meta( $postID, 'pwork_announcement_content', true );
                  ?>
                  <div class="col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3">
                    <div class="card post-card mb-4 <?php echo esc_attr($text_color . ' ' . $bg_color); ?>">
                      <?php 
                      if (has_post_thumbnail()) {
                          $thumbnail_id = get_post_thumbnail_id();
                          $thumbnail_src = wp_get_attachment_image_src($thumbnail_id, 'large', true);
                          echo '<a href="' . esc_url($ann_url) . '"><img class="card-img-top" src="' . esc_url($thumbnail_src[0]) . '"></a>';
                      } ?>
                      <div class="card-body">
                        <span class="card-date <?php echo esc_attr($text_color); ?>"><a href="<?php echo esc_url($ann_url); ?>" style="color:inherit"><?php echo esc_html(get_the_date(get_option('date_format'))); ?></a></span>
                        <h4 class="card-title <?php echo esc_attr($text_color); ?>"><a href="<?php echo esc_url($ann_url); ?>" style="color:inherit"><?php the_title(); ?></a></h4>
                        <p class="card-text"><?php the_excerpt(); ?></p>
                        <?php 
                        if (!empty($terms) && is_array($terms)) {
                          echo '<div class="mt-3">';
                          foreach($terms as $term) {
                            echo '<span class="badge bg-dark me-1 mt-1"><a href="' . esc_url(get_site_url() . '/' . $slug . '/?page=announcements&tagID=' . $term->term_id) . '">' . esc_html($term->name) . '</a></span>';
                          }
                          echo '</div>';
                        } 
                        ?>
                      </div>
                      <div class="pwork-card-footer justify-content-start">
                      <div class="d-flex align-items-center"><a href="<?php echo esc_url($user_profile_url); ?>"><?php echo get_avatar($authorID, 80); ?></a><strong class="ms-2"><?php echo esc_html(get_the_author_meta('display_name')); ?></strong></div>
                      </div>
                    </div>
                  </div>
                  <?php
                  endwhile;
                  if ($limit == $visible_posts) { ?>
                    <div class="col-12 mt-2">
                        <button id="pwork-load-more-anns" type="button" class="btn btn-lg btn-primary w-100" data-offset="<?php echo esc_attr($limit); ?>"><?php echo esc_html__( 'LOAD MORE', 'pwork' ); ?></button>
                    </div>
                    <?php }
                    wp_reset_postdata();
                } else {
                  echo '<div class="alert alert-warning">' . esc_html__('Nothing found.', 'pwork') . '</div>';
                } ?>
                </div>
              </div>
            <div class="content-backdrop fade"></div>
        </div>
    </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php include_once('footer.php'); ?>