<?php
namespace App\Model\Taxonomy;

/**
 *  Placeholder for post categories
 */
class AppTaxonomy extends \App\Model\AppModel {

    /**
     * Returns the taxonomy name
     * @return string
     */
    public function getName()
    {
        return "Taxonomy";
    }

    /**
     * Instanciates a TaxonomyQuery ready to query taxonomies from the default language.
     * @return [type] [description]
     */
    public function globalQuery()
    {
        $query = TaxonomyQuery::factory();
        return $query->in($this);
    }

    /**
     * Returns a link to edit the current model in the backend
     * @param  string $taxonomy    Taxonomy label
     * @param  stdClass $translation A sitepress translation object
     * @param  string $language    Local code to edit in
     * @return string
     */
    public static function generateAdminEditLink($taxonomy, $translation, $language)
    {
        return admin_url(sprintf("edit-tags.php?action=edit&taxonomy=%s&action=edit&tag_ID=%s&post_type=post&lang=%s", $taxonomy, $translation->element_id, $language));
    }
}
