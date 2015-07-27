<footer role="contentinfo">
  <div class="top-content">
	  <div class="container">
	  	<?php echo $FooterMenuHelper->renderFooterNavigation(); ?>
	  </div>
  </div>
  <div class="btm-content">
	  <div class="container">	    
	  	<div class="col-md-10">
		  	<div class="copyright">&copy; <?php _e("Copyright", PROJECT_KEY); ?> <?php bloginfo('name'); ?> <?php echo date('Y'); ?></div>
	  		<?php echo $MenuHelper->renderBtmFooterNavigation(); ?>
	  	</div>
	  	<div class="col-md-2">
	  		<img class="svg footer-logo" width="126" height="25" alt="<?php bloginfo('name'); ?>" src="<?php bloginfo('stylesheet_directory'); ?>/assets/img/amnet-logo.svg" />                
	  	</div>
	  </div>
  </div>
</footer>

<?php wp_footer(); ?>
