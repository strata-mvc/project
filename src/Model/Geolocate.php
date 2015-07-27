<?php
namespace App\Model;

class Geolocate extends AppModel {

    public $city;
    public $country;
    public $region;
    public $org;
    public $hostname;

    public function query($ip)
    {
        $details = $this->remoteQuery($ip);

        if (property_exists($details, "city"))      $this->city     = $details->city;
        if (property_exists($details, "region"))    $this->region  = $details->region;
        if (property_exists($details, "country"))   $this->country  = $details->country;
        if (property_exists($details, "org"))       $this->org      = $details->org;
        if (property_exists($details, "hostname"))  $this->hostname = $details->hostname;
    }

    private function remoteQuery($ip)
    {
        $json = file_get_contents("http://ipinfo.io/{$ip}");
        return json_decode($json);
    }

}
