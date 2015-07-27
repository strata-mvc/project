<?php
namespace App\View\Helper;

use App\Model\Taxonomy\AppTaxonomy;

class WpmlHelper extends AppHelper {

    public $translations;
    public $model;
    public $wp_object;

    /**
     * When a WPML object is being edited in a form, one may need to
     * check if a field is readonly or not.
     * @param array wpml A Wpml data container
     * @return boolean True if is required
     */
    public function exists($wpml = array())
    {
        return (int)$wpml['id'] > 0;
    }

    /**
     * Return the translation value of a language based on the
     * context during the creation in the backend.
     * @param  array $currentLanguage
     * @param  array $translation
     * @return string
     */
    public function getTranslationValue($currentLanguage, $translation)
    {
        if ($this->exists($currentLanguage)) {
            return stripslashes_deep($translation['display_name']);
        }
        return "";
    }


    public function editOrCreate($language)
    {
        if ($this->hasTranslation($language)) {
            return $this->model->generateAdminEditLink($this->getTranslation($language), $language);
        }

        return "#"; // will be ajaxified
    }


    public function editOrCreateTaxonomy($language)
    {
        if ($this->hasTranslation($language)) {
            return AppTaxonomy::generateAdminEditLink($this->wp_object->taxonomy, $this->getTranslation($language), $language);
        }

        return "#"; // will be ajaxified
    }


    public function hasTranslation($language)
    {
        if (!is_null($this->translations)) {
            $translationKeys = array_keys($this->translations);
            return in_array($language, $translationKeys);
        }

        return false;
    }

    public function getTranslation($language)
    {
        return $this->translations[$language];
    }

    public function generateAttributes($language = null, $postId = null)
    {
        $classes = array("button");
        $returnHtml = "";

        if ($this->hasTranslation($language)) {
            $classes[] = "translated";
        } else {
            $classes[] = "untranslated";
        }

        $returnHtml .= 'class="'.implode(" ", $classes).'"';

        if (!is_null($postId)) {
            $returnHtml .= " data-pid=\"$postId\"";
        }

        if (!is_null($language)) {
            $returnHtml .= " data-lang=\"$language\"";
        }

        return $returnHtml;
    }

}
