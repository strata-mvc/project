<?php
	use \App\Model\News;
	use \App\Model\Wpml;
?>



<?php $ticker = $Acf->get("news_ticker"); ?>
<?php if (count($ticker)) : ?>
<section class="news-ticker">
	<div class="container">
		<ul>
			<?php foreach ($ticker as $news) : ?>
			<li>
				<?php if (is_array($news['news_article']))  : ?>
					<a class="news-item" href="<?php echo get_the_permalink($news['news_article']); ?>">
				<?php else : ?>
					<a class="news-item" href="<?php echo $news['news_custom_link']; ?>">
				<?php endif; ?>

					<?php echo $news['news_title']; ?>
				</a>

				<a class="cta" href="<?php echo News::getBaseUrl(); ?>"><?php echo $Acf->get("news_ticker_call_to_action"); ?></a>

			</li>
			<?php endforeach; ?>
		</li>


	</div>
</section>
<?php endif; ?>

<section class="banner-header">
     <div class="media-container">
     <?php if($Acf->get("our_approach_bg_type") == "video") : ?>
        <video class="video" id="bgv_shIBu9" poster="" preload="true" loop="true" autoplay="true" muted="true"><!--autobuffer="true"-->
             <source src="http://player.vimeo.com/external/106598502.hd.mp4?s=9a463ad2b1f30744330de8c0a825e454" type="video/mp4">
             <source src="http://player.vimeo.com/external/106598502.mobile.mp4?s=ab678224cb0aca736e8aa876a1074b19" media="all and (max-width: 680px)">
             <source src="http://cdn.paralachs.de/intro.webm" type="video/webm">
        </video>
     <?php else : ?>
     	<?php
		    $detect = new \Detection\MobileDetect();

		    $img = get_field("our_approach_bg_image"); 
		    $size = ($detect->isMobile() && !$detect->isTablet()) ? "banner-mobile" : "banner";
		    $defaultImg = get_placeholder_image($size);
		    $img = ( isset($img["sizes"][$size]) ) ? $img : $defaultImg;
		    
		    $imgSrc = $img["sizes"][$size];
		    $imgHeight = $img["sizes"][$size."-height"];
		    $imgWidth = $img["sizes"][$size."-width"];
		?>
		<img src="<?php echo $imgSrc; ?>" height="<?php echo $imgHeight; ?>" width="<?php echo $imgWidth; ?>" alt="<?php echo $Acf->get("our_approach_title").get_bloginfo("name"); ?>" />
     <?php endif; ?>
     </div>

    <div class="overlay">
        <div class="banner-inner">
	     	<div class="container">
	     	    <div class="section-content">
		     		<div class="banner-content">
						<h2><?php echo $Acf->get("our_approach_title"); ?></h2>
						<?php echo $Acf->get("our_approach_content"); ?>
						<?php if (!$Acf->isEmpty("our_approach_cta_link")) : ?>
							<a class="btn white" href="<?php echo $Acf->get("our_approach_cta_link"); ?>"><?php echo $Acf->get("our_approach_cta_label"); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
	    </div>
	</div>
</section>

<section class="we-are-experts">
    <div class="container">
    	<div class="col-md-6 left-block">
			<h2><?php echo $Acf->get("experts_title"); ?></h2>
		</div>
		<div class="col-md-6 right-block">
			<?php
			    $img = $Acf->get("experts_image");
			    $size = "medium";
	    		$imgSrc = $img["sizes"][$size];
				$imgHeight = $img["sizes"][$size."-height"];
				$imgWidth = $img["sizes"][$size."-width"];
			?>
			<div class="circle">
				<div class="text">
					<?php echo $Acf->get("experts_circle_text"); ?>
				</div>
			</div>

			<?php echo $Acf->get("experts_paragraph"); ?>

			<?php if (!$Acf->isEmpty("experts_CTA_link")) : ?>
				<a class="btn" href="<?php echo $Acf->get("experts_CTA_link") ?>"><?php echo $Acf->get("experts_CTA_text"); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php $localBGImg =  $Acf->get("local_team_bg_image"); ?>
<section class="home-quote"  <?php echo ($localBGImg) ? 'style="background-image:url('.$localBGImg.')"' : ''; ?>>
	<div class="container">
      <h2 class="section-title"><?php echo $Acf->get("local_team_title"); ?></h2>
      <?php foreach ((array)$Acf->get("quotes") as $idx => $quote) : ?>
	      <blockquote>
	      	<div class="text">
	      		<?php echo $quote["quote_text"]; ?>
	      	</div>
	      	<div class="author">
	      		<div class="name"><?php echo $quote["quote_author_name"]; ?>,</div> 
				<div class="position"><?php echo $quote["quote_author_position"]; ?></div>
	      	</div>
	      </blockquote>
	  <?php endforeach; ?>
	  <?php if (!$Acf->isEmpty("local_team_cta_link")) : ?>
      	<a class="btn white" href="<?php echo $Acf->get("local_team_cta_link"); ?>"><?php echo $Acf->get("local_team_cta_text"); ?></a>
      <?php endif; ?>
    </div>
</section>

<?php $WordpressHelper->common("solutions"); ?>


<section class="success-stories grey-background">
<div class="inner-content">
    <div class="container">
		<h2 class="section-title"><?php echo $Acf->get("success_stories_title"); ?></h2>
            <?php $storyID = $Acf->get("featured_success_story"); ?>
            <?php $storyLogo = $Acf->get("story_logo", $storyID); ?>
		    <?php $storyStats = $Acf->get("story_stats", $storyID); ?>
		    <?php $storyQuotes = $Acf->get("story_quotes", $storyID); ?>
			<div class="story">
				<div class="row stats">
					
						<div class="col-md-4 item logo">
							<a href="<?php echo get_permalink($storyID); ?>">
								<?php if (!is_null($storyLogo)) : ?>
							    	<img src="<?php echo $storyLogo; ?>" alt="<?php echo get_the_title($storyID); ?>" />
							    <?php else : ?>
							    	<h4><?php echo get_the_title($storyID); ?></h4>
							    <?php endif; ?>
							</a>
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



