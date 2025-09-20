<?php include_once('header.php'); ?>
<?php
$slug =  PworkSettings::get_option('slug', 'pwork');
$tagID = '';
$keyword = '';
$header_title = '<span class="text-muted">' . esc_html__( 'Knowledge Base', 'pwork' ) . ' / </span>' . esc_html__('All Articles', 'pwork');
if (isset($_GET['tagID']) && !empty($_GET['tagID'])) {
    $tagID = (int) $_GET['tagID'];
    $tag = get_term_by('term_id', $tagID, 'pworkkbtags');
    $header_title = '<span class="text-muted">' . esc_html__( 'Knowledge Base', 'pwork' ) . ' / </span>' . $tag->name;
}
if (isset($_GET['s']) && !empty($_GET['s'])) {
    $keyword = esc_attr($_GET['s']);
}
?>
<?php include_once('header.php'); ?>
<div id="pwork-kb-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
        <div class="layout-page">
        <?php include_once('navbar.php'); ?>
            <div class="content-wrapper">
                <div class="container-lg flex-grow-1 container-p-y">
                    <?php 
                    $slug =  PworkSettings::get_option('slug', 'pwork');
                    $search_url = get_site_url() . '/' . $slug . '/'; 
                    ?>
                    <form action="<?php echo esc_url($search_url); ?>" method="get">
                        <div class="pwork-page-header d-flex justify-content-between align-items-center mb-4">
                            <h3 class="col fw-bold mb-4">
                            <?php echo $header_title; ?>
                            </h3>
                            <div class="col input-group input-group-merge mb-4" style="max-width:300px">
                                <input id="pwork-search-input" name="s" type="text" class="form-control form-control-lg" placeholder="<?php echo esc_attr__( 'Search by title...', 'pwork' ); ?>" autocomplete="off" value="<?php echo $keyword; ?>">
                                <input id="pwork-search-page-input" name="page" type="hidden" class="d-none" autocomplete="off" value="knowledgebase-search">
                                <div id="pwork-search-input-clear" class="input-group-text d-none" title="<?php echo esc_attr__( 'Clear', 'pwork' ); ?>">
                                    <i class="bx bx-x cursor-pointer text-danger"></i>
                                </div>
                                <button id="pwork-search-kb" type="submit" class="btn btn-lg btn-primary"><span class="tf-icons bx bx-search"></span></button>
                            </div>
                        </div>
                    </form>
                    <?php
                    $kb_args = array(
                        'post_status' => 'publish',
                        'post_type' => 'pworkkb',
                        'posts_per_page'  => 9999,
                        'order'  => 'DESC',
                        'orderby'  => 'post_date'
                    );
                    if (!empty($tagID)) {
                        $kb_args['tax_query'] = array(
                            array(
                                'taxonomy' => 'pworkkbtags',
                                'field' => 'term_id',
                                'terms' => (int) $tagID,
                            ),
                        );
                    }
                    if (!empty($keyword)) {
                        $kb_args['s'] = $keyword;
                    }
                    $kb_query = new WP_Query($kb_args);
                    $articles_found = $kb_query->found_posts;
                    ?>
                    <div class="card">
                        <div class="table-responsive text-nowrap">
                            <table id="pwork-kb-table-<?php echo esc_attr($rand); ?>" class="table table-striped">
                                <thead> 
                                    <tr> 
                                        <th class="w-100 text-end"><?php echo $articles_found . ' ' . esc_html__( 'Articles Found', 'pwork' ); ?></th>
                                    </tr> 
                                </thead>
                                <tbody id="pwork-kb-tbody-<?php echo esc_attr($rand); ?>" class="table-border-bottom-0">
                                <?php while ( $kb_query->have_posts() ) : $kb_query->the_post(); ?>
                                    <?php 
                                    $postID = get_the_ID();
                                    $title = get_the_title();
                                    $article_url = get_site_url() . '/' . $slug . '/?page=knowledgebase-single&ID=' . $postID;
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo esc_url($article_url); ?>" class="d-block"><strong class="pwork-topic-title"><?php echo esc_html($title); ?></strong></a>
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
                </div>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php include_once('footer.php'); ?>