<?php
  // consider moving this block inside the AppController when
  // setting up the project:


    $GTMViewHelper = new IP\GoogleTagManager\GTMViewHelper();
    $GTMViewHelper->callRemotePlugin();

    $WordpressHelper = new App\View\Helper\WordpressHelper();

?>


<?php get_template_part('templates/head'); ?>
<body <?php body_class(); ?>>

    <!--[if lt IE 9]>
        <div class="alert alert-warning">
          <?php _e('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'roots'); ?>
        </div>
    <![endif]-->

    <?php
        $GTMViewHelper->callRemotePlugin();
        echo $GTMViewHelper->render();
    ?>


  <div id="wrapper">

    <?php
      do_action('get_header');
      get_template_part('templates/header');
    ?>

    <div class="wrap fluid-container" role="document">
      <div class="content row">
        <main class="main" role="main">
          <?php include roots_template_path(); ?>
        </main>
      </div>
    </div>

    <?php get_template_part('templates/footer'); ?>
  </div>
</body>
</html>
