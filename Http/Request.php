<?php

declare(strict_types=1);

namespace Http;

use Vtiger_Functions;
use Vtiger_Util_Helper;
use Zend_Json;

class Request
{
    private array $valueMap = [];
    private array $rawValueMap = [];
    private array $defaultMap = [];

    public function __construct($values, $rawvalues = array())
    {
        Vtiger_Functions::validateRequestParameters($values);
        $this->valueMap = $values;
        $this->rawValueMap = $rawvalues;
    }

    /**
     * Get value for key as boolean
     */
    function getBoolean($key, $defaultValue = ''): bool
    {
        return strcasecmp('true', $this->get($key, $defaultValue) . '') === 0;
    }

    /**
     * Get key value (otherwise default value)
     */
    function get($key, $defaultValue = '')
    {
        $value = $defaultValue;
        if (isset($this->valueMap[$key])) {
            $value = $this->valueMap[$key];
        }
        if ($value === '' && isset($this->defaultMap[$key])) {
            $value = $this->defaultMap[$key];
        }

        $isJSON = false;
        if (is_string($value)) {
            // NOTE: Zend_Json or json_decode gets confused with big-integers (when passed as string)
            // and convert them to ugly exponential format - to overcome this we are performing a pre-check
            if (strpos($value, "[") === 0 || strpos($value, "{") === 0) {
                $isJSON = true;
            }
        }
        if ($isJSON) {
            $oldValue = Zend_Json::$useBuiltinEncoderDecoder;
            Zend_Json::$useBuiltinEncoderDecoder = false;
            $decodeValue = Zend_Json::decode($value);
            if (isset($decodeValue)) {
                $value = $decodeValue;
            }
            Zend_Json::$useBuiltinEncoderDecoder = $oldValue;
        }

        //Handled for null because vtlib_purify returns empty string
        if (! empty($value)) {
            $value = vtlib_purify($value);
        }
        return $value;
    }

    /**
     * Function to get the value if its safe to use for SQL Query (column).
     * @param string $key
     * @param bool $skipEmpty - Skip the check if string is empty
     * @return mixed value for the given key
     */
    public function getForSql($key, $skipEmpty = true)
    {
        return Vtiger_Util_Helper::validateStringForSql($this->get($key), $skipEmpty);
    }

    /**
     * Check for existence of key
     */
    function has($key): bool
    {
        return isset($this->valueMap[$key]);
    }

    /**
     * Is the value (linked to key) empty?
     */
    function isEmpty($key): bool
    {
        $value = $this->get($key);
        return empty($value);
    }

    /**
     * Get the raw value (if present) ignoring primary value.
     */
    function getRaw($key, $defValue = '')
    {
        if (isset($this->rawValueMap[$key])) {
            return $this->rawValueMap[$key];
        }
        return $this->get($key, $defValue);
    }

    /**
     * Set the value for key, both in the object as well as global $_REQUEST variable
     */
    function setGlobal($key, $newValue)
    {
        $this->set($key, $newValue);
        // TODO - This needs to be cleaned up once core apis are made independent of REQUEST variable.
        // This is added just for backward compatibility
        $_REQUEST[$key] = $newValue;
    }

    /**
     * Set the value for key
     */
    function set($key, $newValue)
    {
        $this->valueMap[$key] = $newValue;
    }

    /**
     * Set default value for key
     */
    function setDefault($key, $defValue)
    {
        $this->defaultMap[$key] = $defValue;
    }

    /**
     * Shorthand function to get value for (key=_operation|operation)
     */
    function getOperation()
    {
        return $this->get('_operation', $this->get('operation'));
    }

    /**
     * Shorthand function to get value for (key=_session)
     */
    function getSession()
    {
        return $this->get('_session', $this->get('session'));
    }

    /**
     * Shorthand function to get value for (key=mode)
     */
    function getMode()
    {
        return $this->get('mode');
    }

