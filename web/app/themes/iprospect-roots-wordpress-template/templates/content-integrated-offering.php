


<section class="dan">
    <h1><?php echo $Acf->get("dan_header"); ?></h1>
    <img src="<?php echo $resource['dan_thumbnail']; ?>">
</section>



<section class="resources">
    <h1><?php echo $Acf->get("network_resources_title"); ?></h1>
    <?php foreach((array)$Acf->get("network_resources_repeater") as $idx => $resource) : ?>
        <div class="resource <?php echo ($idx % 2) === 0 ? "even" : "odd"; ?>">
            <div class="content">
                <h2><?php echo $resource['title']; ?></h2>
                <p><?php echo $resource['content']; ?></p>
            </div>
            <div class="thumbnail">
                <img src="<?php echo $resource['thumbnail']; ?>">
            </div>
        </div>
    <?php endforeach; ?>
</section>


