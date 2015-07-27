<?php

define('THEME_DIRECTORY', get_bloginfo('stylesheet_directory'));

add_theme_support(\IP\Security\Security::LOGIN_SECURITY);
add_theme_support(\IP\GoogleTagManager\PluginInitializer::GTM_VIEW_HELPER);