    function getModule($raw = true)
    {
        $moduleName = $this->get('module');
        if (! $raw) {
            $parentModule = $this->get('parent');
            if (! empty($parentModule)) {
                $moduleName = $parentModule . ':' . $moduleName;
            }
        }
        return $moduleName;
    }

    function isAjax(): bool
    {
        if (! empty($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX'] == true) {
            return true;
        } elseif (! empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return true;
        }
        return false;
    }

    function validateWriteAccess($skipRequestTypeCheck = false): bool
    {
        if (! $skipRequestTypeCheck) {
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                throw new \Exception('Invalid request');
            }
        }
        $this->validateReadAccess();
        $this->validateCSRF();
        return true;
    }

    /**
     * Validating incoming request.
     */
    function validateReadAccess(): bool
    {
        $this->validateReferer();
        // TODO validateIP restriction?
        return true;
    }

    protected function validateReferer(): bool
    {
        $user = vglobal('current_user');
        // Referer check if present - to over come
        if (isset($_SERVER['HTTP_REFERER']) && $user) {//Check for user post authentication.
            global $site_URL;
            if ((stripos($_SERVER['HTTP_REFERER'], $site_URL) !== 0) && ($this->get('module') != 'Install')) {
                throw new \Exception('Illegal request');
            }
        }
        return true;
    }

    protected function validateCSRF()
    {
        if (! csrf_check(false)) {
            throw new \Exception('Unsupported request');
        }
    }

    /**
     * Get purified data map
     */
    function getAllPurified(): array
    {
        foreach ($this->valueMap as $key => $value) {
            $sanitizedMap[$key] = $this->get($key);
        }
        return $sanitizedMap;
    }

    /**
     * Function gives the return url for a request
     * @return string - return url
     */
    function getReturnURL()
    {
        $data = $this->getAll();
        $returnURL = array();
        foreach ($data as $key => $value) {
            if (stripos($key, 'return') === 0 && ! empty($value) && $value != '/') {
                if ($key == 'returnsearch_params' && $value == '""') {
                    continue;
                }
                $newKey = str_replace('return', '', $key);
                $returnURL[$newKey] = $value;
            }
        }
        return http_build_query($returnURL);
    }

    /**
     * Get data map
     */
    function getAll()
    {
        return $this->valueMap;
    }

    /**
     * Function sets the viewer with the return url parameters
     * @param $viewer - template object
     */
    function setViewerReturnValues($viewer)
    {
        $viewer->assign('RETURN_MODULE', $this->get('returnmodule'));
        $viewer->assign('RETURN_VIEW', $this->get('returnview'));
        $viewer->assign('RETURN_PAGE', $this->get('returnpage'));
        $viewer->assign('RETURN_VIEW_NAME', $this->get('returnviewname'));
        $viewer->assign('RETURN_SEARCH_PARAMS', $this->get('returnsearch_params'));
        $viewer->assign('RETURN_SEARCH_KEY', $this->get('returnsearch_key'));
        $viewer->assign('RETURN_SEARCH_VALUE', $this->get('returnsearch_value'));
        $viewer->assign('RETURN_SEARCH_OPERATOR', $this->get('returnoperator'));
        $viewer->assign('RETURN_SORTBY', $this->get('returnsortorder'));
        $viewer->assign('RETURN_ORDERBY', $this->get('returnorderby'));

        $viewer->assign('RETURN_RECORD', $this->get('returnrecord'));
        $viewer->assign('RETURN_RELATED_TAB', $this->get('returntab_label'));
        $viewer->assign('RETURN_RELATED_MODULE', $this->get('returnrelatedModuleName'));
        $viewer->assign('RETURN_MODE', $this->get('returnmode'));
        $viewer->assign('RETURN_RELATION_ID', $this->get('returnrelationId'));
        $viewer->assign('RETURN_PARENT_MODULE', $this->get('returnparent'));
    }
}
