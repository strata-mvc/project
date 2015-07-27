<?php
namespace App\Model\Taxonomy;

use Strata\Model\CustomPostType\Query;
use App\Model\Taxonomy\AppTaxonomy;
use App\Model\Wpml;

use Exception;

class TaxonomyQuery extends Query {

    protected $_filters = array();
    protected $_taxnomonies = array();

    /**
     * Factories a TaxonomyQuery
     * @return TaxonomyQuery
     */
    public static function factory()
    {
        $obj = new self();
        return $obj;
    }

    /**
     * Adds a taxonomy to filter terms from
     * @param  string $taxonomy
     * @return TaxonomyQuery
     */
    public function in($taxonomy)
    {
        $this->_taxnomonies[] = $taxonomy->wordpressKey();
        return $this;
    }

    /**
     * Find the taxonomies using the default language.
     * @return array
     */
    public function findGlobal()
    {
        return Wpml::localizedFetch($this, Wpml::getDefaultLanguage());
    }


    /**
     * Find the taxonomies using the default language and appends references to their translations.
     * @return array
     */
    public function findGlobalWithLanguages()
    {
        return $this->appendTranslations($this->findGlobal());
    }


    /**
     * Fetches the terms matching the TaxonomyQuery.
     * @return array
     */
    public function fetch()
    {
        return get_terms($this->_taxnomonies, $this->_filters);
    }

    /**
     * Appends corresponding translations to a term array.
     * @param  array  $terms
     * @return array
     */
    private function appendTranslations(array $terms)
    {
        foreach ($terms as $term) {
            $term->{"translations"} = Wpml::getTermTranslations($term->term_taxonomy_id, $term->taxonomy);
        }

        return $terms;
    }
}
