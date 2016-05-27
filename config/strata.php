<?php
$strata = array(

    "routes" => array(
        array('POST', '/wp/wp-admin/admin-ajax.php', 'AjaxController'),
        array('GET|POST', '/', 'AppController#home'),
        // array('GET|POST',   '/[*:controller]/[*:action]'),
        // array('GET|POST',   '/[*:controller]/'),
        // array('GET|POST',   '/[.*]',       'AppController#noActionMatch'),
    ),

    "custom-post-types" => array(
    )
);

return $strata;
