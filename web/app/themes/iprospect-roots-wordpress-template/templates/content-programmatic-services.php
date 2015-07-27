<section class="programmatic">
    <div class="inner-content">
        <div class="container">
            <div class="section-title">
                <h2><?php echo $Acf->get("programmatic_presentation"); ?></h2>
                <img src="<?php echo $Acf->get("programmatic_thumbnail")["url"]; ?>">
            </div>
        </div>
    </div>
</section>

<section class="goals row grey-background">
    <div class="inner-content">
        <div class="container">
            <h2 class="section-title"><?php echo $Acf->get("goals_title"); ?></h2>
            <?php $count = 1; ?>
            <?php foreach((array)$Acf->get("goal_repeater") as $goal) : ?>
                <div class="goal row <?php echo ( $count == 1 ) ? "first" : ""; ?>">
                    <div class="col-md-12">
                        <h3><?php echo $goal['title']; ?></h3>
                    </div>
                    <div class="col-md-8 text">
                        <?php echo $goal['content']; ?>
                    </div>
                   
                    <div class="col-md-4">
                        <?php if ($goal["tactics"]) : ?>
                            <h4><?php _e("Tactics include:", PROJECT_KEY); ?></h4>
                            <ul class="tactics">
                                <?php foreach((array)$goal['tactics'] as $tactic) : ?>
                                    <li><?php echo $tactic['tactic']; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (!empty($goal['page_link'])) : ?>
                            <a class="cta" href="<?php echo $goal['page_link']; ?>"><?php echo $goal['link_label']; ?></a>
                        <?php endif; ?>
                    </div>
                    
                </div>
                <?php $count++; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>



<section class="specialties">
    <div class="inner-content">
        <div class="container">
            <div class="section-title">
                <h2><?php echo $Acf->get("specialty_title"); ?></h2>
            </div>
        </div>
    </div>
</section>

<section class="bottom-ctas">
    <div class="container">
        <?php $ctas = (array)$Acf->get("specialty_repeater"); ?>
        <?php $class = (count($ctas) == 3) ? "col-md-4" : "col-md-6"; ?>
        <?php foreach ($ctas as $idx => $cta) : ?>
            <a class="cta-block <?php echo $class; ?>" href="<?php echo $cta['call_to_action_link']; ?>">
                <div class="block-icon">
                    <?php if($cta['block_image_type'] == "image") : ?>
                        <img src="<?php echo $cta['block_image']; ?>" alt="" class="svg" />
                    <?php else : ?>
                        <?php echo $cta['block_icon']; ?>
                    <?php endif; ?>
                </div>
                <h4><?php echo $cta["title"] ?></h4>
                <?php echo $cta["content"] ?>
                <div class="btn" href="<?php echo $cta['call_to_action_link']; ?>"><?php echo $cta['call_to_action_label']; ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</section>