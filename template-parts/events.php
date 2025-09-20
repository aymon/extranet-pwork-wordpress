<?php include_once('header.php'); ?>
<?php Pwork::remove_notification('events'); ?>
<div id="pwork-events-page" class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
    <?php include_once('aside.php'); ?>
    <div class="layout-page">
    <?php include_once('navbar.php'); ?>
        <div class="content-wrapper">
            <div class="container-fluid flex-grow-1 container-p-y">
                <div class="card"><div class="card-body"><div id="calendar"></div></div></div>
            </div>
            <div class="content-backdrop fade"></div>
        </div>
    </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<?php include_once('footer.php'); ?>