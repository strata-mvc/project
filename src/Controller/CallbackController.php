<?php
namespace App\Controller;

class CallbackController extends AppController {

    public static function widgets_init()
    {
        register_nav_menus(array(
            'top_navigation' => __('Top Navigation', PROJECT_KEY),
        ));
    }

}
