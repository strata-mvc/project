<?php
namespace App\Model;

use Strata\Model\Model;
use App\Model\PostQuery;
use Strata\Model\CustomPostType\LabelParser;

/**
 * Wraps Post default objects.
 */
class Post extends AppModel {

    public $configuration = array(
        "has" => array(
            "Taxonomy\Category"
        )
    );
    /**
     * The permission level required for editing by the model
     * @var string
     */
    public $permissionLevel = 'edit_posts';

    /**
     * Returns the model's menu icon
     * @return string
     */
    public function getIcon()
    {
        return 'dashicons-admin-post';
    }

    /**
     * Returns the key Wordpress uses to identify this model.
     * @return string
     */
    public function wordpressKey()
    {
        return "post";
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
     * Returns whether or not the current model supports and has taxonomies.
     * @return boolean True if model has taxonomies
     */
    public function hasTaxonomies()
    {
        return array_key_exists("has", $this->configuration) && count($this->configuration['has'] > 0);
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
     * Gets the associated taxonomy objects.
     * @return array
     */
    public function getTaxonomies()
    {
        $tax = array();
        foreach ($this->configuration['has']  as $taxonomyName) {
            $tax[] = Model::factory($taxonomyName);
        }
        return $tax;
    }

    /**
     * Generates the edit link url for editing the model in the backend.
     * @param  stdClass $translation Sitepress translation object
     * @param  string $language    Locale code
     * @return string              Url
     */
    public function generateAdminEditLink($translation, $language)
    {
        return admin_url("edit-tags.php?taxonomy=category&action=edit&tag_ID=9&lang=it_IT&post_type=post");
    }

    /**
     * Returns an array of taxonomies associated to the model but with
     * translated terms processed into the result.
     * @return array
     */
    public function findGlobalTaxonomiesWithLanguages()
    {
        $taxonomyData = $this->getTaxonomies();

        foreach ($taxonomyData as $idx => $taxonomy) {
            $query = $taxonomy->globalQuery();
            $taxonomyData[$idx]->{"terms"} = $query->findGlobalWithLanguages();
        }

        return $taxonomyData;
    }

    /**
     * Gets a post translation
     * @param  int $postID
     * @param  int $languageCode
     * @param  string $postType     Optional
     * @return array
     */
    public static function translate($postID, $languageCode, $postType = 'post')
    {
        $translatedId = icl_object_id($postID , $postType, true, $languageCode);
        if ($translatedId) {
            return get_permalink($translatedId);
        }

        // Forward to the language's homepage
        return Wpml::getLanguageUrl($languageCode);
    }


    public static function findInBlog($limit = 30)
    {
        $ref = new self();
        return $ref->globalQuery()
            ->orderby("date")
            ->direction("DESC")
            ->status("publish")
            ->where('suppress_filters', false)
            ->limit((int)$limit)
            ->fetch();
    }
}
