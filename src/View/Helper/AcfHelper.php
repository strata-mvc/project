<?php
namespace App\View\Helper;

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

        if (!$this->isCachedValue($id, $field)) {
            $this->getFields($id);
        }

        // We could throw an error, but I think
        // views will be broken too often...
        if ($this->check($field, $id)) {
            return $this->cache[$id][$field];
        }
    }

    public function check($field, $id = null)
    {
        return $this->isCachedValue($this->proofCurrentId($id), $field);
    }

    public function hasCached($id = null)
    {
        return array_key_exists($this->proofCurrentId($id), $this->cache);
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

    private function isCachedValue($id, $field)
    {
        return $this->hasCached($id) && array_key_exists($field, $this->cache[$id]);
    }

    private function getFields($id)
    {
        $this->cache[$id] = get_fields($id);
    }
}
