<?php
namespace App\View\Helper;

class WordpressHelper extends AppHelper {

    /**
     * Prints a Wordpress nav menu
     * @param  string $name The name of the sidebar as known to Wordpress
     */
    public function printNavMenu($name)
    {
        if (has_nav_menu($name)) {
            wp_nav_menu(array('theme_location' => $name, 'menu_class' => ''));
        }
    }

    public function common($name)
    {
         get_template_part('templates/common/' . $name);
    }

}