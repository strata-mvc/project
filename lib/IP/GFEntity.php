<?php

namespace IP;

use Exception;
use \GFAPI;

class GFEntity {

    protected $_formId = null;
    protected $_mapping = array(
        "id"      => array(),
        "label"   => array()
    );

    protected $_validatedSteps = array();
    protected $_validatedKeys = array();
    protected $_redereredKeys = array();
    protected $_reparsedKeys = array();
    protected $_datasourceKeys = array();
    protected $_submitCallbacks = array();
    protected $_gform = null;
    protected $_marketoValues = array();

    function __construct($formId = null)
    {
        if (is_null($formId)) {
            throw new Exception("IP\GFEntity requires a Gravity Form Id.");
        }

        $this->_formId = $formId;
        $this->_gform = (class_exists('GFAPI')) ? GFAPI::get_form($formId) : null;    
        
        $this->_buildIndexes();
    }

    /**
     * Enables or disables the use of credit cards
     * @param (bool) Status : true to enable, false to disable.
     */
    public static function enableCreditCards($status = true)
    {
        add_action("gform_enable_credit_card_field", function() use ($status) { return $status; });
    }

    /**
     * Cancel out the default Gravity Form emailer.
     */
    public function disableGlobalEmails()
    {
        add_filter("gform_pre_send_email", function($email) {
            $email["abort_email"] = true;
            return $email;
        }, 10, 2);
    }

    public function getIdMapping()
    {
        return $this->_mapping["id"];
    }

    public function getLabelMapping()
    {
        return $this->_mapping["label"];
    }

    public function getFieldValue($key, $subKey = null)
    {
        return $this->_getValue($key, $subKey);
    }

    public function getFieldId($key)
    {
        $field = $this->getFieldByKey($key);
        if (!is_null($field)) {
            return (int)$field["id"];
        }
    }

    public function getFieldKey($id)
    {
        $field = $this->getFieldById($id);
        if (!is_null($field)) {
            return (int)$field["adminLabel"];
        }
    }

    public function getFieldById($key)
    {
        $map = $this->getIdMapping();
        if(array_key_exists($key, $map)) {
            return $map[$key];
        }
    }

    public function getFieldByKey($id)
    {
        $map = $this->getLabelMapping();
        if(array_key_exists($id, $map)) {
            return $map[$id];
        }
    }

    public function getField($bySomething)
    {
        if (intval($bySomething) === 0) {
            return $this->getFieldByKey($bySomething);
        }

        return $this->getFieldById($bySomething);
    }

    public function getCurrentPage()
    {
        return (int)rgpost('gform_source_page_number_' . (int)$this->_formId);
    }

    /**
     * Globally sets a value on a gravity form field.
     * @return the set value if it worked
     */
    public function setGFFieldValue($name, $value)
    {
        $field = $this->getFieldByKey($name);
        if (!is_null($field)) {
            $_POST["input_" . $field["id"]] = $value;
            return $value;
        }

    }

    public function addStepValidation($stepNumber, $callback)
    {
        $this->_validatedSteps[$stepNumber] = $callback;

        // Add the global form validation only once
        if (count($this->_validatedSteps) === 1) {
            add_filter(sprintf("gform_validation_%d", $this->_formId), array($this, 'onValidation'), 10, 4);
        }
    }

    public function addValidation($key, $callback)
    {
        $id = $this->getFieldId($key);
        if($id > 0) {
            add_filter(sprintf("gform_field_validation_%d_%d", $this->_formId, $id), $callback, 10, 4);
        }
    }

    public function addRenderer($key, $callback)
    {
        $this->_renderedKeys[$key] = $callback;

        // Add the global form renderer only once
        if (count($this->_renderedKeys) === 1) {
            add_filter(sprintf("gform_pre_render_%d", $this->_formId), array($this, 'onRender'), 10, 1);
        }
    }

    public function addDatasource($key, $callback)
    {
        $this->_datasourceKeys[$key] = $callback;
    }

    public function addMapInput($key, $callback)
    {
        $this->_reparsedKeys[$key] = $callback;

        // Add the global form renderer only once
        if (count($this->_reparsedKeys) === 1) {
            add_filter("gform_field_input", array($this, "mapInput"), 10, 5);
        }
    }

    /**
     * Based on the known form fields, create a multidimentional array of values.
     * @return array
     */
    public function toArray()
    {
        $data = array();

        foreach (array_keys($this->getLabelMapping()) as $fieldName) {
            // Save the results are regular array, not dot notation.
            $this->_dotToArray($data, $fieldName, $this->getFieldValue($fieldName));
        }

        return $data;
    }

