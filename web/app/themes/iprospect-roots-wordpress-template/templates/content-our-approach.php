


<section class="assessments">
    <h1><?php echo $Acf->get("assessment_title"); ?></h1>
    <?php foreach($Acf->get("assessment_repeater") as $idx => $assessment) : ?>
        <?php $link = $assessment['link']; ?>
        <div class="assessment <?php echo ($idx % 2) === 0 ? "even" : "odd"; ?>">
            <div class="content">
                <h2><?php echo $assessment['title']; ?></h2>
                <?php echo $assessment['content']; ?>
                <?php if (!empty($link)) : ?>
                    <a href="<?php echo $link; ?>"><?php echo $assessment['link_label']; ?></a>
                <?php endif; ?>
            </div>
            <div class="thumbnail">
                <?php if (!empty($link)) : ?><a href="<?php echo $link; ?>"><?php endif; ?>
                <img src="<?php echo $assessment['thumbnail']; ?>">
                <?php if (!empty($link)) : ?></a><?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</section>



<section class="team">
    <h1><?php echo $Acf->get("team_title"); ?></h1>
    <?php echo $Acf->get("team_description"); ?>

    <?php $teams = $Acf->get("team_repeater"); ?>

    <?php if (is_array($teams)) : ?>
    <ul>
    <?php foreach($teams as $idx => $team) : ?>
        <li class="<?php echo ($idx % 2) === 0 ? "even" : "odd"; ?>"><a href="<?php echo $team['link']; ?>"><img src="<?php echo $team['team_icon']; ?>"></a></li>
    <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <a href="<?php echo $Acf->get('meet_your_team_link'); ?>"><?php echo $Acf->get('meet_your_team_label'); ?></a>
</section>



<section class="processes">
    <h1><?php echo $Acf->get("process_title"); ?></h1>

    <?php foreach($Acf->get("processes") as $idx => $process) : ?>
        <div class="process <?php echo ($idx % 2) === 0 ? "even" : "odd"; ?>">
            <div class="content">
                <h2><?php echo $process['title']; ?></h2>
                <p><?php echo $process['content']; ?></p>
            </div>
            <div class="thumbnail">
                <img src="<?php echo $process['thumbnail']; ?>">
            </div>
        </div>
    <?php endforeach; ?>
</section>




<section class="doing-it-over">
    <h1><?php echo $Acf->get("doing_it_all_over_title"); ?></h1>
    <?php echo $Acf->get("doing_it_all_over_content"); ?>
</section>


