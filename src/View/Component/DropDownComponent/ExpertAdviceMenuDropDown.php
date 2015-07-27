<?php
namespace App\View\Component\DropDownComponent;

use App\Model\Post;

class ExpertAdviceMenuDropDown extends DropDownComponent {

    public function getLabel()
    {
        return __("Expert Advice", PROJECT_KEY);
    }

    public function getTemplateName()
    {
        return "expert-advice";
    }

    public function getAssociatedACF()
    {
        return "expert_advice_widget";
    }

    public function getBlogPosts($limit = 2)
    {
        return Post::findInBlog($limit);
    }
}
