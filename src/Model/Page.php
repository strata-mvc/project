<?php
namespace App\Model;

use App\Model\PostQuery;
use Strata\Model\CustomPostType\LabelParser;

/**
 * Wraps Page default objects. Also mimic functions expected by CustomPostType objects
 * in order for the Page model to be used seamlessly alongside the CPTs.
 */
class Page extends AppModel {

    public $configuration = array();

    /**
     * The permission level required for editing by the model
     * @var string
     */
    public $permissionLevel = 'edit_pages';

    /**
     * Returns the model's menu icon
     * @return string
     */
    public function getIcon()
    {
        return 'dashicons-admin-page';
    }

    /**
     * Returns the key Wordpress uses to identify this model.
     * @return string
     */
    public function wordpressKey()
    {
        return "page";
    }

    /**
     * Returns a label object that exposes singular and plural labels
     * @return LabelParser
     */
    public function getLabel()
    {
        $labelParser = new LabelParser($this);
        $labelParser->parse();
        return $labelParser;
    }

    /**
     * Returns a query object that looks up global posts.
     * @return PostQuery
     */
    public function globalQuery()
    {
        return PostQuery::factory($this);
    }

    /**
     * Returns whether or not the current model supports and has taxonomies.
     * @return boolean True if model has taxonomies
     */
    public function hasTaxonomies()
    {
        return false;
    }

    /**
     * Generates the edit link url for editing the model in the backend.
     * @param  stdClass $translation Sitepress translation object
     * @param  string $language    Locale code
     * @return string              Url
     */
    public function generateAdminEditLink($translation, $language)
    {
        return admin_url(sprintf("post.php?post=%s&action=edit&lang=%s", $translation->element_id, $language));
    }

    /**
     * Gets a page translation
     * @param  int $postID
     * @param  int $languageCode
     * @param  string $postType     Optional
     * @return array
     */
    public static function translate($postID, $languageCode, $postType = 'page')
    {
        $translatedId = icl_object_id($postID , $postType, true, $languageCode);
        if ($translatedId) {
            return get_permalink($translatedId);
        }

        // Forward to the language's homepage
        return Wpml::getLanguageUrl($languageCode);
    }

    public static function getParentId()
    {
        $page = get_post(get_the_ID());
        return (int)$page->post_parent;
    }
}
