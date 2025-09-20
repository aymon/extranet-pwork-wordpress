<?php include_once('header.php'); ?>
<?php Pwork::remove_notification('topics'); ?>
<?php $slug = PworkSettings::get_option('slug', 'pwork'); ?>
<div id="pwork-forum-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-lg flex-grow-1 container-p-y">
                <?php 
                if(isset($_GET['topicID']) && !empty($_GET['topicID'])) {
                    $topic_id = (int) $_GET['topicID'];
                    $user_content = get_post_meta( $topic_id, 'pwork_forum_content', true );
                    $userID = (int) get_post_field('post_author', $topic_id);
                    $user_name = get_the_author_meta('display_name', $userID);
                    $user_profile_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $userID;
                    $edit_topic_url = get_site_url() . '/' . $slug . '/?page=forum-manage&edit=' . $topic_id;
                    $terms = get_the_terms($topic_id, 'pworkforumtags'); 
                    if ($terms && !empty($terms)) {
                        $terms = $terms[0];
                    } else {
                        $terms = '';
                    }
                ?>
                <div class="d-flex justify-content-between align-items-center flex-wrap w-100">
                    <h4 class="fw-bold mb-4">
                        <span class="text-muted fw-light"><?php echo esc_html__('Forum', 'pwork'); ?> /</span> <span class="text-muted fw-light"><a class="text-muted fw-light" href="<?php echo esc_url(get_site_url() . '/' . $slug . '/?page=forum'); ?>"><?php echo esc_html__('Topics', 'pwork'); ?></a> /</span> <?php if (!empty($terms)) { ?><span class="text-muted fw-light"><a class="text-muted fw-light" href="<?php echo esc_url(get_site_url() . '/' . $slug . '/?page=forum-tag&tagID=' . $terms->term_id); ?>"><?php echo esc_html__($terms->name); ?></a> /</span><?php } ?> <?php echo esc_html(get_the_title($topic_id)); ?>
                    </h4>  
                    <?php if ( comments_open($topic_id) ) { ?>
                    <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#pwork-add-comment-modal"><i class="tf-icons bx bxs-comment-add me-0 me-md-2"></i><span class="d-none d-md-inline-block"><?php echo esc_html__('Add Comment', 'pwork'); ?></span></button>
                    <div class="modal fade text-start" id="pwork-add-comment-modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                            <h3 class="modal-title"><?php echo esc_html__('Add Comment', 'pwork'); ?></h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'pwork'); ?>"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div id="pwork-comment-content"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                            <button id="pwork-cancel-comment-btn" type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo esc_html__('Cancel', 'pwork'); ?></button>
                            <button id="pwork-add-comment-btn" type="button" class="btn btn-primary" data-id="<?php echo esc_attr($topic_id); ?>"><?php echo esc_html__('Add Comment', 'pwork'); ?></button>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="modal fade text-start" id="pwork-add-reply-modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                            <h3 class="modal-title"><?php echo esc_html__('Add Reply', 'pwork'); ?></h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'pwork'); ?>"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div id="pwork-reply-content"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo esc_html__('Cancel', 'pwork'); ?></button>
                            <button id="pwork-add-reply-btn" type="button" class="btn btn-primary" data-id="<?php echo esc_attr($topic_id); ?>"><?php echo esc_html__('Reply', 'pwork'); ?></button>
                            </div>
                        </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <div id="pwork-chat-main" class="pwork-chat-wrap">
                    <div class="pwork-chat-row">
                        <div class="pwork-chat-row-left">
                            <a href="<?php echo esc_url($user_profile_url); ?>"><?php echo get_avatar($userID, 300); ?></a>
                            <strong class="text-center"><a href="<?php echo esc_url($user_profile_url); ?>"><?php echo esc_html($user_name); ?></a></strong>
                            <small class="text-center mt-1"><?php echo esc_html__('Topics created:', 'pwork') . ' ' . esc_html(PworkForum::count_user_topics($userID)); ?></small>
                            <small class="text-center"><?php echo esc_html__('Replies created:', 'pwork') . ' ' . esc_html(PworkForum::count_user_replies($userID)); ?></small>
                        </div>
                        <div class="pwork-chat-row-right">
                            <div class="pwork-chat-card">
                                <div class="pwork-chat-card-top-wrap">
                                    <div class="pwork-chat-card-info">
                                        <i class="bx bxs-time me-1"></i><?php echo esc_html__( 'Posted', 'pwork' ) . ' ' . human_time_diff( get_the_date('U', $topic_id) , current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago.', 'pwork' ); ?>
                                    </div>
                                    <?php if ($userID == get_current_user_id() || current_user_can('administrator') || current_user_can('editor')) { ?>
                                    <div class="pwork-chat-card-top">
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo esc_url($edit_topic_url); ?>" class="btn btn-sm pwork-edit-topic btn-dark" title="<?php echo esc_attr__('Edit topic', 'pwork'); ?>"><span class="tf-icons bx bxs-edit-alt"></span></a>
                                            <a href="#" class="btn btn-sm pwork-delete-topic btn-danger" title="<?php echo esc_attr__('Delete topic', 'pwork'); ?>" data-id="<?php echo esc_attr($topic_id); ?>"><span class="tf-icons bx bxs-trash"></span></a>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                                <?php echo wp_kses_post(wpautop($user_content)); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pwork-divider">
                    <span><?php echo esc_html__('Comments', 'pwork'); ?></span>
                </div>

                <?php
                if (!comments_open($topic_id)) {
                    echo '<div class="alert alert-danger">'  . esc_html__('Comments are closed.', 'pwork') .'</div>';
                }
                    $args = array(
                        'post_id' => $topic_id,
                        'status'  => 'approve',
                        'parent' => 0
                    );
                    $comments = get_comments( $args );
                    if ($comments) {
                        echo '<div class="pwork-chat-wrap">';
                        foreach ( $comments as $comment ) {
                            $comment_id = (int) $comment->comment_ID;
                            $parent = $comment->comment_parent;
                            $author_id = $comment->user_id;
                            $author_name = get_the_author_meta('display_name', $author_id);
                            $author_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $author_id;
                            $author_content = $comment->comment_content;
                            ?>
                            <div class="pwork-chat-row parent-<?php echo esc_attr($parent); ?>" data-parent="<?php echo esc_attr($parent); ?>">
                                <div class="pwork-chat-row-left">
                                    <a href="<?php echo esc_url($author_url); ?>"><?php echo get_avatar($author_id, 300); ?></a>
                                    <strong class="text-center"><a href="<?php echo esc_url($author_url); ?>"><?php echo esc_html($author_name); ?></a></strong>
                                    <small class="text-center mt-1"><?php echo esc_html__('Topics created:', 'pwork') . ' ' . esc_html(PworkForum::count_user_topics($author_id)); ?></small>
                                    <small class="text-center"><?php echo esc_html__('Replies created:', 'pwork') . ' ' . esc_html(PworkForum::count_user_replies($author_id)); ?></small>
                                </div>
                                <div class="pwork-chat-row-right">
                                    <div class="pwork-chat-card">
                                        <div class="pwork-chat-card-top-wrap">
                                            <div class="pwork-chat-card-info">
                                                <i class="bx bxs-time me-1"></i><?php echo esc_html__( 'Posted', 'pwork' ) . ' ' . human_time_diff( get_comment_date('U', $comment_id) , current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago.', 'pwork' ); ?>
                                            </div>
                                            <div class="pwork-chat-card-top">
                                                <div class="btn-group" role="group">
                                                    <?php if ($parent == 0 && comments_open($topic_id)) { ?>
                                                    <a href="#" class="btn btn-sm pwork-add-reply btn-dark" data-bs-toggle="modal" data-bs-target="#pwork-add-reply-modal" data-id="<?php echo esc_attr($comment_id); ?>"><?php echo esc_html__('Reply', 'pwork'); ?></a>
                                                    <?php } ?>
                                                    <?php if ($author_id == get_current_user_id() || current_user_can('administrator') || current_user_can('editor')) { ?>
                                                    <a href="#" class="btn btn-sm pwork-delete-comment btn-danger" title="<?php echo esc_attr__('Delete comment', 'pwork'); ?>" data-id="<?php echo esc_attr($comment_id); ?>"><span class="tf-icons bx bxs-trash"></span></a>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pwork-comment-content-area"><?php echo wp_kses_post(wpautop($author_content)); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php $replies_args = array(
                                'post_id' => $topic_id,
                                'status'  => 'approve',
                                'parent' => $comment_id
                            );
                            $replies = get_comments( $replies_args ); 
                            if ($replies) {
                                echo '<div class="pwork-replies-toggle pwork-show-replies"><a href="#">' . esc_html__('Show Replies', 'pwork') . '<i class="bx bx-chevron-down ms-1"></i></a></div>';
                                echo '<div class="pwork-chat-reply-wrap d-none">';
                                echo '<div class="child-start"><i class="bx bx-reply-all"></i></div>';
                                foreach ( $replies as $reply ) {
                                $reply_id = (int) $reply->comment_ID;
                                $reply_parent = $reply->comment_parent;
                                $reply_author_id = $reply->user_id;
                                $reply_author_name = get_the_author_meta('display_name', $reply_author_id);
                                $reply_author_url = get_site_url() . '/' . $slug . '/?page=profile&userID=' . $reply_author_id;
                                $reply_author_content = $reply->comment_content;
                                ?>
                                <div class="pwork-chat-row parent-<?php echo esc_attr($reply_parent); ?>" data-parent="<?php echo esc_attr($reply_parent); ?>">
                                    <div class="pwork-chat-row-left">
                                        <a href="<?php echo esc_url($reply_author_url); ?>"><?php echo get_avatar($reply_author_id, 300); ?></a>
                                        <strong class="text-center"><a href="<?php echo esc_url($reply_author_url); ?>"><?php echo esc_html($reply_author_name); ?></a></strong>
                                        <small class="text-center mt-1"><?php echo esc_html__('Topics created:', 'pwork') . ' ' . esc_html(PworkForum::count_user_topics($reply_author_id)); ?></small>
                                        <small class="text-center"><?php echo esc_html__('Replies created:', 'pwork') . ' ' . esc_html(PworkForum::count_user_replies($reply_author_id)); ?></small>
                                    </div>
                                    <div class="pwork-chat-row-right">
                                        <div class="pwork-chat-card">
                                            <div class="pwork-chat-card-top-wrap">
                                                <div class="pwork-chat-card-info">
                                                    <i class="bx bxs-time me-1"></i><?php echo esc_html__( 'Posted', 'pwork' ) . ' ' . human_time_diff( get_comment_date('U', $reply_id) , current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago.', 'pwork' ); ?>
                                                </div>
                                                <div class="pwork-chat-card-top">
                                                    <div class="btn-group" role="group">
                                                        <?php if ($reply_parent == 0) { ?>
                                                        <a href="#" class="btn btn-sm pwork-add-reply btn-dark"><?php echo esc_html__('Reply', 'pwork'); ?></a>
                                                        <?php } ?>
                                                        <?php if ($reply_author_id == get_current_user_id() || current_user_can('administrator') || current_user_can('editor')) { ?>
                                                        <a href="#" class="btn btn-sm pwork-delete-comment btn-danger" title="<?php echo esc_attr__('Delete comment', 'pwork'); ?>" data-id="<?php echo esc_attr($reply_id); ?>"><span class="tf-icons bx bxs-trash"></span></a>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pwork-comment-content-area"><?php echo wp_kses_post(wpautop($reply_author_content)); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                                </div>
                                <?php } ?>
                        <?php }
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-dark">' . esc_html__('No replies yet.', 'pwork') . '</div>';
                    }
                } else { ?>
                <div class="input-group input-group-merge mb-3">
                    <input id="pwork-topic-search-input" type="text" class="form-control form-control-lg" placeholder="<?php echo esc_attr__( 'Search by topic title...', 'pwork' ); ?>" autocomplete="off">
                    <div id="pwork-topic-search-input-clear" class="input-group-text d-none" title="<?php echo esc_attr__( 'Clear', 'pwork' ); ?>">
                        <i class="bx bx-x cursor-pointer text-danger"></i>
                    </div>
                    <select id="pwork-topic-search-tag" class="form-select" autocomplete="off">
                        <option value="" selected><?php echo esc_html__('All Tags', 'pwork'); ?></option>
                        <?php
                        $tags = get_terms([
                            'taxonomy' => 'pworkforumtags',
                            'orderby' => 'name',
                            'order' => 'ASC',
                            'hide_empty' => true,
                        ]);
                        foreach ($tags as $tag){
                            echo '<option value="' . $tag->term_id . '">' . $tag->name . ' (' . $tag->count . ')</option>';
                        }
                        ?>
                    </select>
                    <button id="pwork-topic-search" type="button" class="btn btn-lg btn-primary"><span class="tf-icons bx bx-search"></span></button>
                </div>
                <div class="d-flex justify-content-between align-items-center flex-wrap w-100 mt-4">
                    <h3 class="fw-bold mb-4">
                        <span class="text-muted fw-light"><?php echo esc_html__('Forum', 'pwork'); ?> /</span> <?php echo esc_html__('Topics', 'pwork'); ?>
                    </h3>
                    <?php 
                    $user = wp_get_current_user();
                    $forum_roles =  PworkSettings::get_option('add_forum_not_allowed_roles', array());
                    if (!array_intersect( $forum_roles, $user->roles )) { 
                    ?>
                    <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#pwork-add-topic-modal"><i class="bx bxs-comment-add me-0 me-md-2"></i><span class="d-none d-md-inline-block"><?php echo esc_html__('Add Topic', 'pwork'); ?></span></button>
                    <div class="modal fade text-start" id="pwork-add-topic-modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                            <h3 class="modal-title"><?php echo esc_html__('Add Topic', 'pwork'); ?></h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__('Close', 'pwork'); ?>"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="mb-3 col-12">
                                        <label for="pwork-topic-title" class="form-label"><?php echo esc_html__( 'Title', 'pwork' ); ?> <span class="text-danger">*</span></label>
                                        <input autocomplete="off" class="form-control" type="text" name="pwork-topic-title" id="pwork-topic-title" value="">
                                    </div>
                                    <div id="pwork-topic-content-wrap" class="mb-3 col-12">
                                        <label for="pwork-topic-content" class="form-label"><?php echo esc_html__( 'Content', 'pwork' ); ?></label>
                                        <div id="pwork-topic-content"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="pwork-topic-tag" class="form-label"><?php echo esc_html__( 'Tag(s)', 'pwork' ); ?></label>
                                        <select  multiple="" autocomplete="off" id="pwork-topic-tag" name="pwork-topic-tag" class="form-select">
                                            <?php
                                            $tags = get_terms([
                                                'taxonomy' => 'pworkforumtags',
                                                'orderby' => 'name',
                                                'order' => 'ASC',
                                                'hide_empty' => false
                                            ]);
                                            foreach ($tags as $tag){
                                                echo '<option value="' . $tag->term_id . '">' . $tag->name . '</option>';   
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="pwork-comments-status" class="form-label"><?php echo esc_html__( 'Status', 'pwork' ); ?></label>
                                        <select autocomplete="off" id="pwork-comments-status" name="pwork-comments-status" class="form-select">
                                            <option value="open" checked><?php echo esc_html__('Open', 'pwork'); ?></option>
                                            <option value="closed"><?php echo esc_html__('Closed', 'pwork'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                            <button id="pwork-cancel-topic-btn" type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php echo esc_html__('Cancel', 'pwork'); ?></button>
                            <button id="pwork-topic-submit" type="button" class="btn btn-primary" data-id=""><?php echo esc_html__('Publish', 'pwork'); ?></button>
                            </div>
                        </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <?php
                $forum_limit = PworkSettings::get_option('forum_limit', 10);
                $topic_args = array(
                    'post_status' => 'publish',
                    'post_type' => 'pworkforum',
                    'posts_per_page'  => 99999,
                    'meta_key' => 'pwork_last_activity',
                    'order'  => 'DESC',
                    'orderby'  => 'meta_value'
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
                                    <th class="d-none d-md-table-cell text-center"><?php echo esc_html__('Last Activity', 'pwork'); ?></th>
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
                                        <small>
                                        <?php
                                        if ($last_activity && !empty($last_activity)) { 
                                            echo human_time_diff( $last_activity, current_time( 'timestamp' ) ) . ' ' . esc_html__( 'ago', 'pwork' );
                                        } else {
                                            echo human_time_diff( get_the_date('U'), current_time( 'U' ) ) . ' ' . esc_html__( 'ago', 'pwork' );
                                        }
                                        ?>
                                        </small>
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
                <?php } ?>
            </div>
            <div class="content-backdrop fade"></div>
        </div>
    </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php include_once('footer.php'); ?>