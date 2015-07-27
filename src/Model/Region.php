<?php
namespace App\Model;

use App\Model\Option;

class Region extends AppCustomPostType {

    public $configuration = array(
        "supports"  => array('title'),
        "menu_icon" => "dashicons-pressthis"
    );

    /**
     * Returns all active regions
     * @return [type] [description]
     */
    public function findAllActive()
    {
        return $this->query()
            ->status("publish")
            ->where("suppress_filters", 0)
            ->fetch();
    }

    /**
     * Returns a list of regions associated to a locale
     * @param  string $localeCode
     * @return array
     */
    public static function findFromLocale($localeCode)
    {
        $map = Option::regionMap();

        foreach ($map as $regionName => $currentRegion) {
            foreach($currentRegion as $idx => $subregion) {
                if (array_key_exists($localeCode, $subregion)) {
                     return self::findBySlug($regionName);
                }
            }
        }

        return array();
    }

    public static function findFromCountryCode($countryCode)
    {
        foreach (Region::repo()->findAll() as $region) {
            foreach (get_field("region_and_locales_map", $region->ID) as $area) {
                if ($area["subregion_country_code"] == $countryCode) {
                    return $region;
                }
            }
        }
    }

    public static function findFromStateName($stateName)
    {
        foreach (Region::repo()->findAll() as $region) {
            foreach (get_field("region_and_locales_map", $region->ID) as $area) {
                if ($area["subregion_province"] == $stateName) {
                    return $region;
                }
            }
        }
    }


    /**
     * Returns a list of locales linked to the same locale code as the one
     * sent in parameter from the first matching region.
     * @param  string $localeCode
     * @return array
     */
    public static function listCompanionLocales($localeCode)
    {
        $map = Option::regionMap();

        foreach ($map as $regionName => $region) {
            $locales = self::listCompanionLocalesFromRegion($region, $localeCode);
            if (!is_null($locales)) {
                return $locales;
            }
        }

        return array();
    }

    /**
     * Returns a list of locales linked to the same locale code as the one
     * sent in parameter also located in the $inRegion.
     * @param  stdClass $inRegion   A region reference
     * @param  string $localeCode
     * @return array
     */
    public static function listCompanionLocalesFromRegion($inRegion, $localeCode)
    {
        $map = Option::regionMap();
        $regionAreas = $map[$inRegion->post_name];

        foreach($regionAreas as $idx => $locales) {
            if (array_key_exists($localeCode, $locales)) {
                 return $locales;
            }
        }

        return array();
    }

    /**
     * Returns the list of locales associated to the region's area subdivision.
     * @param  stdClass $region   A region reference
     * @param  int $areaIdx
     * @return array
     */
    public static function getAreaLocales($region, $areaIdx)
    {
        $map = Option::regionMap();

        if (array_key_exists($region->post_name, $map) && count($map[$region->post_name]) > $areaIdx) {
            return $map[$region->post_name][$areaIdx];
        }

        return array();
    }

    /**
     * Based on the region and locale, returns the first area that supports the combination.
     * @param  stdClass $currentRegion
     * @param  string $locale
     * @return int|null
     */
    public static function filterFirstMatchingAreaIdx($currentRegion, $locale)
    {
        $map = Option::regionMap();

        if (array_key_exists($currentRegion->post_name, $map)) {
            foreach ($map[$currentRegion->post_name] as $idx => $subregionAssociations) {
                if (array_key_exists($locale, $subregionAssociations) && count(self::findAreas($currentRegion->ID))) {
                    return $idx;
                }
            }
        }
    }

    /**
     * Lists the areas of a region
     * @param  int $regionId
     * @return array
     */
    public static function findAreas($regionId)
    {
        $results = array();

        foreach (get_field("region_and_locales_map", $regionId) as $area) {
            $results[] = $area["subregions"];
        }

        return $results;
    }

    /**
     * Finds a region by it's unique slug.
     * @param  string $slug [description]
     * @return stdObject
     */
    public static function findBySlug($slug)
    {
        $resultset = $this->query()->where("name", $slug)->status("publish")->limit(1)->fetch();
        return array_pop($resultset);
    }
}
