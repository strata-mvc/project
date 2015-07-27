<?php
namespace App\View\Helper;

use Exception;
use App\Model\Menu;
use Strata\View\Template;

class FooterMenuHelper extends MenuHelper {
	
	private $Acf;

	public function __construct($config = array()) {
		$this->Acf = $config['Acf'];
	}

    public function renderFooterNavigation()
    {
        $this->key = "footer-navigation";
        $this->linkedObject = Menu::getFooterNavigation();
        return $this->generate();
    }

    protected function generate()
    {
        if (is_null($this->linkedObject )) {
            throw new Exception("Missing main menu");
        }
        return Template::parse('menu/footer-menu', array("Acf" => $this->Acf, "MenuHelper" => $this));

    }

    public function generateTitle($section)
    {
        $html = array();
        
        $html[] = '<li>';
        $html[] = '<h3>';
        if($section['section_link']) {
        	$html[] = sprintf('<a href="%s">%s</a>', $section['section_link'], $section['section_title']);
        } else {
        	$html[] = $section['section_title'];
        }
        $html[] = '</h3>';
        $html[] = '</li>';

        return implode("\n", $html);
    }


	public function generateLink($link)
    {
        $html = array();

        $html[] = '<li>';
        if(!$link['is_subtitle']) {
        	$html[] = sprintf('<a href="%s">%s</a>', $this->parseUrl($link), $link['link_label']);
        } else {
        	$html[] = '<h5>'.$link['link_label'].'</h5>';
        }
        $html[] = '</li>';

        return implode("\n", $html);
    }

    public function generateTextBlock($section)
    {
        $html = array();

        if($section['add_text_block']) {
        	$html[] = '<li>';
        	$html[] = $section['section_text_block'];
        	$html[] = '</li>';
        } 

        return implode("\n", $html);
    }

}