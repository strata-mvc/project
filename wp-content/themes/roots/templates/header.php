<header class="top-header banner container-fluid" role="banner">
  <div class="container">
      
    <div class="row nav-main-container">
      
      <div class="col-md-6">
        <!-- <button type="button" class="snap-toggle"><i class="fa fa-bars fa-2x"></i></button> -->
        <button type="button" class="hamburger snap-toggle">
          <div class="top"></div>
          <div class="middle"></div>
          <div class="bottom"></div>
        </button>

        <a class="brand" title="<?php bloginfo('name'); ?>" href="<?php echo home_url('/') ?>"><img alt="<?php bloginfo('name'); ?>" src="http://placehold.it/220x80" /></a>
        <div class="clearfix"></div>
      </div>

      <div class="col-md-6 hidden-xs hidden-sm">
        <nav class="nav-main" role="navigation">
          <?php
            if (has_nav_menu('primary_navigation')) :
              wp_nav_menu(array('theme_location' => 'primary_navigation', 'menu_class' => ''));
            endif;
          ?>
        </nav>
      </div>

    </div>

    <div class="row">

      <div class="col-md-8 hidden-xs hidden-sm">
        <nav class="nav-secondary" role="navigation">
          <?php
            if (has_nav_menu('secondary_navigation')) :
              wp_nav_menu(array('theme_location' => 'secondary_navigation', 'menu_class' => ''));
            endif;
          ?>
        </nav>
      </div>

      <div class="col-md-4">
        <?php echo roots_get_search_form(); ?>
      </div>

    </div>
  </div>
</header>
