<?php
namespace App\Model;

class Career extends AppCustomPostType {
    /**
     * {@inheritdoc}
     */
    public $configuration = array(
        "supports"  => array('title', 'editor'),
        'publicly_queryable' => true,
        "rewrite"   => array(
            'slug'                => 'career',
            'with_front'          => false,
        ),
        "menu_icon" => "dashicons-businessman"
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

    /**
     * Fetches the top most recent careers
     * @param  int $quantity
     * @return array
     */
    public static function top($quantity)
    {
        return self::repo()
            ->status("publish")
            ->limit((int)$quantity)
            ->orderby("creation_date")
            ->direction("DESC")
            ->fetch();
    }
}
