<?php include_once('header.php'); ?>
<div id="pwork-error-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-sm flex-grow-1 container-p-y">
                <div class="d-flex align-items-center h-100">
                    <div class="card bg-warning text-center">
                        <div class="card-body">
                            <h1>404</h1>
                            <p><?php echo esc_html__("We are sorry but it looks like the page doesn't exist anymore.", 'pwork' ); ?></p>
                            <div class="d-block">
                                <a href="#" class="btn btn-pill btn-dark"><?php echo esc_html__("Back to Dashboard", 'pwork' ); ?></a>
                            </div>
                        </div>
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