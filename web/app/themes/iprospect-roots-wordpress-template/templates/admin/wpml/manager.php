<h2><?php _e("Locale Manager", PROJECT_KEY); ?></h2>

<?php if (isset($hasUpdated)) : ?>
    <p class="success"><?php _e("The language has been updated.", PROJECT_KEY); ?></p>
<?php endif; ?>

<p><?php _e("Bellow is a list of the active languages on your website."); ?></p>

<div class="menu">
    <button class="custom-languages add button right" name="add"><?php _e("Add new", PROJECT_KEY); ?></button>
</div>

<?php if (isset($languages)) : ?>
    <table class="widefat fixed custom-languages" cellspacing="0">
        <thead>
            <tr>
                <th class="column-columnname language-name"><?php _e("Name", PROJECT_KEY); ?></th>
                <th class="column-columnname language-edit"></th>
                <th class="column-columnname language-delete"></th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 0; foreach ($languages as $key => $language) : $count++; ?>
                <tr <?php if(($count % 2) === 0) : ?>class="alternate"<?php endif; ?>>
                    <td class="column-columnvalue language-name">
                        <?php echo $language["display_name"]; ?>
                    </td>
                    <td class="column-columnvalue language-edit">
                        <button class="edit button" value="<?php echo $key; ?>" name="edit"><?php _e("Edit", PROJECT_KEY); ?></button>
                    </td>
                    <td class="column-columnvalue language-delete">
                        <button class="delete button" value="<?php echo $key; ?>" name="delete"><?php _e("Delete", PROJECT_KEY); ?></button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php else : ?>
    <p>No language has been defined by your website administrator.</p>
<?php endif; ?>

<?php // There is a cache issue that prevents the icon to be auto-loaded fast enough ?>
<div class="spinner"></div>
