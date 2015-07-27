<div class="tpl-duplicate-locale-post">
    <?php if (isset($post) && !is_null($post)) : ?>

        <h2><?php _e("Translate post"); ?></h2>
        <h3>"<?php echo $post->post_title; ?>"</h3>

        <p><?php _e("Are you sure you wish to duplicate this entry's original content?"); ?></p>
        <p><?php echo sprintf(__("It will create a copy of the content and associated files to the %s locale."), "<strong>" . $translateTo["display_name"] . "</strong>"); ?></p>

        <form enctype="multipart/form-data" action="<?php echo admin_url('admin.php?page=app_view_global_'. $post->post_type.'&action=duplicate') ?>" method="post">
            <input type="hidden" name="language" value="<?php echo $translateTo['code']; ?>"/>
            <input type="hidden" name="postId" value="<?php echo $post->ID; ?>"/>
        </form>
    <?php endif ?>
</div>
