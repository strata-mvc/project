<?php
namespace App\Model;

class Client extends AppCustomPostType {

    public $configuration = array(
        "supports"  => array('title', 'editor', 'thumbnail'),
        "menu_icon" => "dashicons-universal-access"
    );

}
