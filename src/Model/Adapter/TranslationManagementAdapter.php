<?php

namespace App\Model\Adapter;

use Strata\Strata;

/**
 * Overrides the default SitePress_EditLanguages constructor
 * because they print html in theirs.
 *
 * Also prevent automated CRUD operations
 */
class TranslationManagementAdapter  {

    /**
     * Saves a static reference to ensure we use the same instance of the object across
     * each instances of this class
     * @var SitePress_EditLanguages
     */
    static $translationManagement;

    public function __construct()
    {
        $this->_assignObject();
    }

    public function duplicatePost($postId, $language)
    {
        // Duplicate the post
        $duplicateId = self::$translationManagement->make_duplicate($postId, $language);

        // But unlink it form the original
        self::$translationManagement->reset_duplicate_flag($duplicateId);

        return $duplicateId;
    }

    private function _getTranslationManagementPath()
    {
        $path = array(Strata::getPluginsPath() . "wpml-multilingual-cms-manual-fork", "inc", "translation-management", "translation-management.class.php");
        return implode(DIRECTORY_SEPARATOR, $path);
    }

    private function _includeTranslationManagement()
    {
        ob_start();
        include_once($this->_getTranslationManagementPath());
        ob_end_clean();
    }

    private function _assignObject()
    {
        if (!self::$translationManagement instanceof \TranslationManagement) {
            $this->_includeTranslationManagement();

            self::$translationManagement = new \TranslationManagement();
        }
    }
}

