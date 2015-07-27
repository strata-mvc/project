<?php
namespace App\Model;

use Strata\Strata;
use Strata\Model\CustomPostType\Query;

use App\Model\Adapter\EditSitepressLanguagesAdapter;
use App\Model\Adapter\TranslationManagementAdapter;

/**
 * Bridges recurring language operations
 */
class Wpml extends AppModel {

    /**
     * Creates an empty shell of a Wpml instance
     * @return array
     */
    public static function create()
    {
        return array(
            'id'                => '',
            'display_name'      => __('New locale', PROJECT_KEY),
            'english_name'      => '',
            'code'              => '',
            'default_locale'    => '',
            'encode_url'        => '',
            'tag'               => ''
        );
    }

    /**
     * Removes the language links injected by the WPML plugin
     */
    public static function removeAdminLanguageFilter()
    {
        global $sitepress;
        remove_action('admin_footer', array($sitepress, 'language_filter'));
    }

    /**
     * Lists the active languages on the website.
     * @return array
     */
    public static function findAllActive()
    {
        global $sitepress;
        return $sitepress->get_active_languages(true);
    }

    /**
     * Returns the list of languages a user can modify.
     * @param  int $userId
     * @return array
     */
    public static function getUserTranslationCapabilities($userId)
    {
        global $wpdb;
        return get_user_meta($userId, "{$wpdb->prefix}language_pairs", true);
    }

    /**
     * Lists the translation information for the current post.
     * @return array
     */
    public static function getCurrentPostTranslations()
    {
        return self::getPostTranslations(get_the_ID(), get_post_type());
    }

    /**
     * Lists the translation information for the post id.
     * @param  int $postId
     * @param  string $postType A Wordpress post type.
     * @return array
     */
    public static function getPostTranslations($postId, $postType)
    {
        global $sitepress;
        $completeType = 'post_' . $postType;
        $data = $sitepress->get_element_translations(self::getTrid($postId, $completeType), $completeType);

        if (is_null($data)) {
            return array();
        }

        return $data;
    }

    /**
     * Lists the translation information for the term id
     * @param  int $termId
     * @param  string $type   A Wordpress taxonomy type
     * @return array
     */
    public static function getTermTranslations($termId, $type)
    {
        global $sitepress;

        $completeType = 'tax_' . $type;
        $data = $sitepress->get_element_translations(self::getTrid($termId, $completeType), $completeType);

        if (is_null($data)) {
            return array();
        }

        return $data;
    }

    /**
     * Returns the url for the current state by language.
     * @param  string $languageCode
     * @return string
     */
    public static function getLanguageUrl($languageCode)
    {
        global $sitepress;
        return $sitepress->language_url($languageCode);
    }

    /**
     * The mysterious trid likely means "translation id" but it seems
     * to be created internally by WPML using their won set of rules.
     * @return integer trid value
     */
    public static function getTrid($postId = null, $type)
    {
        if (is_null($postId)) {
            $postId = get_the_ID();
        }

        global $sitepress;
        $details = $sitepress->get_element_language_details($postId, $type);

        if ($details) {
            return $details->trid;
        }

        return $postId;
    }

    /**
     * Returns the app's current default language
     * @return [type] [description]
     */
    public static function getDefaultLanguage()
    {
        global $sitepress;
        return $sitepress->get_default_language();
    }

    /**
     * Localizes a query's query object for the required language
     * @param  Query  $query
     * @param  string $language
     * @return Query
     */
    public static function localizedQuery(Query $query, $language)
    {
        global $sitepress;
        $original = self::getCurrentLanguage();

        $sitepress->switch_lang($language);
        $result = $query->query();
        $sitepress->switch_lang($original);

        return $result;
    }

    /**
     * Localizes a query's fetch object for the required language
     * @param  Query  $query
     * @param  string $language
     * @return array
     */
    public static function localizedFetch(Query $query, $language)
    {
        global $sitepress;
        $original = self::getCurrentLanguage();

        $sitepress->switch_lang($language);
        $result = $query->fetch();
        $sitepress->switch_lang($original);

        return $result;
    }

