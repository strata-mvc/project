<?php
$strata = array(
    "routes" => array(
        array('POST',       '/wp/wp-admin/admin-ajax.php',  'AjaxController'),
        array('GET|POST',   '/[.*]',       'AppController#index'),
    ),

    "custom-post-types" => array(
        "Region",
        "News",
        "Career",
        "Menu",
        "TeamMember",
        "Client",
        "CaseStudy",
    ),
);

return $strata;
