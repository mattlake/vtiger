<?php

namespace Infrastructure\DataAccess\Databases\Contracts;

interface DatabaseContract
{
    /**
     * Manage instance usage of this class
     */
    static function getInstance();

    function isMySQL();

    function isOracle();

    function isPostgres();

    function println($msg);

    function setDieOnError($value);

    function setDatabaseType($type);

    function setUserName($name);

    function setOption($name, $value);

    function setUserPassword($pass);

    function setDatabaseName($db);

    function setDatabaseHost($host);

    function getDataSourceName();

    function startTransaction();

    function completeTransaction();

    function hasFailedTransaction();

    function checkError($msg = '', $dieOnError = false);

    function change_key_case($arr);

    function checkConnection();

    function logSqlTiming($startat, $endat, $sql, $params = false);

    /**
     * Execute SET NAMES UTF-8 on the connection based on configuration.
     */
    function executeSetNamesUTF8SQL($force = false);

    /**
     * Execute query in a batch.
     *
     * For example:
     * INSERT INTO TABLE1 VALUES (a,b);
     * INSERT INTO TABLE1 VALUES (c,d);
     *
     * like: INSERT INTO TABLE1 VALUES (a,b), (c,d)
     */
    function query_batch($prefixsql, $valuearray);

    function query($sql, $dieOnError = false, $msg = '');

    /**
     * Convert PreparedStatement to SQL statement
     */
    function convert2Sql($ps, $vals);

    function pquery($sql, $params = array(), $dieOnError = false, $msg = '');

    /**
     * Flatten the composite array into single value.
     * Example:
     * $input = array(10, 20, array(30, 40), array('key1' => '50', 'key2'=>array(60), 70));
     * returns array(10, 20, 30, 40, 50, 60, 70);
     */
    function flatten_array($input, $output = null);

    function getEmptyBlob($is_string = true);

    function updateBlob($tablename, $colname, $id, $data);

    function updateBlobFile($tablename, $colname, $id, $filename);

    function limitQuery($sql, $start, $count, $dieOnError = false, $msg = '');

    function getOne($sql, $dieOnError = false, $msg = '');

    function getFieldsDefinition(&$result);

    function getFieldsArray(&$result);

    function getRowCount(&$result);

    function num_rows(&$result);

    function num_fields(&$result);

    function fetch_array(&$result);

    function run_query_record_html($query);

    function sql_quote($data);

    function sql_insert_data($table, $data);

    function run_insert_data($table, $data);

    function run_query_record($query);

    function run_query_allrecords($query);

    function run_query_field($query, $field = '');

    function run_query_list($query, $field);

    function run_query_field_html($query, $field);

    function result_get_next_record($result);

    function sql_expr_datalist($a);

    function sql_expr_datalist_from_records($a, $field);

    function sql_concat($list);

    function query_result(&$result, $row, $col = 0);

    function query_result_rowdata(&$result, $row = 0);

    /**
     * Get an array representing a row in the result set
     * Unlike it's non raw siblings this method will not escape
     * html entities in return strings.
     *
     * The case of all the field names is converted to lower case.
     * as with the other methods.
     *
     * @param &$result The query result to fetch from.
     * @param $row The row number to fetch. It's default value is 0
     *
     */
    function raw_query_result_rowdata(&$result, $row = 0);

    function getAffectedRowCount(&$result);

    function requireSingleResult($sql, $dieOnError = false, $msg = '', $encode = true);

    function requirePsSingleResult($sql, $params, $dieOnError = false, $msg = '', $encode = true);

    function fetchByAssoc(&$result, $rowNum = -1, $encode = true);

    function getNextRow(&$result, $encode = true);

    function fetch_row(&$result, $encode = true);

    function field_name(&$result, $col);

    function getQueryTime();

    function connect($dieOnError = false);

    function resetSettings($dbtype, $host, $dbname, $username, $passwd);

    function quote($string);

    function disconnect();

    function setDebug($value);

    function createTables(
        $schemaFile,
        $dbHostName = false,
        $userName = false,
        $userPassword = false,
        $dbName = false,
        $dbType = false
    );

    function createTable($tablename, $flds);

    function alterTable($tablename, $flds, $oper);

    function getColumnNames($tablename);

    function formatString($tablename, $fldname, $str);

    function formatDate($datetime, $strip_quotes = false);

    function getDBDateString($datecolname);

    function getUniqueID($seqname);

    function get_tables();

    function sql_escape_string($str);

    function getLastInsertID($seqname = '');

    function escapeDbName($dbName = '');

    function check_db_utf8_support();

    function get_db_charset();
}