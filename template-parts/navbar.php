<?php 
$logo = PworkSettings::get_option('logo', PWORK_PLUGIN_URL . 'assets/logo.webp');
$user = get_user_by( 'ID', get_current_user_id());
$slug = PworkSettings::get_option('slug', 'pwork');
?>
<nav id="pwork-navbar" class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center d-xl-none">
  <div class="navbar-nav-right d-flex align-items-center justify-content-between" id="navbar-collapse">
    <a href="<?php echo esc_url(get_site_url() . '/' . $slug . '/'); ?>" class="navbar-brand">
      <img src="<?php echo esc_url($logo); ?>">
    </a>
    <div class="layout-menu-toggle navbar-nav align-items-xl-center ms-3 me-xl-0">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="bx bx-menu bx-sm"></i>
    </a>
    </div>
  </div>
</nav>