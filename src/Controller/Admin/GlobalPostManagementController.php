<?php
namespace App\Controller\Admin;

use App\Model\Wpml;
use App\Model\Editor;
use App\Model\PostQuery;
use App\Model\AppCustomPostType;
use App\View\Helper\WpmlHelper;
use App\View\Helper\PaginationHelper;

class GlobalPostManagementController extends AdminController {

   /**
     * Renders the language metabox allowing editors to translate content in their
     * own language.
     * @return string html
     */
    public function languageMetaBox()
    {

        // Need the original post
        $originalId = (int)get_post_meta(get_the_ID(), '_icl_lang_duplicate_of', true);
        $originalPost = get_post($originalId);
        $this->view->set("duplicateOf", $originalPost);

        $wpmlHelper = new WpmlHelper();
        $wpmlHelper->model = AppCustomPostType::factoryFromString($originalPost->post_type);
        $wpmlHelper->wp_object = $originalPost;

        // When looking at a post, it's useful to see all the related translations.
        // Make sure we compare the original post id while doing so. Duplicates
        // have no translations.
        if (get_post_status() == "auto-draft") {
            $this->view->set("invalidStatus", true);
        } else {
            $this->view->set("languages", Editor::getTranslationsPossibilities());
            $wpmlHelper->translations = Wpml::getPostTranslations($originalPost->ID, $originalPost->post_type);
        }

        $this->view->set("defaultLanguage", Wpml::getDefaultLanguage());
        $this->view->set("WpmlHelper", $wpmlHelper);

        $this->view->render(array(
            "content" => $this->view->loadTemplate($this->toPath(array("admin", "metabox", "translate-in")))
        ));
    }

    /**
     * Displays an uneditable list of posts assigned to the global locale.
     */
    public function globalList()
    {
        if ($this->request->isPost()) {
            $this->duplicatePostInLanguage();
        } else {
            $this->buildGlobalPostsListing();
        }
    }

    private function buildGlobalPostsListing()
    {
        $customPostType = AppCustomPostType::factoryFromString($this->request->get("page"));

        $wpmlHelper = new WpmlHelper();
        $wpmlHelper->model = $customPostType;

        $this->view->set("customPostType", $customPostType);
        $this->view->set("label", $customPostType->getLabel());
        $this->view->set("WpmlHelper", $wpmlHelper);
        $this->view->set("defaultLanguage", Wpml::getDefaultLanguage());
        $this->view->set("languages", Editor::getTranslationsPossibilities());

        $query = $customPostType->globalQuery();
        $this->view->set("PaginationHelper", new PaginationHelper($query->paginateGlobal()));
        $this->view->set("posts", $query->findGlobalWithLanguage());

        $this->view->render(array(
            "content" => $this->view->loadTemplate($this->toPath(array("admin", "locales", "view-posts")))
        ));
    }

    private function duplicatePostInLanguage()
    {
        if ($this->request->hasPost("postId") && $this->request->hasPost("language")) {
            $duplicateId = Wpml::duplicateInLocale($this->request->post("postId"), $this->request->post("language"));
            $this->view->set("duplicateId", (int)$duplicateId);
            $this->view->set("duplicateLanguage", $this->request->post("language"));
        }

        $this->view->render(array(
            "content" => $this->view->loadTemplate($this->toPath(array("admin", "locales", "duplicate-forward")))
        ));
    }
}
