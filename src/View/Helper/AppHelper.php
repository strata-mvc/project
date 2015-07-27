<?php
namespace App\View\Helper;

/**
 * This is the base view helper class for your application.
 *  All your custom helper classes should extend this class.
 */
class AppHelper extends \Strata\View\Helper\Helper {

    /**
     * Factories a helper reference to this class
     * @return mixed
     */
    public static function staticFactory()
    {
        $classname = get_called_class();
        return new $classname();
    }

}
