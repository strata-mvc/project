<?php
	use \App\Model\Wpml;
?>

<section class="success-stories grey-background">
<div class="inner-content">
    <div class="container">
		<h2 class="section-title"><?php echo $Acf->get("success_stories_title"); ?></h2>
            <?php $storyID = $post->ID; ?>
            <?php $storyLogo = $Acf->get("story_logo", $storyID); ?>
		    <?php $storyStats = $Acf->get("story_stats", $storyID); ?>
		    <?php $storyQuotes = $Acf->get("story_quotes", $storyID); ?>
			<div class="story">
				<div class="row stats">
					
						<div class="col-md-4 item logo">
							<?php if (!is_null($storyLogo)) : ?>
						    	<img src="<?php echo $storyLogo; ?>" alt="<?php echo get_the_title($storyID); ?>" />
						    <?php else : ?>
						    	<h4><?php echo get_the_title($storyID); ?></h4>
						    <?php endif; ?>
						</div>
					
					<?php $statsCount = 1; ?>
					<?php foreach($storyStats as $stat) : ?>
						<div class="col-md-4 item stat">
							<div class="number">
								+<div class="odometer" data-stat="<?php echo $stat["story_stat"]; ?>">0</div>%
							</div>
							<div class="text"><?php echo $stat["story_stat_subtext"]; ?></div>
						</div>
						<?php $statsCount++; ?>
						<?php if ($statsCount == 3) break; ?>
					<?php endforeach; ?>

				</div>

				<?php foreach($storyQuotes as $quote) : ?>
					<blockquote>
					  	<div class="text">
					  		<?php echo $quote["story_quote"]; ?>
					  	</div>
					  	<div class="author">
					  		<div class="name"><?php echo $quote["story_quote_author"]; ?>,</div> 
							<div class="position"><?php echo $quote["story_quote_author_position"]; ?></div>
					  	</div>
					</blockquote>
					<?php break; ?>
                <?php endforeach; ?>

				<?php if (!$Acf->isEmpty("success_cta_link")) : ?>
					<div class="btm-actions">
						<a class="btn" href="<?php echo $Acf->get("success_cta_link"); ?>"><?php echo $Acf->get("success_cta_text"); ?></a>
					</div>
				<?php endif; ?>
			</div>
    </div>
</div>
</section>



