<h3>Royal Slider</h3>
<div class="royalSlider rsDefault">
    <!-- simple image slide -->
    <img class="rsImg" src="http://placehold.it/1200x400.jpg" alt="image desc" />
    <img class="rsImg" src="http://placehold.it/1200x400.jpg" alt="image desc" />
    <img class="rsImg" src="http://placehold.it/1200x400.jpg" alt="image desc" />
    <img class="rsImg" src="http://placehold.it/1200x400.jpg" alt="image desc" />
    <img class="rsImg" src="http://placehold.it/1200x400.jpg" alt="image desc" />
</div>

<section class="home-cms-content">
	<header>
		<h3>CMS Content</h3>
	</header>
	<?php while (have_posts()) : the_post(); ?>
	  <?php the_content(); ?>
	  <?php wp_link_pages(array('before' => '<nav class="pagination">', 'after' => '</nav>')); ?>
	<?php endwhile; ?>
</section>

<section class="home-articles">
	<header>
		<h3>Latest Posts</h3>
	</header>
<?php
	$args = array( 'posts_per_page' => 7, 'suppress_filters' => 0 ); // suppress_filters retrieves posts in current language
	$lastposts = get_posts( $args );
	$count = 1;
	// print_r($lastposts);
	foreach ( $lastposts as $post ) :
	  setup_postdata( $post ); ?>
		<div class="post-item <?php if($count == 1) echo 'first' ?>">
			<?php $imageid = get_post_meta($post->ID, 'image_full', true); ?>
			<?php if($count == 1) {?>
				<?php $imageid = get_post_meta($post->ID, 'image_full', true); ?>
				<div class="img-container">
					<?php 
						if(has_post_thumbnail()) {
						    the_post_thumbnail();
						} else {
						    echo '<img src="'.get_bloginfo("template_url").'/assets/img/placeholder-1140.gif" />';
						}
					 ?>
					<span class="date-container"><?php the_time('d');?><span class="month"><?php the_time('M');?></span></span>
				</div>
			<?php } ?>
			<div class="post-content">
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

			<?php if($count != 1) {?>
				<?php $imageid = get_post_meta($post->ID, 'image_preview', true); ?>
				<div class="img-container">
					<span class="date-container"><?php the_time('d');?><span class="month"><?php the_time('M');?></span></span>
					<?php 
						if(has_post_thumbnail()) {
						    the_post_thumbnail();
						} else {
						    echo '<img src="'.get_bloginfo("template_url").'/assets/img/placeholder-600.gif" />';
						}
					 ?>
				</div>
				<a class="read-more" href="<?php the_permalink(); ?>">Lire l'article</a>
			<?php }else{ ?>
				<?php the_excerpt(); ?>
				<a class="read-more" href="<?php the_permalink(); ?>">Lire l'article</a>
			<?php } ?>
			</div>
		</div>
		<?php $count++;  ?>
	<?php endforeach;
	
	wp_reset_postdata(); ?>
	<div class='clearfix'></div>
</section>

<section class="features">
	<header>
		<h3>Features</h3>
	</header>

	<article>
		<h3>Font Awesome Included</h3>
		<i class="fa fa-flag"></i> This site uses Font-awesome !
	</article>

	<article>
		<h3>TweenMax Included</h3>
		This site comes bundled with TweenMax for all your awesome animations ! &nbsp;&nbsp; <i class="fa fa-heart"></i>
	</article>

	<article>
		<h3>Snap.js Included</h3>
		<i class="fa fa-arrows-h"></i> Drag this panel on mobile to reveal a left pane menu!
	</article>
</section>
