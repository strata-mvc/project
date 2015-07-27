<?php
namespace App\Controller\Admin;

use App\Model\Wpml;

class WpmlManagementController extends \App\Controller\Admin\AdminController {

    /**
     * Manages WPML languages outside of their scope because they break
     * MySQL and HTTP headers due to their poor management of resources.
     * @return string html
     */
    public function customLanguageManager()
    {
        $this->throwIfCantManageOptions();

        if ($this->request->isPost()) {
            $this->_performOptionsPostOperation();
        }

        $this->view->set("languages", Wpml::findAllActive());
        $this->view->render(array(
            "content" => $this->view->loadTemplate($this->toPath(array("admin", "wpml", "manager")))
        ));
    }

    private function _performOptionsPostOperation()
    {
        if ($this->request->get("action") == "delete") {
            $this->_deleteLanguage();
        } else {
            $localeID = (int)$this->request->post("icl_edit_language.id");
            if ($localeID > 0) {
                $this->_updateLanguage();
            } else {
                $this->_createLanguage();
            }
        }

        Wpml::resetCache();
    }

    private function _deleteLanguage()
    {
        $localeID = (int)$this->request->post("icl_edit_language.id");
        if ($localeID > 0) {
            Wpml::deleteLanguage($localeID);
        }
    }

    private function _updateLanguage()
    {
        Wpml::updateLanguage($this->request->post("icl_edit_language"));

        $editingLanguage = $this->request->post("icl_edit_language.code");
        foreach ($this->request->post("translations") as $translationKey => $translationValue) {

            if (Wpml::hasALabel($editingLanguage, $translationKey)) {
                Wpml::updateLabel($editingLanguage, $translationKey, $translationValue);
            } else {
                Wpml::insertLabel($editingLanguage, $translationKey, $translationValue);
            }
        }
    }

    private function _createLanguage()
    {
        Wpml::insertLanguage($this->request->post("icl_edit_language"));

        $editingLanguage = $this->request->post("icl_edit_language.code");
        Wpml::insertLabel($editingLanguage, $editingLanguage, $editingLanguage);
        foreach ($this->request->post("translations") as $translationKey => $translationValue) {
            Wpml::insertLabel($editingLanguage, $translationKey, $translationValue);
        }
    }
}
