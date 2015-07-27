<?php
namespace App\View\Helper;

class PaginationHelper extends AppHelper {

    private $paginationConfig;

    public function __construct($paginationConfig = null)
    {
        $this->config = $paginationConfig;
    }

    /**
     * Renders a pagination
     * @param  integer
     * @return string
     */
    public function render()
    {
        $pagination = paginate_links($this->config);

        if (is_null($pagination)) {
            return "";
        }

        return $pagination;
    }
}
