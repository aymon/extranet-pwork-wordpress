<?php
$logo = PworkSettings::get_option('logo', PWORK_PLUGIN_URL . 'assets/logo.webp');
$slug = PworkSettings::get_option('slug', 'pwork');
$page = 'dashboard';
if(isset($_GET['page']) && !empty($_GET['page'])) {
  $page = $_GET['page'];
}
$pages = Pwork::get_pages();
?>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand">
    <a href="<?php echo esc_url(get_site_url() . '/' . $slug . '/'); ?>" class="navbar-brand">
      <img src="<?php echo esc_url($logo); ?>">
    </a>
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>
  <div class="menu-inner-shadow"></div>
  <ul class="menu-inner">
    <?php 
    foreach($pages as $id => $val) {
      $menu_class = 'menu-item';
      if ($id === $page || strpos($page, $id) === 0) {
        $menu_class = 'menu-item active open';
      }
      if ($id === 'profile' && isset($_GET['userID']) && $_GET['userID'] != get_current_user_id()) {
        $menu_class = 'menu-item';
      }
      if (!empty($val[3])) {
        echo '<li class="menu-header small text-uppercase"><span class="menu-header-text">' . esc_html($val[3]) . '</span></li>';
      }
      echo '<li id="menu-item-' . $id . '" class="' . $menu_class . '">';
      if (empty($val[4]) || !is_array($val[4])) {
        echo '<a href="' . esc_url($val[2]) . '" class="menu-link"><i class="menu-icon tf-icons bx ' . esc_attr($val[1]) . '"></i><div>' . esc_html($val[0]) . '</div></a>';
      } else {
        echo '<a href="javascript:void(0);" class="menu-link menu-toggle"><i class="menu-icon tf-icons bx ' . esc_attr($val[1]) . '"></i><div>' . esc_html($val[0]) . '</div></a>';
        echo '<ul class="menu-sub">';
        foreach($val[4] as $item) {
          if ($item[0] === $page) {
            echo '<li class="menu-item active"><a href="' . $item[2] . '" class="menu-link"><div>' . $item[1] . '</div></a></li>';
          } else {
            echo '<li class="menu-item"><a href="' . $item[2] . '" class="menu-link"><div>' . $item[1] . '</div></a></li>';
          }
        }
        echo '</ul>';
      }
      echo '</li>';
    }

    echo '<li class="menu-item">';
    echo '<a href="' . esc_url(wp_logout_url()) . '" class="menu-link"><i class="bx bx-power-off me-2"></i><div>' . esc_html__( 'Log Out', 'pwork' ) . '</div></a>';
    echo '</li>';

    if (has_nav_menu('pwork-side-menu')) { 
      $menuLocations = get_nav_menu_locations();
      $menuID = $menuLocations['pwork-side-menu'];
      $navMenuItems = wp_get_nav_menu_items($menuID); 
      echo '<li class="menu-header small text-uppercase"><span class="menu-header-text">' . esc_html__( 'Links', 'pwork' ) . '</span></li>';
      foreach($navMenuItems as $item) {
        echo '<li class="menu-item"><a href="' . esc_url($item->url) . '" class="menu-link" target="_blank"><i class="menu-icon tf-icons bx bx-link"></i><div>' . esc_html($item->post_title) . '</div></a></li>';
      }
    }
    ?>
  </ul>
</aside>