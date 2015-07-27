<?php

namespace App\Model\Adapter;

use Strata\Strata;
use Strata\Model\Model;
use Strata\Router\Router;
use Strata\Utility\Inflector;

/**
 * Bundles commands to setup Wordpress for the current project.
 */
class WordpressAdapter  {

    public function removeOriginalAdminLinks()
    {
        remove_menu_page('themes.php');
        remove_menu_page('tools.php');
        remove_menu_page('edit.php?post_type=google_maps');
        remove_menu_page('wpml-string-translation-manual-fork/menu/string-translation.php');
        remove_menu_page('wpml-translation-management-manual-fork/menu/translations-queue.php');
    }

    public function removeEditLinks()
    {
        # Remove the basic edit link because later localized links will likely
        # be used instead.
        foreach ($this->getTranslatableTypes() as $postTypeName => $postType) {
            remove_menu_page('edit.php?post_type=' . $postType->wordpressKey());
        }

        # Remove original post links
        remove_submenu_page('edit.php', 'edit.php');
        remove_submenu_page('edit.php', 'post-new.php');
        remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=category');
        remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=post_tag');

        # Remove original post block
        remove_menu_page('edit.php');
    }

    /**
     * Adds links to the global posts listing of all translatable post types.
     */
    public function addGlobalManagementLinks()
    {
        foreach ($this->getTranslatableTypes() as $postTypeName => $postType) {
            $label = $postType->getLabel();
            $globalLabel = sprintf(__("Global %s"), $label->plural());
            $action = 'app_view_global_' . $postType->wordpressKey();
            $callback = Router::callback("Admin\\GlobalPostManagementController", "globalList");

            add_menu_page($globalLabel, $globalLabel, $postType->permissionLevel, $action, $callback, $postType->getIcon());

            if ($postType->hasTaxonomies()) {
                $this->registerTaxonomyMenu($action, $postType);
            }
        }
    }


    public function addLocalizedEditorLinks($locale)
    {
        foreach ($this->getTranslatableTypes() as $postTypeName => $postType) {
            $label = $postType->getLabel();
            $localizedLabel = sprintf(__("Localized %s"), $label->plural());
            $action = $this->getActionFromType($postType);

// debug($action);
            add_menu_page($localizedLabel, $localizedLabel, $postType->permissionLevel, $action, null, $postType->getIcon());
            remove_submenu_page($action, 'post-new.php?post_type=' . $postType->wordpressKey());
        }
    }

    public function addLocalizationTools($callback)
    {
        foreach ($this->getTranslatableTypes() as $postTypeName => $postType) {
            add_meta_box('amnet-language-metabox', __('Locales', PROJECT_KEY), $callback, $postType->wordpressKey());
        }
    }

    /**
     * Add a link to the custom, simpler, management form for WPML languages.
     */
    public function addLocaleManagementLinks()
    {
        $callback = Router::callback("Admin\\WpmlManagementController", "customLanguageManager");
        add_menu_page(__('All Locales', PROJECT_KEY), __('Locales', PROJECT_KEY), 'manage_options', 'app_manage_locales_options', $callback, 'dashicons-admin-site', 10);
        add_submenu_page('app_manage_locales_options', __('All Locales', PROJECT_KEY), __('All Locales', PROJECT_KEY), 'manage_options', 'app_manage_locales_options', $callback);
        add_submenu_page('app_manage_locales_options', __('Locale Association', PROJECT_KEY), __('Locale Association', PROJECT_KEY), 'manage_options', 'app_manage_region_locales', Router::callback("Admin\\RegionManagementController", "associateRegions"));
    }

    // Remove the SEO meta boxes from all the custom post types
    public function removeSEOGarbage()
    {
        foreach ($this->getTranslatableTypes() as $postTypeName => $postType) {
            remove_meta_box('wpseo_meta', $postType->wordpressKey(), 'normal');
        }
    }

    public function requireJqueryUi()
    {
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
    }

    public function requireAdminStylesAndScript()
    {
        wp_register_style('amnet-admin-style', get_template_directory_uri() . '/assets/css/admin.css');
        wp_enqueue_style('amnet-admin-style');

        wp_register_script('amnet-admin-js', get_template_directory_uri() . '/assets/js/admin.js');
        wp_enqueue_script('amnet-admin-js');

        // Add javascript config variables to modernizr load script
        $config = array(
            'bower' => get_template_directory_uri() . '/assets/js/bower_components/',
            'plugins' => get_template_directory_uri() . '/assets/js/plugins/',
            'js' => get_template_directory_uri() . '/assets/js/',
            'lang' => ICL_LANGUAGE_CODE,
            'ajaxurl' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce(AJAX_NONCE_KEY)
        );
        wp_localize_script('amnet-admin-js', 'WpConfig', $config);
    }

    public function registerTranslatableTypeViewEditCallback($callback)
    {
        foreach ($this->getTranslatableTypes() as $postTypeName => $postType) {
            add_action('views_edit-' . $postType->wordpressKey(), $callback);
        }
    }

    private function registerTaxonomyMenu($parentAction, $postType)
    {
        $label = $postType->getLabel();
        $globalLabel = sprintf(__("Global %s categories"), $label->plural());
        $action = 'app_view_global_tax_' . $postType->wordpressKey();
        $callback = Router::callback("Admin\\GlobalTaxonomyManagementController", "globalList");

        add_submenu_page($parentAction, $globalLabel, $globalLabel, $postType->permissionLevel, $action, $callback);
    }

    /**
     * Allows the automation of known types
     * @return array name, key array of post types
     */
    private function getTranslatableTypes()
    {
        $cpts = array();

        foreach (Strata::config("custom-post-types") as $customPostType) {
            $cptObject = Model::factory($customPostType);
            $name = strtolower($cptObject->getShortName());
            $cpts[$name] = $cptObject;
        }

        $cpts["page"] = Model::factory("Page");
        $cpts["post"] = Model::factory("Post");

        return $cpts;
    }

    private function getActionFromType($cpt)
    {
        $key = $cpt->wordpressKey();

        if ($key === "post") {
            return 'edit.php';
        }

        return 'edit.php?post_type=' . $key;
    }
}
