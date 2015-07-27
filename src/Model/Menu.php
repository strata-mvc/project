<?php
namespace App\Model;

class Menu extends AppCustomPostType  {

    /**
     * {@inheritdoc}
     */
    public $configuration = array(
        "supports"  => array('title'),
        "menu_icon" => "dashicons-networking"
    );

    public static function getMainNavigationId()
    {
        return 206;
    }

    public static function getTopNavigationId()
    {
        return 262;
    }

    public static function getFooterNavigationId()
    {
        return 466;
    }

    public static function getBtmFooterNavigationId()
    {
        return 524;
    }

    public static function getMainNavigation()
    {
        $obj = self::staticFactory();
        return $obj->findById(self::getMainNavigationId());
    }

    public static function getTopNavigation()
    {
        $obj = self::staticFactory();
        return $obj->findById(self::getTopNavigationId());
    }

    public static function getFooterNavigation()
    {
        $obj = self::staticFactory();
        return $obj->findById(self::getFooterNavigationId());
    }

    public static function getBtmFooterNavigation()
    {
        $obj = self::staticFactory();
        return $obj->findById(self::getBtmFooterNavigationId());
    }
}
