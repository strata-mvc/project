<?php
namespace App\Controller;

use App\Model\Wpml;
use App\View\Helper\WpmlHelper;

class AjaxController extends AppController {

    public function before()
    {
        $this->view->loadHelper("Wpml");
    }

    public function editLocaleLabels()
    {
        $this->view->set("translations", Wpml::getLanguageTranslations($this->request->post("language")));
        $this->view->set("currentLanguage", Wpml::getLanguageDetails($this->request->post("language")));

        $this->view->render(array(
            "content" => $this->view->loadTemplate($this->toPath(array("admin", "wpml", "edit")))
        ));
    }

    public function addNewLocale()
    {
        $this->view->set("translations", Wpml::findAllActive());
        $this->view->set("currentLanguage", Wpml::create());

        $this->view->render(array(
            "content" => $this->view->loadTemplate($this->toPath(array("admin", "wpml", "edit")))
        ));
    }

    public function deleteLocale()
    {
        $this->view->set("currentLanguage", Wpml::getLanguageDetails($this->request->post("language")));
        $this->view->render(array(
            "content" => $this->view->loadTemplate($this->toPath(array("admin", "wpml", "delete")))
        ));
    }

    public function confirmDuplication()
    {
        $this->view->set("post", get_post((int)$this->request->post("postId")));
        $this->view->set("translateTo", Wpml::getLanguageDetails($this->request->post("language")));

        $this->view->render(array(
            "content" => $this->view->loadTemplate($this->toPath(array("admin", "locales", "confirm-duplicate")))
        ));
    }
}
