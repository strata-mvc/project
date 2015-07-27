<h2><?php echo sprintf(__("Global %s", PROJECT_KEY), $label->plural()); ?></h2>

<?php if (isset($languages)): ?>
    <?php if (!count($languages)) : ?>
        <p><?php echo sprintf(__("No languages have been assigned to your profile and therefore you are not allowed to translate %s.", PROJECT_KEY), $label->plural()); ?></p>
    <?php endif; ?>
<?php endif; ?>
<p><?php echo sprintf(__("The following is the list of possible translations you may modify for each %s. Missing translations are marked with a *.", PROJECT_KEY), $label->plural()); ?></p>

<?php if (count($posts)) : ?>
    <table class="wp-list-table widefat fixed striped posts amnet-global-posts">
        <thead>
            <tr>
                <th scope="col" class="manage-column"><?php _e($label->plural(), PROJECT_KEY); ?></th>
                <th scope="col" class="manage-column"><?php _e("Locales", PROJECT_KEY); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($posts as $post) :
            $WpmlHelper->translations = $post->translations; ?>
            <tr>
                <td>
                    <?php echo $post->post_title; ?>
                </td>
                <td>
                    <?php foreach ($languages as $language) : ?>
                        <a href="<?php echo $WpmlHelper->editOrCreate($language); ?>" <?php echo $WpmlHelper->generateAttributes($language, $post->ID); ?>>
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

    <?php echo $PaginationHelper->render(); ?>

<?php else : ?>
    <p><?php echo sprintf(__("No %s found.", PROJECT_KEY), $label->plural()); ?></p>
<?php endif; ?>


<?php // There is a cache issue that prevents the icon css to be auto-loaded fast enough ?>
<div class="spinner"></div>

