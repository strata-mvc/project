<?php
namespace App\Model;

use App\Model\Wpml;

/**
 * Handles user management.
 */
class Editor extends AppModel {

    /**
     * Gets the pairing between the locales and the current user
     * @return array
     */
    public static function getLanguagePairs()
    {
        global $wpdb;
        return get_user_meta(get_current_user_id(), "{$wpdb->prefix}language_pairs", true);
    }

    /**
     * Forces the either the default language or the first matching
     * locale linked to the current user.
     */
    public static function forceFirstAvailableLanguage()
    {
        if (self::canWriteInDefaultLocale()) {
            Wpml::setLanguage(Wpml::getDefaultLanguage());
        } else {
            Wpml::setLanguage(self::getFirstAvailableLocale());
        }
    }

    /**
     * Returns the languages the current user may translation to, from an optional
     * source language parameter.
     * @param  string $srcLanguage The language of the original post. Defaults to current language
     * @return array A list of languages
     */
    public static function getTranslationsPossibilities($srcLanguage = null)
    {
        if (is_null($srcLanguage)) {
            $srcLanguage = Wpml::getDefaultLanguage();
        }

        $allTranslations = Wpml::getUserTranslationCapabilities(get_current_user_id());

        if (array_key_exists($srcLanguage, $allTranslations)) {
            $translations = array();
            foreach ($allTranslations[$srcLanguage] as $key => $val) {
                if ($key != "") {
                    $translations[] = $key;
                }
            }
            return $translations;
        }

        return array();
    }

    /**
     * Pops the locale code from the list of available translations.
     * @return string|null locale code, null if not found
     */
    public static function getFirstAvailableLocale()
    {
        $locales = self::getTranslationsPossibilities();
        if (count($locales) > 0) {
            return $locales[0];
        }
    }

    /**
     * Returns whether or not the current user can edit objects in the default locale.
     * @return bool True if is allowed
     */
    public static function canWriteInDefaultLocale()
    {
        return self::canWriteIn(Wpml::getDefaultLanguage());
    }

    /**
     * Returns whether or not the current user can edit objects in the specified locale.
     * @param string Locale code
     * @return bool True if is allowed
     */
    public static function canWriteIn($locale)
    {
        if (current_user_can("administrator")) {
            return true;
        }

        return in_array($locale, self::getTranslationsPossibilities());
    }
}
