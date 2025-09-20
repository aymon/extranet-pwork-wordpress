<?php 
$userID = get_current_user_id();
$forum = PworkSettings::get_option('forum_module', 'enable');
$events = PworkSettings::get_option('events_module', 'enable');
$files =  PworkSettings::get_option('files_module', 'enable');
$projects =  PworkSettings::get_option('projects_module', 'enable');
?>
<div id="my-statistics-widget" class="pwork-widget col-12 col-md-12 col-lg-8 col-xl-8 col-xxl-6">
    <div class="card post-card">
        <div class="card-header pwork-widget-header bg-dark">
            <h6 class="d-flex align-items-center text-uppercase m-0 text-white"><?php echo esc_html__( "My Statistics", 'pwork' ); ?><i class="bx bx-move ms-auto text-white grabbing"></i></h6>
        </div>
        <div class="pwork-statistics-row row m-0">
            <div class="col-6 col-sm-4 p-0">
                <div class="pwork-statistics-item">
                    <div class="pwork-statistics-icon">
                        <div class="pwork-statistics-icon-wrap"><i class="bx bxs-contact"></i></div>
                    </div>
                    <div class="pwork-statistics-icon-content">
                        <label><?php echo esc_html__( "Contacts", 'pwork' ); ?></label>
                        <span class="d-block text-dark"><?php echo esc_html(Pwork::count_contacts($userID)); ?></span>
                    </div>
                </div>
            </div>
            <?php if ($forum == 'enable') { ?>
            <div class="col-6 col-sm-4 p-0">
                <div class="pwork-statistics-item">
                    <div class="pwork-statistics-icon">
                        <div class="pwork-statistics-icon-wrap"><i class="bx bxs-conversation"></i></div>
                    </div>
                    <div class="pwork-statistics-icon-content">
                        <label><?php echo esc_html__( "Topics", 'pwork' ); ?></label>
                        <span class="d-block text-dark"><?php echo esc_html(PworkForum::count_user_topics($userID)); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 p-0">
                <div class="pwork-statistics-item">
                    <div class="pwork-statistics-icon">
                        <div class="pwork-statistics-icon-wrap"><i class="bx bxs-comment-detail"></i></div>
                    </div>
                    <div class="pwork-statistics-icon-content">
                        <label><?php echo esc_html__( "Topic Comments", 'pwork' ); ?></label>
                        <span class="d-block text-dark"><?php echo esc_html(PworkForum::count_user_replies($userID)); ?></span>
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php if ($files == 'enable') { ?>
            <div class="col-6 col-sm-4 p-0">
                <div class="pwork-statistics-item">
                    <div class="pwork-statistics-icon">
                        <div class="pwork-statistics-icon-wrap"><i class="bx bxs-file"></i></div>
                    </div>
                    <div class="pwork-statistics-icon-content">
                        <label><?php echo esc_html__( "Shared Files", 'pwork' ); ?></label>
                        <span class="d-block text-dark"><?php echo esc_html(PworkFiles::count_my_files()); ?></span>
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php if ($events == 'enable') { ?>
            <div class="col-6 col-sm-4 p-0">
                <div class="pwork-statistics-item">
                    <div class="pwork-statistics-icon">
                        <div class="pwork-statistics-icon-wrap"><i class="bx bxs-calendar"></i></div>
                    </div>
                    <div class="pwork-statistics-icon-content">
                        <label><?php echo esc_html__( "Events", 'pwork' ); ?></label>
                        <span class="d-block text-dark"><?php echo esc_html(PworkEvents::count_my_events()); ?></span>
                    </div>
                </div>
            </div>    
            <?php } ?>
            <?php if ($projects == 'enable') { ?>
            <div class="col-6 col-sm-4 p-0">
                <div class="pwork-statistics-item">
                    <div class="pwork-statistics-icon">
                        <div class="pwork-statistics-icon-wrap"><i class="bx bxs-rocket"></i></div>
                    </div>
                    <div class="pwork-statistics-icon-content">
                        <label><?php echo esc_html__( "Projects", 'pwork' ); ?></label>
                        <span class="d-block text-dark"><?php echo esc_html(PworkProjects::count_my_projects()); ?></span>
                    </div>
                </div>
            </div>    
            <?php } ?>
        </div>
    </div>
</div>