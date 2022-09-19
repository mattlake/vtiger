<?php

require_once 'include/logging.php';
include_once 'libraries/adodb/adodb.inc.php';
require_once 'libraries/adodb/adodb-xmlschema.inc.php';
require_once __DIR__ . '/PreparedQMark2SqlValue.php';

$log = Logger::getLogger('VT');
$logsqltm = Logger::getLogger('SQLTIME');

/**
 * Performance perference API
 */
@include_once('config.performance.php'); // Ignore warning if not present
class PerformancePrefs
{
    /** Get boolean value */
    static function getBoolean($key, $defvalue = false)
    {
        return self::get($key, $defvalue);
    }

    /**
     * Get performance parameter configured value or default one
     */
    static function get($key, $defvalue = false)
    {
        global $PERFORMANCE_CONFIG;
        if (isset($PERFORMANCE_CONFIG)) {
            if (isset($PERFORMANCE_CONFIG[$key])) {
                return $PERFORMANCE_CONFIG[$key];
            }
        }
        return $defvalue;
    }

    /** Get Integer value */
    static function getInteger($key, $defvalue = false)
    {
        return intval(self::get($key, $defvalue));
    }
}