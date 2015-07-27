<h2><?php echo sprintf(__("Global %s categories", PROJECT_KEY), $label->plural()); ?></h2>

<?php if (isset($languages)): ?>
    <?php if (!count($languages)) : ?>
        <p><?php echo sprintf(__("No languages have been assigned to your profile and therefore you are not allowed to translate %s categories.", PROJECT_KEY), $label->plural()); ?></p>
    <?php endif; ?>
<?php endif; ?>
<p><?php echo sprintf(__("The following is the list of possible translations you may modify for each %s categories. Missing translations are marked with a *.", PROJECT_KEY), $label->singular()); ?></p>

<?php if (count($taxonomies)) : ?>

    <?php foreach ($taxonomies as $taxonomy) : ?>

        <table class="wp-list-table widefat fixed striped posts amnet-global-taxonomies">
            <thead>
                <tr>
                    <th scope="col" class="manage-column"><?php $taxonomy->getName(); ?></th>
                    <th scope="col" class="manage-column"><?php _e("Locales", PROJECT_KEY); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($taxonomy->terms as $term) :
                    $WpmlHelper->object = $term;
                    $WpmlHelper->translations = $term->translations;
                ?>
                <tr>
                    <td>
                        <?php echo $term->name; ?>
                    </td>
                    <td>
                        <?php foreach ($languages as $language) : ?>
                            <a href="<?php echo $WpmlHelper->editOrCreateTaxonomy($language); ?>" <?php echo $WpmlHelper->generateAttributes($language, $term->term_id); ?>>
                                <?php echo $language; ?>
                            </a>
                            <?php if (isset($defaultLanguage) && $language == $defaultLanguage) : ?>
                                (<?php _e("Original", PROJECT_KEY); ?>)
                            <?php endif; ?>

                            <?php if (!$WpmlHelper->hasTranslation($language)) : ?>
                                *
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    <?php endforeach; ?>

<?php else : ?>
    <p><?php echo sprintf(__("No %s found.", PROJECT_KEY), $label->plural()); ?></p>
<?php endif; ?>


<?php // There is a cache issue that prevents the icon css to be auto-loaded fast enough ?>
<div class="spinner"></div>

