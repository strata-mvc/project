
<section class="mission">
    <h1><?php echo $Acf->get("mission_title"); ?></h1>

    <img src="<?php echo $Acf->get("mission_thumbnail"); ?>">

    <?php echo $Acf->get("mission_content"); ?>

    <a href="<?php echo $Acf->get("mission_link"); ?>">
        <?php echo $Acf->get("mission_link_label"); ?>
    </a>
</section>


<section class="assets">
    <h1><?php echo $Acf->get("assets_title"); ?></h1>

     <?php foreach((array)$Acf->get("asset_repeater") as $idx => $asset) : ?>
        <div class="asset <?php echo ($idx % 2) === 0 ? "even" : "odd"; ?>">
            <div class="content">
                <h2><?php echo $asset['title']; ?></h2>
                <?php echo $asset['content']; ?>
                <span><?php echo $asset['source']; ?></span>
            </div>
            <div class="thumbnail">
                <img src="<?php echo $asset['thumbnail']; ?>">
            </div>
        </div>
    <?php endforeach; ?>


    <a href="<?php echo $Acf->get("meet_the_team_call_to_action_link"); ?>">
        <?php echo $Acf->get("meet_the_team_call_to_action_label"); ?>
    </a>
</section>


<section class="values">
    <h1><?php echo $Acf->get("values_title"); ?></h1>

     <?php foreach((array)$Acf->get("value_repeater") as $idx => $value) : ?>
        <div class="value <?php echo ($idx % 2) === 0 ? "even" : "odd"; ?>">
            <h2><?php echo $value['title']; ?></h2>
            <?php echo $value['content']; ?>
        </div>
    <?php endforeach; ?>
</section>


<section class="responsibilities">
    <h1><?php echo $Acf->get("responsibility_title"); ?></h1>
    <?php echo $Acf->get("responsibility_content"); ?>

    <a href="<?php echo $Acf->get("responsibility_call_to_action_link"); ?>">
        <?php echo $Acf->get("responsibility_call_to_action_label"); ?>
    </a>
</section>
