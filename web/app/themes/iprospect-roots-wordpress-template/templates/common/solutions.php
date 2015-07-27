
<?php $solutionsBlocks = $Acf->get("solutions"); ?>
<section class="solutions">
    <div class="container">
        <h2 class="section-title"><?php echo $Acf->get("solutions_title"); ?></h2>

        <div class="four-blocks clearfix">
        <?php if (count($solutionsBlocks)) : foreach ($solutionsBlocks as $block) : ?>
            <div class="item">
                <div class="inner">
                    <div class="title">
                        <h3><?php echo $block["solution_title"]; ?></h3>
                        <div class="show-more">
                            <div class="circle">+</div>
                        </div>
                    </div>
                    <div class="content">
                        <?php echo $block["solution_description"]; ?>
                        <a class="cta white" href="<?php echo $block["solution_destination"]; ?>"><?php echo $block["solution_call_to_action"]; ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
        </div>

        <div class="btm-actions">
            <a class="btn" href="#"><?php _e("View more success stories", PROJECT_KEY); ?></a>
        </div>
        
    </div>
</section>
