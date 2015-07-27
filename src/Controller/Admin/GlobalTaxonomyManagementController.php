<?php

/**
 *
 *
 *  I don't think this class is being used. Delete if project has launched and this
 *  message is still there.
 *
 */


namespace App\Controller\Admin;

use App\Model\Wpml;
use App\Model\Editor;
use App\Model\PostQuery;
use App\Model\AppCustomPostType;
use App\Model\Taxonomy\AppTaxonomy;
use App\Model\Taxonomy\TaxonomyQuery;
use App\View\Helper\WpmlHelper;
use App\View\Helper\PaginationHelper;

class GlobalTaxonomyManagementController extends AdminController {

    public function before()
    {
        $this->view->loadHelper("Wpml");
    }

    /**
     * Displays an uneditable list of taxonomies assigned to the global locale.
     */
    public function globalList()
    {
        if ($this->request->isPost()) {
            $this->duplicateTaxonomyInLanguage();
        } else {
            $this->buildGlobalTaxonomyListing();
        }
    }

    private function buildGlobalTaxonomyListing()
    {

        $customPostType = AppCustomPostType::factoryFromString($this->request->get("page"));

        $this->view->set("customPostType", $customPostType);
        $this->view->set("label", $customPostType->getLabel());
        $this->view->set("WpmlHelper", $wpmlHelper);
        $this->view->set("defaultLanguage", Wpml::getDefaultLanguage());
        $this->view->set("languages", Editor::getTranslationsPossibilities());
        $this->view->set("taxonomies", $customPostType->findGlobalTaxonomiesWithLanguages());

        $this->view->render(array(
            "content" => $this->view->loadTemplate($this->toPath(array("admin", "locales", "view-taxonomies")))
        ));
    }

}
