<?php
namespace App\View\Component\DropDownComponent;


class ServiceMenuDropDown extends DropDownComponent {

    public function getLabel()
    {
        return __("Services", PROJECT_KEY);
    }

    public function getTemplateName()
    {
        return "programmatic-services";
    }

    public function getAssociatedACF()
    {
        return "programmatic_services_widget";
    }
}
