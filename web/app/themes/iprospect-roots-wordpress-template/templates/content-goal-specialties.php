
<section class="first-block">
        <div class="container">
            <div class="section-title">
                <h2><?php echo $Acf->get("first_block_title"); ?></h2>
                <?php echo $Acf->get("first_block_content"); ?>
            </div>
        </div>
</section>

<section class="recommendations row grey-background flip">
    <div class="inner-content">
        <div class="container">
            <h2 class="section-title"><?php echo $Acf->get("recommendations_title"); ?></h2>
            <div class="row">
            <?php $count = 1; ?>
            <?php foreach((array)$Acf->get("recommendation_repeater") as $idx => $recommendation) : ?>
                <div class="item col-sm-4">
                    <h3><?php echo $recommendation['title']; ?></h3>
                    <?php echo $recommendation['content']; ?>
                </div>
                <?php echo ($count % 3 === 0) ? '</div><div class="row">' : ''; ?>
                <?php $count++; ?>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php $contentBlocks = $Acf->get("tiles_repeater"); ?>
<section class="complements">
    <div class="container">
        <div class="section-title">
            <h2><?php echo $Acf->get("tiles_complement_title"); ?></h2>
            <?php echo $Acf->get("tiles_complement_content"); ?>
        </div>
        <div class="four-blocks clearfix">
        <?php if (count($contentBlocks)) : foreach ($contentBlocks as $block) : ?>
            <div class="item">
                <div class="inner">
                    <div class="title">
                        <h3><?php echo $block['title']; ?></h3>
                        <div class="show-more">
                            <div class="circle">+</div>
                        </div>
                    </div>
                    
                    <div class="content">
                        <?php echo $block['content']; ?>
                        <?php /*
                        <a class="cta white" href="<?php echo $block["solution_destination"]; ?>"><?php echo $block["solution_call_to_action"]; ?></a>
                        */ ?>
                    </div>
                    
                </div>
            </div>
        <?php endforeach; endif; ?>
        </div>
    </div>
</section>