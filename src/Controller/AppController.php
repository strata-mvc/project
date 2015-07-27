<?php
namespace App\Controller;

use App\Model\Wpml;
use App\Model\Editor;
use IP\GoogleTagManager\GTMViewHelper;
use Strata\Router\Router;

/** This is the base controller class for your application.
 *  All of your custom controllers should extend this class.
 *
 *  Controller documentation can be found here :
 *  http://strata.francoisfaubert.com/docs/controllers/
 */
class AppController extends \Strata\Controller\Controller {

    public $helpers = array(
        "Wordpress",
        "Acf" => array("name" => "Acf"),
        "Menu"
    );

    public function init()
    {
        parent::init();

        if (!is_admin() || Router::isAjax()) {
            $this->preloadGTM();

            $this->loadAcfHelper("Header");
            $this->loadAcfHelper("FooterMenu");

            $this->view->loadHelper("LanguageHelper", array(
                "request" => $this->request,
                "locale" => ICL_LANGUAGE_CODE
            ));
        }
    }


    /**
     * Catch all cases for the project. Every page on Amnet will trigger
     * a call to this unless another route is caught before.
     *
     * @return [type] [description]
     */
    public function index()
    {

    }

    /**
     * Creates a path form a list of folders and filenames.
     * @param  array  $destination The destination in array-form
     * @return string              The destination in string-form
     */
    protected function toPath($destination = array())
    {
        return implode(DIRECTORY_SEPARATOR, $destination);
    }

    /**
     * Throws a Wordpress error if user cannot manage options.
     */
    protected function throwIfCantManageOptions()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
    }

    /**
     * Throws a Wordpress error if user cannot manage the current language.
     */
    protected function throwIfCantEditCurrentLanguage()
    {
        if (!Editor::canWriteIn(Wpml::getCurrentLanguage())) {
            wp_die(__('You do not have sufficient language permissions to access this page.'));
        }
    }

    protected function loadAcfHelper($name, $config = array())
    {
        $config += array(
            "Acf" => $this->view->get("Acf")
        );

        return $this->view->loadHelper($name, $config);
    }

    private function preloadGTM()
    {
        $gtm = new GTMViewHelper();
        $gtm->callRemotePlugin();
        $this->view->set("GTMViewHelper", $gtm);
    }
}
