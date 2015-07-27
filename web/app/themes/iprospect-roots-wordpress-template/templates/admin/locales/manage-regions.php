<div class="manage-regions">
    <h2><?php _e("Locale Association", PROJECT_KEY); ?></h2>
    <p><?php _e("Drag and drop the locales on the right in the gray boxes under regions to enable the locale."); ?></p>

    <form method="post" action="<?php echo admin_url('admin.php?page=app_manage_region_locales') ?>" class="col region-associations">
        <?php if (count($regions) > 0) : ?>
            <?php foreach ($regions as $region) : ?>
                <h3><?php echo $region->post_title; ?></h3>
                <?php $idx = 0; foreach ($Acf->get("region_and_locales_map", $region->ID) as $subregion) : ?>
                    <h4><?php echo $subregion["subregions"]; ?></h4>
                    <div class="drop" data-region-slug="<?php echo $region->post_name; ?>" data-subregion-idx="<?php echo $idx; ?>">
                        <?php if (array_key_exists($region->post_name, $regionMap) && count($regionMap[$region->post_name]) > $idx) : ?>
                            <?php foreach ($regionMap[$region->post_name][$idx] as $localeCode => $localeDisplayName) : ?>
                                <div class="locale-pill">
                                    <span class="close">&times; </span>
                                    <span><?php echo $localeDisplayName; ?></span>
                                    <input type="hidden" name="regionMap[<?php echo $region->post_name; ?>][<?php echo $idx; ?>][<?php echo $localeCode; ?>]" value="<?php echo $localeDisplayName; ?>" />
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php $idx++; endforeach ; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <input type="submit" class="button" name="save" value="<?php _e("Save"); ?>" />
    </form>

    <div class="col">
        <h3><?php _e("Available locales", PROJECT_KEY); ?></h3>
        <div>
            <?php foreach ($languages as $locale) : ?>
                <div class="drag locale-pill">
                    <input type="hidden" name="regionMap[][0][<?php echo $locale['code']; ?>]" value="<?php echo $locale['display_name']; ?>" />
                    <span><?php echo $locale['display_name']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
