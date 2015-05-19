<div class="tpl-edit-language">

    <h2>"<?php echo $currentLanguage["display_name"]; ?>"</h2>

    <p><?php _e("Are you sure you wish to delete this locale?"); ?></p>
    <p><?php _e("It is not a reversible operation."); ?></p>

    <form enctype="multipart/form-data" action="<?php echo admin_url('options-general.php?page=app_manage_locales_options&action=delete') ?>" method="post" id="icl_edit_languages_form">
        <input type="hidden" name="icl_edit_language[id]" value="<?php echo $currentLanguage['id']; ?>"/>
    </form>
</div>
