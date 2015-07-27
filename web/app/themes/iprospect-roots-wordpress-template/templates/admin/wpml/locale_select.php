<?php _e("View in locale", PROJECT_KEY); ?> :

<select name="wpml-language-switch" id="wpml-language-switch" class="postform">
    <?php if (isset($defaultLanguage)) : ?>
        <option value="?post_type=<?php echo $postType; ?>&amp;lang=<?php echo $defaultLanguage; ?>" <?php if ($currentLanguage == $defaultLanguage) :?>selected="selected"<?php endif; ?>><?php echo $defaultLanguage; ?></option>
    <?php endif; ?>
    <?php foreach($availableLocales as $locale) : ?>
        <option value="?post_type=<?php echo $postType; ?>&amp;lang=<?php echo $locale; ?>" <?php if ($currentLanguage == $locale) :?>selected="selected"<?php endif; ?>><?php echo $locale; ?></option>
    <?php endforeach; ?>
</select>
