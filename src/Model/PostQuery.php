<?php
namespace App\Model;

use Strata\Model\CustomPostType\Query;
use App\Model\AppCustomPostType;
use App\Model\Wpml;

use Exception;

class PostQuery extends Query {

    /**
     * Factories a PostQuery object
     * @param  mixed $postType The model on which the queries will be executed.
     * @return PostQuery
     */
    public static function factory($postType)
    {
        $obj = new self();
        $obj->type($postType->wordpressKey());
        return $obj;
    }

    /**
     * Paginates the findGlobal query
     * @return array
     */
    public function paginateGlobal()
    {
        $this->prepareFind();
        $localized = Wpml::localizedQuery($this, Wpml::getDefaultLanguage());
        return $this->paginate($this->findGlobal());
    }

    /**
     * Lists posts from the default language.
     * @return array
     */
    public function findGlobal()
    {
        $this->prepareFind();
        return Wpml::localizedFetch($this, Wpml::getDefaultLanguage());
    }

    /**
     * Lists posts from the default language with the linked translations.
     * @return array
     */
    public function findGlobalWithLanguage()
    {
        return $this->appendTranslations($this->findGlobal());
    }

    /**
     * Appends the post translations on an array of post
     * @param  array  $posts
     * @return array
     */
    private function appendTranslations(array $posts)
    {
        foreach ($posts as $post) {
            $post->{"translations"} = Wpml::getPostTranslations($post->ID, $post->post_type);
        }
        return $posts;
    }

    /**
     * Prepares the common find query
     */
    private function prepareFind()
    {
         $this
            ->orderby("date")
            ->direction("DESC")
            ->status("publish")
            ->where('suppress_filters', false)
            ->where('paged', (get_query_var('paged')) ? get_query_var('paged') : 1);
    }

    /**
     * Paginates the current query.
     * @return array|null
     */
    private function paginate()
    {
        $query = $this->query();
        $totalPages = $query->max_num_pages;

        if ($totalPages > 1) {
            return array(
                'base' => add_query_arg('paged','%#%'),
                'format' => '?paged=%#%',
                'mid-size' => 1,
                'current' => (get_query_var('paged')) ? get_query_var('paged') : 1,
                'total' => $totalPages,
                'prev_next' => true,
                'prev_text' => __( 'Previous' ),
                'next_text' => __( 'Next' )
            );
        }

        return null;
    }

}
