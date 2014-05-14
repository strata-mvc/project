<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

  <!--[if lt IE 9]>
    <div class="alert alert-warning">
      <?php _e('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'roots'); ?>
    </div>
  <![endif]-->

  <div class="snap-drawers">
    <div class="snap-drawer snap-drawer-left">
      <?php
        if (has_nav_menu('primary_navigation')) :
          wp_nav_menu(array('theme_location' => 'primary_navigation', 'menu_class' => ''));
        endif;
      ?>
    </div>
  </div>

  <div id="snap-content">

    <?php
      do_action('get_header');
      // Use Bootstrap's navbar if enabled in config.php
      if (current_theme_supports('bootstrap-top-navbar')) {
        get_template_part('templates/header-top-navbar');
      } else {
        get_template_part('templates/header');
      }
    ?>

    <div class="wrap container" role="document">
      <div class="content row">
        <main class="main <?php echo roots_main_class(); ?>" role="main">
          <?php include roots_template_path(); ?>
        </main><!-- /.main -->
        <?php if (roots_display_sidebar()) : ?>
          <aside class="sidebar <?php echo roots_sidebar_class(); ?>" role="complementary">
            <?php include roots_sidebar_path(); ?>
          </aside><!-- /.sidebar -->
        <?php endif; ?>
      </div><!-- /.content -->
    </div><!-- /.wrap -->

    <?php get_template_part('templates/footer'); ?>

  </div>

</body>
</html>
