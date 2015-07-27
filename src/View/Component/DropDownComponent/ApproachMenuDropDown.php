<?php
namespace App\View\Component\DropDownComponent;

class ApproachMenuDropDown extends DropDownComponent {

    public function getLabel()
    {
        return __("Our Approach", PROJECT_KEY);
    }

    public function getTemplateName()
    {
        return "our-approach";
    }

    public function getAssociatedACF()
    {
        return "our_approach_widget";
    }
}
