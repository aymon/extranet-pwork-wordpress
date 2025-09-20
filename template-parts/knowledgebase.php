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
                            <h2 class="col fw-bold mb-4">
                                <?php echo esc_html__('Knowledge Base', 'pwork'); ?>
                            </h2>
                            <div class="col input-group input-group-merge mb-4">
                                <input id="pwork-search-input" name="s" type="text" class="form-control form-control-lg" placeholder="<?php echo esc_attr__( 'Search by title...', 'pwork' ); ?>" autocomplete="off">
                                <input id="pwork-search-page-input" name="page" type="hidden" class="d-none" autocomplete="off" value="knowledgebase-search">
                                <div id="pwork-search-input-clear" class="input-group-text d-none" title="<?php echo esc_attr__( 'Clear', 'pwork' ); ?>">
                                    <i class="bx bx-x cursor-pointer text-danger"></i>
                                </div>
                                <button id="pwork-search-kb" type="submit" class="btn btn-lg btn-primary"><span class="tf-icons bx bx-search"></span></button>
                            </div>
                        </div>
                    </form>
                    <div class="row pb-4">
                        <?php
                        $slug =  PworkSettings::get_option('slug', 'pwork');
                        $kb_limit = PworkSettings::get_option('kb_limit', 5);
                        $tags = get_terms([
                            'taxonomy' => 'pworkkbtags',
                            'orderby' => 'name',
                            'order' => 'ASC',
                            'hide_empty' => true,
                        ]);
                        foreach ($tags as $tag){ 
                            $rand = rand();
                            $i = 0;
                            $term_url = get_site_url() . '/' . $slug . '/?page=knowledgebase-search&tagID=' . $tag->term_id;
                        ?>
                        <div class="col-12 col-md-6">
                            <div class="card mt-4">
                                <div class="pwork-card-header card-header">
                                    <div class="pwork-card-header-title w-100">
                                        <h4 class="d-flex align-items-center w-100"><?php echo esc_html($tag->name); ?><span class="badge bg-dark fs-6 ms-auto"><?php echo esc_html($tag->count); ?></span></h4>
                                    </div>
                                </div>
                                <?php
                                $kb_args = array(
                                    'post_status' => 'publish',
                                    'post_type' => 'pworkkb',
                                    'posts_per_page'  => $kb_limit,
                                    'order'  => 'DESC',
                                    'orderby'  => 'post_date',
                                    'tax_query' => array(
                                        array(
                                            'taxonomy' => 'pworkkbtags',
                                            'field' => 'term_id',
                                            'terms' => (int) $tag->term_id,
                                        ),
                                    )
                                );
                                $kb_query = new WP_Query($kb_args);
                                ?>
                                <div class="table-responsive text-nowrap">
                                    <table id="pwork-kb-table-<?php echo esc_attr($rand); ?>" class="table table-striped">
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
                                        $i ++;
                                        endwhile; 
                                        wp_reset_postdata();
                                        if ((int) $kb_limit > $i) {
                                            $row_left = (int) $kb_limit - $i;
                                            echo str_repeat('<tr><td><strong style="opacity:0">-</strong></td></tr>', (int) $row_left);
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="pwork-card-footer justify-content-start">
                                    <a href="<?php echo esc_url($term_url); ?>" class="btn btn-primary w-100"><?php echo esc_html__('View All', 'pwork'); ?></a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        <?php
                        $notag_args = array(
                            'post_status' => 'publish',
                            'post_type' => 'pworkkb',
                            'posts_per_page'  => $kb_limit,
                            'order'  => 'DESC',
                            'orderby'  => 'post_date',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'pworkkbtags',
                                    'field'    => 'id',
                                    'operator' => 'NOT EXISTS',
                                ),
                            )
                        );
                        $notag_query = new WP_Query($notag_args);
                        if ( $notag_query->have_posts() ) { 
                            $notag_i = 0;
                            $notag_count = $notag_query->found_posts;
                        ?>
                        <div class="col-12 col-md-6">
                            <div class="card mt-4">
                                <div class="pwork-card-header card-header">
                                    <div class="pwork-card-header-title w-100">
                                    <h4 class="d-flex align-items-center w-100"><?php echo esc_html__('Uncategorized', 'pwork'); ?><span class="badge bg-dark fs-6 ms-auto"><?php echo esc_html($notag_count); ?></span></h4>
                                    </div>
                                </div>
                                <div class="table-responsive text-nowrap">
                                    <table id="pwork-kb-table-uncategorized" class="table table-striped">
                                        <tbody id="pwork-kb-tbody-uncategorized" class="table-border-bottom-0">
                                        <?php while ( $notag_query->have_posts() ) : $notag_query->the_post(); ?>
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
                                        $notag_i ++;
                                        endwhile; 
                                        wp_reset_postdata();
                                        if ((int) $kb_limit > $notag_i) {
                                            $row_left = (int) $kb_limit - $notag_i;
                                            echo str_repeat('<tr><td><strong style="opacity:0">-</strong></td></tr>', (int) $row_left);
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php $noterm_url = get_site_url() . '/' . $slug . '/?page=knowledgebase-search&tagID=0'; ?>
                                <div class="pwork-card-footer justify-content-start">
                                    <a href="<?php echo esc_url($noterm_url); ?>" class="btn btn-primary w-100"><?php echo esc_html__('View All', 'pwork'); ?></a>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php include_once('footer.php'); ?>