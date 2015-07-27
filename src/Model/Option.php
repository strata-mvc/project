<?php
namespace App\Model;

use Strata\Utility\Hash;

/**
 * Class that eases pull out option values from Wordpress' option table.
 */
class Option extends AppModel {

    /**
     * Returns an associative array of regions and their linked languages.
     * @return array
     */
    public static function regionMap()
    {
        $option = get_option('amnet_region_map', array());

        if (is_string($option)) {
            $option = json_decode($option, true);
        }

        if (is_null($option)) {
            return array();
        }

        return $option;
    }

    /**
     * Saves the map region association.
     * @param  array  $regionMap
     * @return bool        True if option is updated
     */
    public static function saveRegionMap(array $value)
    {
        $normed = Hash::normalize($value);
        return update_option('amnet_region_map', json_encode($normed), false);
    }
}
