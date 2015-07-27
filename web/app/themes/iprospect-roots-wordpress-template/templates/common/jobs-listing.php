<?php
    use \App\Model\Career;
?>

<?php $bgImg =  $Acf->get("careers_bg_image"); ?>
<section class="careers">
    <div class="banner-title" <?php echo ($bgImg) ? 'style="background-image:url('.$bgImg.')"' : ''; ?>>
        <div class="container">
            <h2 class="section-title"><?php echo $Acf->get("careers_title"); ?></h2>
            <?php echo $Acf->get("careers_text"); ?>
        </div>
    </div>
    <div class="job-listing">
        <?php $careers = Career::top((int)$Acf->get("career_quantity")); ?>
        <?php if (count($careers) > 0) : ?>
            <ul>
            <?php foreach ($careers as $idx => $career) : ?>
                <li>
                    <a href="<?php echo get_the_permalink($career->ID); ?>"><?php echo $career->post_title; ?></a>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p><?php _e("There are no open career opportunities for moment.", PROJECT_KEY); ?></p>
        <?php endif; ?>
    </div>
    <div class="btm-actions">
        <a class="btn" href="<?php echo Career::getBaseUrl(); ?>"><?php echo $Acf->get("career_call_to_action"); ?></a>
    </div>
</section>
