<?php
namespace App\Model;

class News extends AppCustomPostType {

    /**
     * {@inheritdoc}
     */
    public $configuration = array(
        "supports"  => array('title', 'editor', 'thumbnail'),
        'publicly_queryable' => true,
        "rewrite"   => array(
            'slug'                => 'news',
            'with_front'          => false,
        ),
        "menu_icon" => "dashicons-welcome-write-blog"
    );

    /**
     * {@inheritdoc}
     */
    public $permissionLevel = "edit_posts";

    /**
     * {@inheritdoc}
     */
    public static function getBaseUrl()
    {
        $obj = self::staticFactory();
        return $obj->configuration["rewrite"]["slug"] . "/";
    }

}
