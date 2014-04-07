<footer class="content-info" role="contentinfo">
  <div class="container">
    <?php dynamic_sidebar('sidebar-footer'); ?>
    <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?></p>
  </div>

  <!-- WPML Language switch -->
  <div class="container">
    <?php do_action('icl_language_selector'); ?>
  </div>
</footer>

<?php wp_footer(); ?>
