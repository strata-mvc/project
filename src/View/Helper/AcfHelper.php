<?php
namespace App\View\Helper;

use Strata\Strata;

class AcfHelper extends AppHelper {

    private $cache = array();
    private $defaultId = null;

    public function refresh($id = null)
    {
        $id = $this->proofCurrentId($id);
        $this->getFields($id);
    }

    public function get($field, $id = null)
    {
        $id = $this->proofCurrentId($id);

        if (!$this->hasCached($id)) {
            $this->getFields($id);
        }

        // We could throw an error, but I think
        // views would be broken too often...
        if ($this->check($field, $id)) {
            return $this->cache[$this->getCacheKey($id)][$field];
        }
    }

    public function check($field, $id = null)
    {
        return $this->isCachedValue($field, $this->proofCurrentId($id));
    }

    public function hasCached($id = null)
    {
        $key = $this->getCacheKey($this->proofCurrentId($id));
        return array_key_exists($key, $this->cache);
    }

    public function isEmpty($field, $id = null)
    {
        return empty($this->get($field, $id));
    }

    public function isArray($field, $id = null)
    {
        return is_array($this->get($field, $id));
    }

    public function isNull($field, $id = null)
    {
        return is_null($this->get($field, $id));
    }

    private function getCacheKey($id)
    {
        return "cache-" . $id;
    }

    private function proofCurrentId($id = null)
    {
        if (is_null($id)) {
            // Only query once for default post id.
            if (is_null($this->defaultId)) {
                $this->defaultId = get_the_ID();
            }
            return $this->defaultId;
        }

        return (int)$id;
    }

    private function isCachedValue($field, $id)
    {
        return $this->hasCached($id) && array_key_exists($field, $this->cache[$this->getCacheKey($id)]);
    }

    private function getFields($id)
    {
        $this->log("Loading ACF for post ID $id");
        $this->cache[$this->getCacheKey($id)] = (array)get_fields($id);
    }

    private function log($call)
    {
        $context = "unknown context at unknown line";
        foreach (debug_backtrace() as $idx => $file) {
            if ($file['file'] != __FILE__) {
                $last = explode(DIRECTORY_SEPARATOR, $file['file']);
                $context = sprintf("%s at line %s", $last[count($last)-1], $file['line']);
                break;
            }
        }
        Strata::config("IPLogger")->log($call . " in " . $context, "[IP::AcfHelper]");
    }
}