    public function mapInput($input, $field, $value, $lead_id, $formId)
    {
        // Only trigger on the current form
        if((int)$formId === $this->_formId) {
            // only trigger on supported fields
            $key = (!empty($field['adminLabel'])) ? $field['adminLabel'] : $field['id'];
            if (array_key_exists($key, $this->_reparsedKeys)) {
                return call_user_func_array($this->_reparsedKeys[$key], array($input, $field, $value));
            }
        }

        return $input;
    }

    public function onRender($gform)
    {
        if (!is_null($this->_renderedKeys)) {

            foreach ($gform['fields'] as $idx => $field) {
                // Only render when the field should be on page but gforms doesn't seem to expose anything to help.
                //if((int)$field['pageNumber'] === ($this->getCurrentPage() + 1)) {
                    if (!empty($field['adminLabel']) && array_key_exists($field['adminLabel'], $this->_renderedKeys)) {
                        $gform['fields'][$idx] = call_user_func_array($this->_renderedKeys[$field['adminLabel']], array($gform, $field));

                    }
                    // support for ids on fields that can't be dynamic
                    elseif(array_key_exists(intval($field['id']), $this->_renderedKeys)) {
                        $gform['fields'][$idx] = call_user_func_array($this->_renderedKeys[intval($field['id'])], array($gform, $field));
                    }
                //}
            }
        }

        return $gform;
    }

    public function onValidation($gform)
    {
        // Check to see if there is a validatior for the current page. If so, call it.
        $currentPage = $this->getCurrentPage();
        if (!is_null($this->_validatedSteps) && array_key_exists($currentPage, $this->_validatedSteps)) {
            $gform = call_user_func_array($this->_validatedSteps[$currentPage], array($gform));
        }

        return $gform;
    }

    public function onSubmit($callback)
    {
        add_filter("gform_after_submission", $callback, 10, 2);
    }

    /**
     * From a dot notation, creates a multidimentional array.
     * If a valid is passed on, it assigns it along the way.
     */
    protected function _dotToArray(&$context, $name, $value = null)
    {
        $pieces = explode('.', $name);
        foreach ($pieces as $piece) {
            if (!array_key_exists($piece, $context)) {
                $context[$piece] = array();
            }
            $context = &$context[$piece];
        }

        $context = $value;
        return $context;
    }

    /**
     * Each time we get a input value, either return the default
     * parser or check to see if a custom one has been set.
     */
    protected function _getValue($key, $subInput = null)
    {
        // If a special field getter has been declared, call it
        if (array_key_exists($key, $this->_datasourceKeys)) {
            return call_user_func_array($this->_datasourceKeys[$key], array($key));
        }

        // If the key is a string, expect input admin labels. If it's not,
        // handle it like a numeric id.
        if (is_string($key)) {
            $field = $this->getFieldByKey($key);
            $fieldId = $field["id"];
        } else {
            $fieldId = $key;
        }

        // If the special parser was not set or if it did not return anything,
        // proceed with the default getter.
        if (!is_null($fieldId)) {
            // If we know what we are looking for...
            if (!is_null($subInput)) {
                switch($field['type']) {
                    case "creditcard" :
                        $subs = array(
                            "number" => "_1",
                            "expiration" => "_2",
                            "cvv" => "_3",
                            "type" => "_4",
                            "name" => "_5"
                        );
                        $subfield = $subs[$subInput];
                        break;
                    case "address" :
                        $subs = array(
                            "street-1" => "_1",
                            "street-2" => "_2",
                            "city" => "_3",
                            "province" => "_4",
                            "postal-code" => "_5",
                            "country" => "_6"
                        );
                        $subfield = $subs[$subInput];
                        break;
                    default :
                        $subfield = '_' . $subInput;
                }
                return rgpost("input_" . $fieldId . $subfield);

            // If we aren't looking for a particular field, but just the whole thing...
            } elseif(in_array($field['type'], array("checkbox", "select"))) {
                
                $values = array();
                $matchingKeys = preg_grep("/(input_{$fieldId}_\d+)/i", array_keys($_POST));
                
                // ...but only if there are sub values
                if(count($matchingKeys)) {
                    foreach ($matchingKeys as $key) {
                        $values[] = rgpost($key);
                    }
                    return $values;
                }
            }

            return rgpost("input_" . $fieldId);
        }
    }

    /**
     * Keep an indexed pointer array by numeric ids and adminLabels for quicker
     * lookups.
     */
    protected function _buildIndexes()
    {
        // $gform = \RGFormsModel::get_form_meta($this->_formId);
        foreach ($this->_gform['fields'] as $idx => $field) {
            // Admin labels are only loaded on fields that are currently active.
            if(!empty($field["adminLabel"])) {
                $this->_mapping["label"][$field["adminLabel"]] = $field;
            }

            $this->_mapping["id"][$field["id"]] = $field;
        }
    }
}