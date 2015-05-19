<div class="tpl-edit-language">
    <h2><?php _e("Editing"); ?> : "<?php echo $currentLanguage["display_name"]; ?>"</h2>

    <form enctype="multipart/form-data" action="<?php echo admin_url('options-general.php?page=app_manage_locales_options') ?>" method="post" id="icl_edit_languages_form">
        <input type="hidden" name="icl_edit_language[id]" value="<?php echo $currentLanguage['id']; ?>"/>

        <h3><?php _e("Details", PROJECT_KEY); ?></h3>
        <table id="icl_edit_languages_table" class="widefat" cellspacing="0">
            <tbody>
                <tr>
                    <th class="column-columnname">English name</th>
                    <td><input <?php if($WpmlHelper->exists($currentLanguage)) :?>readonly<?php endif; ?> type="text" name="icl_edit_language[english_name]" value="<?php echo $currentLanguage['english_name']; ?>"/></td>
                </tr>
                <tr>
                    <th class="column-columnname">Code</th>
                    <td><input <?php if($WpmlHelper->exists($currentLanguage)) :?>readonly<?php endif; ?> type="text" name="icl_edit_language[code]" value="<?php echo $currentLanguage['code']; ?>"/></td>
                </tr>
                <tr>
                    <th class="column-columnname">Default locale</th>
                    <td><input type="text" name="icl_edit_language[default_locale]" value="<?php echo $currentLanguage['default_locale']; ?>"/></td>
                </tr>
                <tr>
                    <th class="column-columnname">Encode url?</th>
                    <td>
                        <select name="icl_edit_language[encode_url]">
                            <option value="0" <?php if(empty($currentLanguage['encode_url'])): ?>selected="selected"<?php endif;?>><?php _e('No', 'sitepress') ?></option>
                            <option value="1" <?php if(!empty($currentLanguage['encode_url'])): ?>selected="selected"<?php endif;?>><?php _e('Yes', 'sitepress') ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class="column-columnname">Tag</th>
                    <td><input type="text" name="icl_edit_language[tag]" value="<?php echo $currentLanguage['tag']; ?>" /></td>
                </tr>
            </tbody>
        </table>

        <h3><?php _e("Labels", PROJECT_KEY); ?></h3>

        <ul class="field-grid">
            <?php foreach($translations as $translation): ?>
                <li>
                    <span class="lbl"><?php echo $translation['code']; ?></span>
                    <input type="text" name="translations[<?php echo $translation['code']; ?>]" value="<?php echo $WpmlHelper->getTranslationValue($currentLanguage, $translation); ?>" />
                </li>
            <?php endforeach; ?>
        </ul>
    </form>
</div>
