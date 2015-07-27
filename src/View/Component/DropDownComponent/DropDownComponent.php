<?php
namespace App\View\Component\DropDownComponent;

use Exception;
use Strata\View\Template;

class DropDownComponent {

    /**
     * Reads all the files in the containing directory and maps available drop down menus.
     * @return array
     */
    public static function listing()
    {
        $files = array_diff(scandir(__DIR__), array('..', '.', basename(__FILE__)));
        $return = array();

        foreach ($files as $file) {
            $key = basename($file, ".php");
            $object = self::factoryFromName($key);
            if (!is_null($object)) {
                $return[$key] = $object->getLabel();
            }
        }

        return $return;
    }

    public static function factoryFromName($name)
    {
        $Classname = "App\\View\\Component\\DropDownComponent\\" . $name;
        if (class_exists($Classname)) {
            return new $Classname();
        }
    }

    /**
     * Returns a translatable user-friendly label for the drop down menu.
     * @return string
     * @throws Exception
     */
    public function getLabel()
    {
        throw new Exception("Missing label in sub class.");
    }

    public function getTemplateName()
    {
        throw new Exception("Missing template name in sub class.");
    }

    public function getAssociatedACF()
    {
        throw new Exception("Missing ACF association in sub class.");
    }

    public function render($data = array())
    {
        $path = array("menu", "dropdowns", $this->getTemplateName());
        return Template::parse(implode(DIRECTORY_SEPARATOR, $path), $data);
    }
}
