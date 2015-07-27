<?php
namespace App\Controller\Admin;

use Strata\Router\Router;

use App\Model\Editor;
use App\Model\Post;
use App\Model\Region;
use App\Model\Wpml;

use App\Model\Adapter\WordpressAdapter;

use App\View\Component\DropDownComponent\DropDownComponent;

class CallbackController extends AdminController {

    public function admin_menu_customWpml()
    {
        $adapter = new WordpressAdapter();
        $adapter->removeOriginalAdminLinks();

        if (Editor::canWriteInDefaultLocale()) {
            $adapter->addLocaleManagementLinks();
        } else {
            $adapter->removeEditLinks();
            $adapter->addGlobalManagementLinks();
            $adapter->addLocalizedEditorLinks(Editor::getFirstAvailableLocale());
        }
    }

    public function admin_enqueue_scripts()
    {
        $adapter = new WordpressAdapter();
        $adapter->requireJqueryUi();
        $adapter->requireAdminStylesAndScript();
    }

    public function add_meta_boxes()
    {
        $adapter = new WordpressAdapter();
        $adapter->addLocalizationTools(Router::callback("Admin\\GlobalPostManagementController", "languageMetaBox"));
        $adapter->removeSEOGarbage();
    }

    public function views_edit_post_or_page($views)
    {
        Wpml::removeAdminLanguageFilter();

        if (Editor::canWriteInDefaultLocale()) {
            $this->view->set("defaultLanguage", Wpml::getDefaultLanguage());
        }

        $this->view->set("currentLanguage", $this->_getLangFromRequest());
        $this->view->set("availableLocales", Editor::getTranslationsPossibilities());
        $this->view->set("postType", $this->request->get("post_type"));

        $views["langfilter"] = $this->view->loadTemplate($this->toPath(array("admin", "wpml", "locale_select")));

        return $views;
    }

    public function acf_load_select_dropdowns($field)
    {
        $emptyRow = array(__("Please select a dropdown widget", PROJECT_KEY));
        $field['choices'] = array_merge($emptyRow, DropDownComponent::listing());
        return $field;
    }

    private function _getLangFromRequest()
    {
        if ($this->request->isGet("lang") && !is_null($this->request->get("lang"))) {
            return $this->request->get("lang");
        }

        return Wpml::getDefaultLanguage();
    }
}
