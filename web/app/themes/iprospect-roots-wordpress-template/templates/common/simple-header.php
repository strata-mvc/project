<section class="banner-header" <?php echo $HeaderHelper->generateAttributes(); ?>>
    <div class="media-container">
       <img src="<?php echo $Acf->get("header_background_image"); ?>" alt="" />
    </div>

    <div class="overlay">
        <div class="banner-inner">
            <div class="container">
                <div class="section-content">
                    <div class="banner-content">
                        <h1><?php echo $Acf->get("header_title"); ?></h1>
                        <?php echo $Acf->get("header_content"); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