    /**
     * ICL_LANGUAGE_CODE works well enough in the frontend. However, in
     * the backend we need to ask sitepress directly. Therefore this function
     * is not a duplicate to the global variable.
     * @return string Type
     */
    public static function getCurrentLanguage()
    {
        global $sitepress;
        return $sitepress->get_current_language();
    }

    /**
     * Returns the language configuration data from WPML's internals
     * @param  string $language
     * @return array|null
     */
    public static function getLanguageDetails($language)
    {
        $adapter = new EditSitepressLanguagesAdapter();
        $activeLanguages = $adapter->get_active_languages();

        foreach ($activeLanguages as $activeLanguage) {
            if ($activeLanguage["code"] == $language) {
                return $activeLanguage;
            }
        }
    }

    /**
     * Lists the associated translation of the current language
     * @param  string $language The source language
     * @return array
     */
    public static function getLanguageTranslations($language)
    {
        global $sitepress;
        $translations = array();

        foreach (self::findAllActive() as $lang_translation) {
            $translations[] = array (
                "id" => $lang_translation['id'],
                "code" => $lang_translation['code'],
                "display_name" => $sitepress->get_display_language_name($language, $lang_translation['code'])
            );
        }

        return $translations;
    }

    /**
     * Returns whether or not a language and a label in the specified language.
     * @param  string  $language
     * @param  string  $key      the translation language code
     * @return boolean
     */
    public static function hasALabel($language, $key)
    {
        $adapter = new EditSitepressLanguagesAdapter();
        return $adapter->hasALabel($language, $key);
    }

    /**
     * Updates the translated label of a language in WPML.
     * @param  string $language the source language
     * @param  string $key      the translation language code
     * @param  string $value    the value of the translation.
     */
    public static function updateLabel($language, $key, $value)
    {
        $adapter = new EditSitepressLanguagesAdapter();
        $adapter->update_translation($value, $language, $key);
    }

    /**
     * Insert the translated label of a language in WPML.
     * @param  string $language the source language
     * @param  string $key      the translation language code
     * @param  string $value    the value of the translation.
     */
    public static function insertLabel($language, $key, $value)
    {
        $adapter = new EditSitepressLanguagesAdapter();
        $adapter->insert_translation($value, $language, $key);
    }

    public static function deleteLanguage($languageId)
    {
        $adapter = new EditSitepressLanguagesAdapter();
        $adapter->delete_language($languageId);
    }

    public static function duplicateInLocale($postId, $language)
    {
        $adapter = new TranslationManagementAdapter();
        return $adapter->duplicatePost($postId, $language);
    }

    /**
     * Sitepress needs to refresh its cache after updating with the labels.
     */
    public static function resetCache()
    {
        global $sitepress;
        $sitepress->icl_translations_cache->clear();
        $sitepress->icl_locale_cache->clear();
        $sitepress->icl_flag_cache->clear();
        $sitepress->icl_language_name_cache->clear();
        delete_option('_icl_cache');
    }

    /**
     * Updates language information
     * @param  array $data
     */
    public static function updateLanguage($data)
    {
        $adapter = new EditSitepressLanguagesAdapter();
        $adapter->update_main_table($data["id"], $data["code"], $data["default_locale"], $data["encode_url"], $data["tag"]);
    }

    /**
     * Inserts language information
     * @param  array $data
     */
    public static function insertLanguage($data)
    {
        $adapter = new EditSitepressLanguagesAdapter();
        $adapter->insert_main_table($data["code"], $data["english_name"], $data["default_locale"], $data["encode_url"], $data["tag"]);
    }

    /**
     * Gets the url to the translated version of the url
     * @param  string $urlKey The source url
     * @return string         Translated url
     */
    public static function getPageUrl($urlKey)
    {
        $post = get_page_by_path($urlKey);
        if (!is_null($post)) {
            $translationId = icl_object_id($post->ID , 'page', true, ICL_LANGUAGE_CODE);
            return get_the_permalink($translationId);
        }

        return "";
    }

    /**
     * Sets the WPML language to a new locale
     * @param string $locale
     */
    public static function setLanguage($locale)
    {
        global $sitepress;
        $sitepress->switch_lang($locale);
    }

}
