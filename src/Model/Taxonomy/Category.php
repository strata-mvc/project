<?php
namespace App\Model\Taxonomy;

/**
 *  Placeholder for post categories
 */
class Category extends \App\Model\Taxonomy\AppTaxonomy {

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __("Category", PROJECT_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function wordpressKey()
    {
        return "category";
    }
}
