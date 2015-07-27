<?php if (isset($duplicateId) && $duplicateId > 0) : ?>
    <p><?php _e("Being forwarded to the new post...", PROJECT_KEY); ?></p>
    <script type="text/javascript">
        window.location.assign("/wp/wp-admin/post.php?post=<?php echo $duplicateId; ?>&action=edit&lang=<?php echo $duplicateLanguage; ?>");
    </script>
<?php else : ?>
    <p><?php _e("There was a problem while creating the duplicate.", PROJECT_KEY); ?></p>
<?php endif; ?>
