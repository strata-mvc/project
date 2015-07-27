<?php
namespace App\View\Helper;

use IP\Session\SessionManager;
use Strata\Controller\Request;

use App\Model\Region;
use App\Model\Post;
use App\Model\Geolocate;

/**
 * The LanguageHelper is a tool that handles all translations on the front end.
 */
class LanguageHelper extends AppHelper {

    private $request;
    private $sessionManager;

    private $regionsList;

    private $locale;
    private $region;
    private $area;
    private $areaLocales;

    private $geolocator;

    /**
     * From an active locale and the current request, sets up a locale manager
     * @param string  $locale
     * @param Request $request
     */
    function __construct($configuration = array()) {

        $this->request = $configuration['request'];
        $this->locale = $configuration['locale'];

        $this->geolocator = new Geolocate();

        $this->setSessionManager();
        $this->buildRegionsList();

        $this->setRegion();
        $this->setArea();
        $this->setAreaLocales();

        $this->saveToSession();
    }

    /**
     * Returns the name of the region currently active
     * @return string
     */
    public function getCurrentRegionName()
    {
        return $this->region->post_title;
    }

    /**
     * Returns the Region post type that is currently active.
     * @return string
     */
    public function getCurrentRegion()
    {
        return $this->region;
    }

    /**
     * Returns current area index
     * @return int
     */
    public function getCurrentAreaIdx()
    {
        return $this->area;
    }

    /**
     * Returns current area name
     * @return string
     */
    public function getCurrentAreaName()
    {
        if (!is_null($this->region)) {
            $list = Region::findAreas($this->region->ID);
            return $list[(int)$this->area];
        }

        return "";
    }

    /**
     * Returns the possible locales associated to the current region and area combination.
     * @return [type] [description]
     */
    public function getCurrentAreaLocales()
    {
        return $this->areaLocales;
    }

    /**
     * Gets the url of a translated post.
     * @param  string $localeCode
     * @param  int $currentPostId
     * @param  string $currentPostType Wordpress post type
     * @return string
     */
    public function getTranslatedUrl($localeCode, $currentPostId, $currentPostType)
    {
        return Post::translate($currentPostId, $localeCode, $currentPostType);
    }

    /**
     * Returns whether there are regions set in the project.
     * @return boolean
     */
    public function hasRegions()
    {
        return count($this->regionsList) > 0;
    }

    /**
     * Returns the list of regions set in the project.
     * @return array
     */
    public function getRegions()
    {
        return $this->regionsList;
    }

    /**
     * Returns the list of areas in the specified region
     * @param  int $regionId
     * @return array
     */
    public function getRegionAreas($regionId)
    {
        return Region::findAreas($regionId);
    }

    /**
     * Returns the list of locales in the specified region and area
     * @param  int $regionId
     * @param  int $areaIdx
     * @return array
     */
    public function getAreaLocales($region, $areaIdx)
    {
        return Region::getAreaLocales($region, $areaIdx);
    }

    private function buildRegionsList()
    {
        $this->regionsList = Region::repo()->findAllActive();
    }

    private function setSessionManager()
    {
        $this->sessionManager = SessionManager::getInstance();
    }

    private function setRegion()
    {
        $id = 0;
        // Take the one sent in manually
        if ($this->request->isGet() && $this->request->hasGet("region")) {
            $id = (int)$this->request->get("region");
        }
        elseif ($this->sessionManager->exists("amnet_language_region_id")) {
            $id = (int)$this->sessionManager->get("amnet_language_region_id");
        }

        // If we had already set it once, it should always exit before
        // the external queries
        if ($id > 0) {
           $this->region = Region::repo()->findById($id);
           return;
        }

        // Lookup by IP
        if (is_null($this->region)) {
            $this->geolocator->query($_SERVER['REMOTE_ADDR']);
            $this->region = Region::findFromCountryCode($this->geolocator->country);
        }

        if (is_null($this->region)) {
            $this->region =  Region::findFromLocale($this->locale);
        }
    }

    private function setArea()
    {
        $this->area = null;

        // Take the one sent in manually
        if ($this->request->isGet() && $this->request->hasGet("area")) {
            $this->area = $this->request->get("area");
            return;
        }
        // If not, check if there's a session value for the details
        // and if its a valid combo.
        elseif ($this->sessionManager->exists("amnet_language_area")) {
           $this->area = $this->sessionManager->get("amnet_language_area");
           return;
        }

        // Lookup by IP assumes we already queried for region and use
        // the same data set to load the area
        if (is_null($this->area) && !is_null($this->geolocator->region)) {
            $this->geolocation->query($_SERVER['REMOTE_ADDR']);
            $this->area = Region::findFromStateName($this->geolocation->region, $this->region);
        }

        // Or guess by taking the first one that matches the
        // current region/locale combo
        if (is_null($this->area)) {
            $this->area = Region::filterFirstMatchingAreaIdx($this->region, $this->locale);
        }
    }

    private function setAreaLocales()
    {
        $this->areaLocales = Region::listCompanionLocalesFromRegion($this->region, $this->locale);
        if (is_null($this->areaLocales)) {
            $this->areaLocales = array();
        }
    }

    private function saveToSession()
    {
        $this->sessionManager->set("amnet_language_region_id", $this->region->ID);
        $this->sessionManager->set("amnet_language_area", $this->area);
    }
}
