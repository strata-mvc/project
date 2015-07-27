<?php
namespace App\Controller\Admin;

use App\Model\Editor;

class AdminController extends \App\Controller\AppController {

   public function before()
    {
        if (!$this->request->hasGet("lang")) {
            Editor::forceFirstAvailableLanguage();
        }

        $this->throwIfCantEditCurrentLanguage();
    }

}
