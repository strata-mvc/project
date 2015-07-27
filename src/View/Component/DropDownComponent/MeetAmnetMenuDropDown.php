<?php
namespace App\View\Component\DropDownComponent;

class MeetAmnetMenuDropDown extends DropDownComponent {

    public function getLabel()
    {
        return __("Meet Amnet", PROJECT_KEY);
    }

    public function getTemplateName()
    {
        return "meet-amnet";
    }

    public function getAssociatedACF()
    {
        return "meet_amnet_widget";
    }
}
