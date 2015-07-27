<?php

namespace App\Model\Adapter;

use Strata\Strata;
use Strata\Model\CustomPostType\Query;
use SitePress_EditLanguages;

/**
 * Overrides the default SitePress_EditLanguages constructor
 * because they print html in theirs.
 *
 * Also prevent automated CRUD operations
 */
class EditSitepressLanguagesAdapter  {

    /**
     * Saves a static reference to ensure we use the same instance of the object across
     * each instances of this class
     * @var SitePress_EditLanguages
     */
    static $sitepress;

    public function __construct()
    {
        $this->_assignObjectWithoutPrint();
    }

    public function update_translation($value, $language, $key)
    {
        self::$sitepress->update_translation($value, $language, $key);
    }

    public function insert_translation($name, $language_code, $display_language_code)
    {
        self::$sitepress->insert_translation($name, $language_code, $display_language_code);
    }

    public function delete_language($languageId)
    {
        return self::$sitepress->delete_language($languageId);
    }

    public function get_active_languages()
    {
        self::$sitepress->get_active_languages();
        return self::$sitepress->active_languages;
    }

    public function update_main_table($id, $code, $default_locale, $encode_url, $tag)
    {
        self::$sitepress->update_main_table($id, $code, $default_locale, $encode_url, $tag);
    }

    public function insert_main_table($code, $english_name, $default_locale, $encode_url, $tag)
    {
        if (!empty($code)) {
            self::$sitepress->insert_main_table($code, $english_name, $default_locale, 0, 1, $encode_url, $tag);
            self::$sitepress->insert_flag($code, $code . '.png', 0);
        }
    }

    public function hasALabel($language, $key)
    {
        global $wpdb;
        $hasValuePrep = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_languages_translations WHERE language_code=%s AND display_language_code=%s", $language, $key);
        return (int)$wpdb->get_var($hasValuePrep) > 0;
    }

    private function _getSitepressPath()
    {
        $path = array(Strata::getPluginsPath() . "wpml-multilingual-cms-manual-fork", "menu", "edit-languages.php");
        return implode(DIRECTORY_SEPARATOR, $path);
    }

    private function _includeSitepress()
    {
        ob_start();
        include_once($this->_getSitepressPath());
        ob_end_clean();
    }

    private function _assignObjectWithoutPrint()
    {
        if (!self::$sitepress instanceof SitePress_EditLanguages) {
            $this->_includeSitepress();

            global $icl_edit_languages;
            if (isset($icl_edit_languages) && $icl_edit_languages instanceof SitePress_EditLanguages) {
                self::$sitepress = $icl_edit_languages;
            }
        }
    }
}
