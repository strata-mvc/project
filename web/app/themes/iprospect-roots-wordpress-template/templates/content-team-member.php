<?php
	use \App\Model\Wpml;
?>

<section class="member-header">
	<div class="container">
		<div class="col-sm-6">
			<?php $img = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' ); ?>
			<img src="<?php echo $img[0]; ?>" height="<?php echo $img[2]; ?>" width="<?php echo $img[1]; ?>" alt="<?php echo get_the_title()." ".get_bloginfo("name"); ?>" />
		</div>
		<div class="col-sm-6">
			<h1><?php echo get_the_title(); ?></h1>
		</div>
	</div>
</section>

<section class="values grey-background">

</section>

