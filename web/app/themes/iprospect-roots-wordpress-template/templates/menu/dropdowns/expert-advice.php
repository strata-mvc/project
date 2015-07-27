<div class="dropdown dropdown-<?php echo $dropdown->getTemplateName(); ?>">
    <div class="dropdown-inner col-sm-10  col-sm-offset-2">
    	<div class="block">
	    	<a class="submenu-link" href="<?php echo $data["block_link"]; ?>"><?php echo $data["block_title"]; ?></a>
		    <?php echo $data["block_content"]; ?>
	    </div>
	    <div class="block last">
	        <a class="submenu-link" href="<?php echo $data["blog_link"]; ?>"><?php echo $data["blog_title"]; ?></a>
	        <?php if($dropdown->getBlogPosts()) : ?>
		        <ul class="blog-posts">
		        <?php foreach ($dropdown->getBlogPosts() as $post) : setup_postdata( $post ); ?>
		        	<li class="col-sm-6">
		        		<?php if(has_post_thumbnail($post->ID)) : ?>
			        	<div class="post-image col-sm-4">
			        		<?php echo get_the_post_thumbnail($post->ID, 'thumbnail') ?>
			        	</div>
			        	<?php endif; ?>
			        	<div class="post-content <?php (has_post_thumbnail($post->ID)) ? "col-sm-8" : "col-sm-12"; ?>">
					        <h5><a href="<?php echo get_permalink($post->ID); ?>"><?php echo $post->post_title; ?></a></h5>
					        <p><?php echo wp_trim_words($post->post_content, 8); ?></p>
				        </div>
			        </li>
			    <?php endforeach; ?>
			    </ul>
			    <?php wp_reset_postdata(); ?>
		    <?php endif; ?>
	    </div>
    </div>
</div>