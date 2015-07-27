<?php
namespace App\View\Helper;

use Exception;
use App\Model\Menu;
use App\View\Component\DropDownComponent\DropDownComponent;

class MenuHelper extends AppHelper {

    protected $linkedObj = null;
    protected $key = "";

    public function renderMainNavigation()
    {
        $this->key = "main-navigation";
        $this->linkedObject = Menu::getMainNavigation();
        return $this->generate();
    }

    public function renderTopNavigation()
    {
        $this->key = "top-navigation";
        $this->linkedObject = Menu::getTopNavigation();
        return $this->generate();
    }

    public function renderBtmFooterNavigation()
    {
        $this->key = "btm-footer-navigation";
        $this->linkedObject = Menu::getBtmFooterNavigation();
        return $this->generate();
    }

    protected function generate()
    {
        if (is_null($this->linkedObject )) {
            throw new Exception("Missing main menu");
        }

        $html = "";
        foreach (get_field("links", $this->linkedObject->ID) as $link) {
            $html .= $this->generateElement($link);
        }

        return sprintf('<ul class="%s">%s</ul>', $this->key, $html);
    }

    public function generateElement($link)
    {
        $html = array();

        $html[] = '<li>';
        $html[] = sprintf('<a href="%s">%s</a>', $this->parseUrl($link), $link['link_label']);
        $html[] = $this->parseDropdown($link);
        $html[] = '</li>';

        return implode("\n", $html);
    }

    protected function parseUrl($link)
    {
        if (!empty($link['static_link_url'])) {
            return $link['static_link_url'];
        }

        return $link['link_url'];
    }

    protected function getDropdownData($link, $dropdown)
    {
        if (array_key_exists($dropdown->getAssociatedACF(), $link)) {
            return $link[$dropdown->getAssociatedACF()][0];
        }

        return array();
    }

    protected function parseDropdown($link)
    {
        if (!empty($link['dropdown_widget'])) {
            $dropDown = DropDownComponent::factoryFromName($link['dropdown_widget']);
            return $dropDown->render(array(
                "menu" => $this->linkedObject,
                "link" => $link,
                "dropdown" => $dropDown,
                "data" => $this->getDropdownData($link, $dropDown)
            ));
        }

        return "";
    }

}
