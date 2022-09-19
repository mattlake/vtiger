<?php

require_once 'include/logging.php';
include_once 'libraries/adodb/adodb.inc.php';
require_once 'libraries/adodb/adodb-xmlschema.inc.php';

$log = Logger::getLogger('VT');
$logsqltm = Logger::getLogger('SQLTIME');

// Callback class useful to convert PreparedStatement Question Marks to SQL value
// See function convertPS2Sql in PearDatabase below
class PreparedQMark2SqlValue
{
    // Constructor
    function __construct($vals)
    {
        $this->ctr = 0;
        $this->vals = $vals;
    }

    function call($matches)
    {
        /**
         * If ? is found as expected in regex used in function convert2sql
         * /('[^']*')|(\"[^\"]*\")|([?])/
         *
         */
        if ($matches[3] == '?') {
            $this->ctr++;
            return $this->vals[$this->ctr - 1];
        }
        return $matches[0];
    }
}
