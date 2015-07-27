<?php
    use App\Model\Region;
    $currentId = get_the_ID();
    $currentPostType = get_post_type();
?>
<header class="top-header clearfix" role="banner">
        <div class="top-nav">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <?php echo \Strata\View\Template::parse("menu/localization", array("LanguageHelper" => $LanguageHelper)); ?>
                    </div>
                    <div class="col-md-6">
                        <nav class="nav-top" role="navigation">
                            <?php echo $MenuHelper->renderTopNavigation();   ?>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="region-locales clearfix" role="menu" aria-labelledby="localization-menu" style="display:none;">

            <div class="col-grey">
                <h3><?php _e("<strong>260 Experts</strong> who lead the programmatic industry and push its boundaries in <strong>30 Countries</strong>", PROJECT_KEY); ?></h3>
            </div>  
            <div class="col-white">  
            <?php if ($LanguageHelper->hasRegions()) : ?>
                <?php foreach ($LanguageHelper->getRegions() as $region) : ?>
                    <div class="row">
                    <h4><?php echo $region->post_title; ?>.</h4>
                    <?php foreach ($LanguageHelper->getRegionAreas($region->ID) as $areaIdx => $area) : ?>
                        <div class="col">
                            <h5><?php echo $area; ?></h5>
                            <ul>
                            <?php foreach ($LanguageHelper->getAreaLocales($region, $areaIdx) as $localeCode => $localeDisplayName) : ?>
                                <li><a href="<?php echo $LanguageHelper->getTranslatedUrl($localeCode, $currentId, $currentPostType); ?>?region=<?php echo $region->ID; ?>&amp;area=<?php echo $areaIdx; ?>"><?php echo $localeDisplayName; ?></a></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach ; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div> 
            
        </div>

        <div class="main-nav-container">
            <div class="container">
                    <div class="brand">
                        <a title="<?php bloginfo('name'); ?>" href="<?php echo home_url('/') ?>">
                            <?php echo (is_front_page()) ?  "<h1>" : ""; ?>
                                <img width="192" height="37" alt="<?php bloginfo('name'); ?>" src="<?php bloginfo('stylesheet_directory'); ?>/assets/img/amnet-logo.svg" />
                            <?php echo (is_front_page()) ?  "</h1>" : ""; ?>
                        </a>
                    </div>
                    <div class="nav">
                        <nav class="main-nav" role="navigation">
                            <?php echo $MenuHelper->renderMainNavigation();   ?>
                        </nav>
                    </div>
            </div>
        </div>
</header>
