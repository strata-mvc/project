<?php
namespace App\Model;

use App\Model\Post;
use App\Model\PostQuery;
use Strata\Model\CustomPostType\LabelParser;
use Exception;

class AppCustomPostType extends \Strata\Model\CustomPostType\Entity {

    /**
     * The permission level required for editing by the model
     * @var string
     */
    public $permissionLevel = "edit_posts";

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
     * Returns the model's menu icon
     * @return string
     */
    public function getIcon()
    {
        return $this->configuration['menu_icon'];
    }

    /**
     * Factories a model based on the Wordpress key
     * @param  string $str
     * @return mixed An instanciated model
     * @throws Exception
     */
    public static function factoryFromString($str)
    {
        if (preg_match('/_?cpt_(\w+)/', $str, $matches)) {
            return self::factory($matches[1]);
        }

        if (preg_match('/_?(post|page)/', $str, $matches)) {
            return self::factory($matches[1]);
        }

        throw new Exception("Unknown pattern sent to AppCustomPostType::factoryFromString: " . $str);
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
        return array_key_exists("has", $this->configuration) && count($this->configuration['has'] > 0);
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

}
