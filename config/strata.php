<?php
$strata = array(

    "routes" => array(
        array('GET|POST',   '/[.*]',       'AppController#index'),
        // Here are example values:
        // array('GET|POST',   '/participate/volunteer/',     'VolunteersController#create'),
        // array('POST',       '/wp-admin/admin-ajax.php',    'AjaxController#index'),
        // array('GET',        '/schools/[*:slug]/',  'SchoolsController#view'),
    ),

    // Automate the creation of backend based post types.
    // Added models your app will use that are based on the MVC CustomPostType\Entity class.
    "custom-post-types" => array(
      // Exemples:
      //  "School",
      //  "Volunteer",
    )
);

// Additionally, you can manipulate the array further before returning it.

return $strata;
