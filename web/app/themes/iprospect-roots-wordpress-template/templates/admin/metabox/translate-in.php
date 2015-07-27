<?php if (isset($duplicateOf)) : ?>
    <?php $duplicateInfo = $duplicateOf->post_title; ?>
    <?php if (\App\Model\Editor::canWriteInDefaultLocale()) : ?>
        <?php $duplicateInfo = sprintf('<a href="%s">%s</a>', $WpmlHelper->editOrCreate($defaultLanguage), $duplicateOf->post_title); ?>
    <?php endif; ?>


    <?php if ($defaultLanguage != ICL_LANGUAGE_CODE) : ?>
        <p><?php echo sprintf(__("This page is a duplicate of '%s', originally written in '%s'."), $duplicateInfo, $defaultLanguage); ?></p>
    <?php else : ?>
        <p><?php _e("This is the original version of the post.", PROJECT_KEY); ?></p>
    <?php endif; ?>
<?php endif; ?>

<?php if (isset($invalidStatus)) : ?>
    <p><?php _e("The current status of this post does not allow translations.", PROJECT_KEY); ?></p>
<?php endif; ?>

<?php if (isset($languages)): ?>
    <?php if (count($languages)) : ?>
        <p><?php _e("The following is the list of possible translations you may modify for the current post. Missing translations are marked with a *."); ?></p>

        <ul>
            <?php foreach ($languages as $language) : ?>
                <li>
                    <?php if (ICL_LANGUAGE_CODE != $language) : ?>
                        <a href="<?php echo $WpmlHelper->editOrCreate($language); ?>" <?php echo $WpmlHelper->generateAttributes($language, get_the_ID()); ?>>
                    <?php else : ?>
                        <a href="#" class="button">
                    <?php endif; ?>
                        <?php echo $language; ?>
                    </a>

                    <?php if (isset($defaultLanguage) && $language == $defaultLanguage) : ?>
                        (<?php _e("Original", PROJECT_KEY); ?>)
                    <?php endif; ?>

                    <?php if (!$WpmlHelper->hasTranslation($language) && (ICL_LANGUAGE_CODE != $language)) : ?>
                        *
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p><?php _e("No languages have been assigned to your profile and therefore you are not allowed to translate posts.", PROJECT_KEY); ?></p>
    <?php endif; ?>

<?php endif; ?>
