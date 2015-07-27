<?php
namespace App\Controller\Admin;

use App\Model\Wpml;
use App\Model\Region;
use App\Model\Option;

class RegionManagementController extends \App\Controller\Admin\AdminController {

    public function associateRegions()
    {
        if ($this->request->isPost()) {
            $this->_saveRegionAssociation();
        }

        $this->view->set("regions", Region::repo()->findAllActive());
        $this->view->set("regionMap", Option::regionMap());
        $this->view->set("languages", Wpml::findAllActive());

        $this->view->render(array(
            "content" => $this->view->loadTemplate($this->toPath(array("admin", "locales", "manage-regions")))
        ));
    }

    public function _saveRegionAssociation()
    {
        return Option::saveRegionMap((array)$this->request->post('regionMap'));
    }
}
