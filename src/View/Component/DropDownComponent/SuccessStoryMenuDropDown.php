<?php
namespace App\View\Component\DropDownComponent;

class SuccessStoryMenuDropDown extends DropDownComponent {

    public function getLabel()
    {
        return __("Success Story", PROJECT_KEY);
    }

    public function getTemplateName()
    {
        return "success-story";
    }

    public function getAssociatedACF()
    {
        return "success_story_widget";
    }
}
