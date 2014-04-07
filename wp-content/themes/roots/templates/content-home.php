<div class="royalSlider rsDefault">
    <!-- simple image slide -->
    <img class="rsImg" src="http://placehold.it/1200x400.jpg" alt="image desc" />
    <img class="rsImg" src="http://placehold.it/1200x400.jpg" alt="image desc" />
    <img class="rsImg" src="http://placehold.it/1200x400.jpg" alt="image desc" />
    <img class="rsImg" src="http://placehold.it/1200x400.jpg" alt="image desc" />
    <img class="rsImg" src="http://placehold.it/1200x400.jpg" alt="image desc" />
</div>

<?php while (have_posts()) : the_post(); ?>
  <?php the_content(); ?>
  <?php wp_link_pages(array('before' => '<nav class="pagination">', 'after' => '</nav>')); ?>
<?php endwhile; ?>

<section class="features">
	<header>
		<h2>Features</h2>
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
