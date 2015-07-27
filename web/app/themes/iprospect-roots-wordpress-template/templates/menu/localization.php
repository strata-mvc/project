<?php
    use App\Model\Region;
    $currentId = get_the_ID();
    $currentPostType = get_post_type();
?>
<section class="localization">

    <div class="current-area">
        <a href="javascript:void(0);" class="localization-menu">
           <span><?php echo $LanguageHelper->getCurrentRegionName(); ?> (<?php echo $LanguageHelper->getCurrentAreaName(); ?>)</span>
           <img width="18" height="18" src="<?php echo THEME_DIRECTORY; ?>/assets/img/icon-arrow-down.svg" class="svg" />
       </a>
    </div>
    <ul class="area-locales">
        <?php foreach($LanguageHelper->getCurrentAreaLocales() as $localeCode => $localeName) : ?>
            <li>
                <a <?php echo ($localeCode == $LanguageHelper->getCurrentAreaName()) ? 'class="active"' : '';?> href="<?php echo $LanguageHelper->getTranslatedUrl($localeCode, $currentId, $currentPostType); ?>"><?php echo $localeName; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

</section>
