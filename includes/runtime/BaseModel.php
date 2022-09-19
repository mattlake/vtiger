<?php

/**
 * Base Model Class
 */
class Vtiger_Base_Model
{
    protected $valueMap;

    /**
     * Constructor
     * @param array $values
     */
    function __construct($values = [])
    {
        $this->valueMap = $values;
    }

    /**
     * Function to get the raw value for a given key
     * @param mixed $key
     * @return mixed Raw Value for the given key
     */
    public function getRaw($key)
    {
        return $this->rawData[$key];
    }

    /**
     * Function to get the value if its safe to use for SQL Query (column).
     * @param string $key
     * @param boolean $skipEmpty - Skip the check if string is empty
     * @return mixed Value for the given key
     */
    public function getForSql($key, $skipEmpty = true)
    {
        return Vtiger_Util_Helper::validateStringForSql($this->get($key), $skipEmpty);
    }

    /**
     * Function to get the value for a given key
     * @param mixed $key
     * @return mixed|null value for the given key
     */
    public function get($key)
    {
        return $this->valueMap[$key] ?? null;
    }

    /**
     * Function to set the value for a given key
     * @param mixed $key
     * @param mixed $value
     * @return Vtiger_Base_Model
     */
    public function set($key, $value)
    {
        $this->valueMap[$key] = $value;
        return $this;
    }

    /**
     * Function to set all the values for the Object
     * @param array $values (key-value mapping)
     * @return Vtiger_Base_Model
     */
    public function setData($values)
    {
        $this->valueMap = $values;
        return $this;
    }

    /**
     * Function to get all the values of the Object
     * @return array (key-value mapping)
     */
    public function getData()
    {
        return $this->valueMap;
    }

    /**
     * Function to check if the key exists.
     * @param string $key
     */
    public function has($key)
    {
        return array_key_exists($key, $this->valueMap);
    }

    /**
     * Function to check if the key is empty.
     * @param boolean $key
     */
    public function isEmpty($key)
    {
        return (! isset($this->valueMap[$key]) || empty($this->valueMap[$key]));
    }
}