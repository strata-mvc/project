<?php
namespace App\View\Helper;

use App\View\Helper\AcfHelper;

class HeaderHelper extends AppHelper {

    private $acf = null;

    public function __construct(AcfHelper $acfHelper)
    {
        $this->setupACFConnector($acfHelper);
    }

    public function hasBackgroundImage()
    {
        return !$this->acf->check("header_background_image") && !$this->acf->isEmpty("header_background_image");
    }

    public function hasBackgroundColor()
    {
        return !$this->acf->check("header_background_color") && !$this->acf->isEmpty("header_background_color");
    }

    public function hasThumbnail()
    {
        return !$this->acf->check("header_thumbnail") && !$this->acf->isEmpty("header_thumbnail");
    }

    public function generateAttributes()
    {
        $classes = array("header simple-header");
        $styles = array();

        if ($this->hasBackgroundImage()) {
            $classes[] = "custom-bg-image";
            $styles[] = sprintf("background-image:url(%s)", $this->acf->get("header_background_image"));
        } else {
            $classes[] = "default-bg-image";
        }

        if ($this->hasBackgroundColor()) {
            $styles[] = sprintf("background-color:%s", $this->acf->get("header_background_color"));
        }

        if ($this->hasThumbnail()) {
            $classes[] = "has-thumbnail";
        }

        return sprintf("class=\"%s\" styles=\"%s\"", implode(" ", $classes), implode("; ", $styles));
    }

    private function setupACFConnector($acfHelper)
    {
        $this->acf = $acfHelper;
        if (!$this->acf->hasCached()) {
            $this->acf->refresh();
        }
    }
}
