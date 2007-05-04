<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * The PEAR DB driver for PHP's mysql extension
 * for interacting with MySQL databases
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Database
 * @package    DB
 * @author     Stig Bakken <ssb@php.net>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/DB
 */

/**
 * Obtain the DB_common class so it can be extended from
 */
require_once 'DB/common.php';
require_once dirname(__FILE__) . '/core.php';
/**
 * The methods PEAR DB uses to interact with PHP's mysql extension
 * for interacting with MySQL databases
 *
 * These methods overload the ones declared in DB_common.
 *
 * @category   Database
 * @package    DB
 * @author     Stig Bakken <ssb@php.net>
 * @author     Daniel Convissor <danielc@php.net>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: 1.7.11
 * @link       http://pear.php.net/package/DB
 */
class DB_mock extends DB_common
{
    // {{{ properties

    /**
     * The DB driver type (mysql, oci8, odbc, etc.)
     * @var string
     */
    var $phptype = 'mock';

    /**
     * The database syntax variant to be used (db2, access, etc.), if any
     * @var string
     */
    var $dbsyntax = 'mysql';

    /**
     * The capabilities of this DB implementation
     *
     * The 'new_link' element contains the PHP version that first provided
     * new_link support for this DBMS.  Contains false if it's unsupported.
     *
     * Meaning of the 'limit' element:
     *   + 'emulate' = emulate with fetch row by number
     *   + 'alter'   = alter the query
     *   + false     = skip rows
     *
     * @var array
     */
    var $features = array(
        'limit'         => 'alter',
        'new_link'      => '4.2.0',
        'numrows'       => true,
        'pconnect'      => true,
        'prepare'       => false,
        'ssl'           => false,
        'transactions'  => true,
    );

    /**
     * A mapping of native error codes to DB error codes
     * @var array
     */
    var $errorcode_map = array(
        1004 => DB_ERROR_CANNOT_CREATE,
        1005 => DB_ERROR_CANNOT_CREATE,
        1006 => DB_ERROR_CANNOT_CREATE,
        1007 => DB_ERROR_ALREADY_EXISTS,
        1008 => DB_ERROR_CANNOT_DROP,
        1022 => DB_ERROR_ALREADY_EXISTS,
        1044 => DB_ERROR_ACCESS_VIOLATION,
        1046 => DB_ERROR_NODBSELECTED,
        1048 => DB_ERROR_CONSTRAINT,
        1049 => DB_ERROR_NOSUCHDB,
        1050 => DB_ERROR_ALREADY_EXISTS,
        1051 => DB_ERROR_NOSUCHTABLE,
        1054 => DB_ERROR_NOSUCHFIELD,
        1061 => DB_ERROR_ALREADY_EXISTS,
        1062 => DB_ERROR_ALREADY_EXISTS,
        1064 => DB_ERROR_SYNTAX,
        1091 => DB_ERROR_NOT_FOUND,
        1100 => DB_ERROR_NOT_LOCKED,
        1136 => DB_ERROR_VALUE_COUNT_ON_ROW,
        1142 => DB_ERROR_ACCESS_VIOLATION,
        1146 => DB_ERROR_NOSUCHTABLE,
        1216 => DB_ERROR_CONSTRAINT,
        1217 => DB_ERROR_CONSTRAINT,
        1356 => DB_ERROR_DIVZERO,
        1451 => DB_ERROR_CONSTRAINT,
        1452 => DB_ERROR_CONSTRAINT,
    );

    /**
     * The raw database connection created by PHP
     * @var resource
     */
    var $connection;

    /**
     * The DSN information for connecting to a database
     * @var array
     */
    var $dsn = array();


    /**
     * Should data manipulation queries be committed automatically?
     * @var bool
     * @access private
     */
    var $autocommit = true;

    /**
     * The quantity of transactions begun
     *
     * {@internal  While this is private, it can't actually be designated
     * private in PHP 5 because it is directly accessed in the test suite.}}
     *
     * @var integer
     * @access private
     */
    var $transaction_opcount = 0;

    /**
     * The database specified in the DSN
     *
     * It's a fix to allow calls to different databases in the same script.
     *
     * @var string
     * @access private
     */
    var $_db = '';
    /**
     * Mock database
     *
     * @var mockDB_core
     */
    var $_mock;


    // }}}
    // {{{ constructor

    /**
     * This constructor calls <kbd>$this->DB_common()</kbd>
     *
     * @return void
     */
    function DB_mock()
    {
        $this->DB_common();
    }
    // }}}

    function setMock(mockDB_core $mock)
    {
        $this->_mock = $mock;
    }

    // {{{ connect()

    /**
     * Connect to the database server, log in and open the database
     *
     * Don't call this method directly.  Use DB::connect() instead.
     *
     * PEAR DB's mysql driver supports the following extra DSN options:
     *   + new_link      If set to true, causes subsequent calls to connect()
     *                    to return a new connection link instead of the
     *                    existing one.  WARNING: this is not portable to
     *                    other DBMS's. Available since PEAR DB 1.7.0.
     *   + client_flags  Any combination of MYSQL_CLIENT_* constants.
     *                    Only used if PHP is at version 4.3.0 or greater.
     *                    Available since PEAR DB 1.7.0.
     *
     * @param array $dsn         the data source name
     * @param bool  $persistent  should the connection be persistent?
     *
     * @return int  DB_OK on success. A DB_Error object on failure.
     */
    function connect($dsn, $persistent = false)
    {
        if (mockDB_core::$failToConnect) {
            return $this->raiseError(DB_ERROR_CONNECT_FAILED,
                                     null, null, null,
                                     'Connect should fail and did');
        }

        return DB_OK;
    }

    // }}}
    // {{{ disconnect()

    /**
     * Disconnects from the database server
     *
     * @return bool  TRUE on success, FALSE on failure
     */
    function disconnect()
    {
        return true;
    }

    // }}}
    // {{{ simpleQuery()

    /**
     * Sends a query to the database server
     *
     * Generally uses mysql_query().  If you want to use
     * mysql_unbuffered_query() set the "result_buffering" option to 0 using
     * setOptions().  This option was added in Release 1.7.0.
     *
     * @param string  the SQL query string
     *
     * @return mixed  + a PHP result resrouce for successful SELECT queries
     *                + the DB_OK constant for other successful queries
     *                + a DB_Error object on failure
     */
    function simpleQuery($query)
    {
        try {
            $rows = $this->_mock->query($query);
        } catch (MockDB_Core_QueryException $e) {
            echo "**Unplanned query:\n" . $e->getMessage();
            die;
        } catch (Exception $e) {
            return $this->raiseError(DB_ERROR, null, $e->getMessage(), $e->getCode());
        }
        if (is_array($rows)) {
            return $query;
        }
        return DB_OK;
    }

    // }}}
    // {{{ nextResult()

    /**
     * Move the internal mysql result pointer to the next available result
     *
     * This method has not been implemented yet.
     *
     * @param a valid sql result resource
     *
     * @return false
     */
    function nextResult($result)
    {
        return false;
    }

    // }}}
    // {{{ fetchInto()

    /**
     * Places a row from the result set into the given array
     *
     * Formating of the array and the data therein are configurable.
     * See DB_result::fetchInto() for more information.
     *
     * This method is not meant to be called directly.  Use
     * DB_result::fetchInto() instead.  It can't be declared "protected"
     * because DB_result is a separate object.
     *
     * @param resource $result    the query result resource
     * @param array    $arr       the referenced array to put the data in
     * @param int      $fetchmode how the resulting array should be indexed
     * @param int      $rownum    the row number to fetch (0 = first row)
     *
     * @return mixed  DB_OK on success, NULL when the end of a result set is
     *                 reached or on failure
     *
     * @see DB_result::fetchInto()
     */
    function fetchInto($result, &$arr, $fetchmode, $rownum = null)
    {
        if ($rownum !== null) {
            if ($this->_mock->rowExists($result, $rownum)) {
                return null;
            }
        } else {
            $rownum = $this->_mock->nextRowNum($result);
        }
        $arr = $this->_mock->getRow($result, $rownum);
        if ($fetchmode & DB_FETCHMODE_ASSOC) {
            if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE && $arr) {
                $arr = array_change_key_case($arr, CASE_LOWER);
            }
        } else {
            $arr = array_values($arr);
        }
        if (!$arr) {
            return null;
        }
        if ($this->options['portability'] & DB_PORTABILITY_RTRIM) {
            /*
             * Even though this DBMS already trims output, we do this because
             * a field might have intentional whitespace at the end that
             * gets removed by DB_PORTABILITY_RTRIM under another driver.
             */
            $this->_rtrimArrayValues($arr);
        }
        if ($this->options['portability'] & DB_PORTABILITY_NULL_TO_EMPTY) {
            $this->_convertNullArrayValuesToEmpty($arr);
        }
        return DB_OK;
    }

    // }}}
    // {{{ freeResult()

    /**
     * Deletes the result set and frees the memory occupied by the result set
     *
     * This method is not meant to be called directly.  Use
     * DB_result::free() instead.  It can't be declared "protected"
     * because DB_result is a separate object.
     *
     * @param resource $result  PHP's query result resource
     *
     * @return bool  TRUE on success, FALSE if $result is invalid
     *
     * @see DB_result::free()
     */
    function freeResult($result)
    {
        return true;
    }

    // }}}
    // {{{ numCols()

    /**
     * Gets the number of columns in a result set
     *
     * This method is not meant to be called directly.  Use
     * DB_result::numCols() instead.  It can't be declared "protected"
     * because DB_result is a separate object.
     *
     * @param resource $result  PHP's query result resource
     *
     * @return int  the number of columns.  A DB_Error object on failure.
     *
     * @see DB_result::numCols()
     */
    function numCols($result)
    {
        $row = $this->_mock->getRow($result, 0);
        if (!$row) return $row;
        return count($row);
    }

    // }}}
    // {{{ numRows()

    /**
     * Gets the number of rows in a result set
     *
     * This method is not meant to be called directly.  Use
     * DB_result::numRows() instead.  It can't be declared "protected"
     * because DB_result is a separate object.
     *
     * @param resource $result  PHP's query result resource
     *
     * @return int  the number of rows.  A DB_Error object on failure.
     *
     * @see DB_result::numRows()
     */
    function numRows($result)
    {
        return $this->_mock->numRows($result);
    }

    // }}}
    // {{{ autoCommit()

    /**
     * Enables or disables automatic commits
     *
     * @param bool $onoff  true turns it on, false turns it off
     *
     * @return int  DB_OK on success.  A DB_Error object if the driver
     *               doesn't support auto-committing transactions.
     */
    function autoCommit($onoff = false)
    {
        // XXX if $this->transaction_opcount > 0, we should probably
        // issue a warning here.
        $this->autocommit = $onoff ? true : false;
        return DB_OK;
    }

    // }}}
    // {{{ commit()

    /**
     * Commits the current transaction
     *
     * @return int  DB_OK on success.  A DB_Error object on failure.
     */
    function commit()
    {
        return DB_OK;
    }

    // }}}
    // {{{ rollback()

    /**
     * Reverts the current transaction
     *
     * @return int  DB_OK on success.  A DB_Error object on failure.
     */
    function rollback()
    {
        return DB_OK;
    }

    // }}}
    // {{{ affectedRows()

    /**
     * Determines the number of rows affected by a data maniuplation query
     *
     * 0 is returned for queries that don't manipulate data.
     *
     * @return int  the number of rows.  A DB_Error object on failure.
     */
    function affectedRows()
    {
        if ($this->_last_query_manip) {
            return $this->_mock->affectedRows;
        } else {
            return 0;
        }
     }

    // }}}
    // {{{ nextId()

    /**
     * Returns the next free id in a sequence
     *
     * @param string  $seq_name  name of the sequence
     * @param boolean $ondemand  when true, the seqence is automatically
     *                            created if it does not exist
     *
     * @return int  the next id number in the sequence.
     *               A DB_Error object on failure.
     *
     * @see DB_common::nextID(), DB_common::getSequenceName(),
     *      DB_mysql::createSequence(), DB_mysql::dropSequence()
     */
    function nextId($seq_name, $ondemand = true)
    {
        return 1;
    }

    // }}}
    // {{{ createSequence()

    /**
     * Creates a new sequence
     *
     * @param string $seq_name  name of the new sequence
     *
     * @return int  DB_OK on success.  A DB_Error object on failure.
     *
     * @see DB_common::createSequence(), DB_common::getSequenceName(),
     *      DB_mysql::nextID(), DB_mysql::dropSequence()
     */
    function createSequence($seq_name)
    {
        return DB_OK;
    }

    // }}}
    // {{{ dropSequence()

    /**
     * Deletes a sequence
     *
     * @param string $seq_name  name of the sequence to be deleted
     *
     * @return int  DB_OK on success.  A DB_Error object on failure.
     *
     * @see DB_common::dropSequence(), DB_common::getSequenceName(),
     *      DB_mysql::nextID(), DB_mysql::createSequence()
     */
    function dropSequence($seq_name)
    {
        return DB_OK;
    }

    // }}}
    // {{{ quoteIdentifier()

    /**
     * Quotes a string so it can be safely used as a table or column name
     * (WARNING: using names that require this is a REALLY BAD IDEA)
     *
     * WARNING:  Older versions of MySQL can't handle the backtick
     * character (<kbd>`</kbd>) in table or column names.
     *
     * @param string $str  identifier name to be quoted
     *
     * @return string  quoted identifier string
     *
     * @see DB_common::quoteIdentifier()
     * @since Method available since Release 1.6.0
     */
    function quoteIdentifier($str)
    {
        return '`' . str_replace('`', '``', $str) . '`';
    }

    // }}}
    // {{{ quote()

    /**
     * @deprecated  Deprecated in release 1.6.0
     */
    function quote($str)
    {
        return $this->quoteSmart($str);
    }

    // }}}
    // {{{ escapeSimple()

    /**
     * Escapes a string according to the current DBMS's standards
     *
     * @param string $str  the string to be escaped
     *
     * @return string  the escaped string
     *
     * @see DB_common::quoteSmart()
     * @since Method available since Release 1.6.0
     */
    function escapeSimple($str)
    {
        return $this->_mock->escape($str);
    }

    // }}}
    // {{{ modifyQuery()

    /**
     * Changes a query string for various DBMS specific reasons
     *
     * This little hack lets you know how many rows were deleted
     * when running a "DELETE FROM table" query.  Only implemented
     * if the DB_PORTABILITY_DELETE_COUNT portability option is on.
     *
     * @param string $query  the query string to modify
     *
     * @return string  the modified query string
     *
     * @access protected
     * @see DB_common::setOption()
     */
    function modifyQuery($query)
    {
        if ($this->options['portability'] & DB_PORTABILITY_DELETE_COUNT) {
            // "DELETE FROM table" gives 0 affected rows in MySQL.
            // This little hack lets you know how many rows were deleted.
            if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $query)) {
                $query = preg_replace('/^\s*DELETE\s+FROM\s+(\S+)\s*$/',
                                      'DELETE FROM \1 WHERE 1=1', $query);
            }
        }
        return $query;
    }

    // }}}
    // {{{ modifyLimitQuery()

    /**
     * Adds LIMIT clauses to a query string according to current DBMS standards
     *
     * @param string $query   the query to modify
     * @param int    $from    the row to start to fetching (0 = the first row)
     * @param int    $count   the numbers of rows to fetch
     * @param mixed  $params  array, string or numeric data to be used in
     *                         execution of the statement.  Quantity of items
     *                         passed must match quantity of placeholders in
     *                         query:  meaning 1 placeholder for non-array
     *                         parameters or 1 placeholder per array element.
     *
     * @return string  the query string with LIMIT clauses added
     *
     * @access protected
     */
    function modifyLimitQuery($query, $from, $count, $params = array())
    {
        if (DB::isManip($query) || $this->_next_query_manip) {
            return $query . " LIMIT $count";
        } else {
            return $query . " LIMIT $from, $count";
        }
    }

    // }}}
    // {{{ errorNative()

    /**
     * Gets the DBMS' native error code produced by the last query
     *
     * @return int  the DBMS' error code
     */
    function errorNative()
    {
        return 123;
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table or a result set
     *
     * @param object|string  $result  DB_result object from a query or a
     *                                 string containing the name of a table.
     *                                 While this also accepts a query result
     *                                 resource identifier, this behavior is
     *                                 deprecated.
     * @param int            $mode    a valid tableInfo mode
     *
     * @return array  an associative array with the information requested.
     *                 A DB_Error object on failure.
     *
     * @see DB_common::tableInfo()
     */
    function tableInfo($result, $mode = null)
    {
        if (is_string($result)) {
            /*
             * Probably received a table name.
             * Create a result resource identifier.
             */
            $id = @mysql_query("SELECT * FROM $result LIMIT 0",
                               $this->connection);
            $got_string = true;
        } elseif (isset($result->result)) {
            /*
             * Probably received a result object.
             * Extract the result resource identifier.
             */
            $id = $result->result;
            $got_string = false;
        } else {
            /*
             * Probably received a result resource identifier.
             * Copy it.
             * Deprecated.  Here for compatibility only.
             */
            $id = $result;
            $got_string = false;
        }

        if (!is_resource($id)) {
            return $this->raiseError(DB_ERROR_NEED_MORE_DATA, null, null, 'hi error', 123);
        }

        if ($this->options['portability'] & DB_PORTABILITY_LOWERCASE) {
            $case_func = 'strtolower';
        } else {
            $case_func = 'strval';
        }

        $count = @mysql_num_fields($id);
        $res   = array();

        if ($mode) {
            $res['num_fields'] = $count;
        }

        for ($i = 0; $i < $count; $i++) {
            $res[$i] = array(
                'table' => $case_func(@mysql_field_table($id, $i)),
                'name'  => $case_func(@mysql_field_name($id, $i)),
                'type'  => @mysql_field_type($id, $i),
                'len'   => @mysql_field_len($id, $i),
                'flags' => @mysql_field_flags($id, $i),
            );
            if ($mode & DB_TABLEINFO_ORDER) {
                $res['order'][$res[$i]['name']] = $i;
            }
            if ($mode & DB_TABLEINFO_ORDERTABLE) {
                $res['ordertable'][$res[$i]['table']][$res[$i]['name']] = $i;
            }
        }

        // free the result only if we were called on a table
        if ($got_string) {
            @mysql_free_result($id);
        }
        return $res;
    }

    // }}}
    // {{{ getSpecialQuery()

    /**
     * Obtains the query string needed for listing a given type of objects
     *
     * @param string $type  the kind of objects you want to retrieve
     *
     * @return string  the SQL query string or null if the driver doesn't
     *                  support the object type requested
     *
     * @access protected
     * @see DB_common::getListOf()
     */
    function getSpecialQuery($type)
    {
        switch ($type) {
            case 'tables':
                return 'SHOW TABLES';
            case 'users':
                return 'SELECT DISTINCT User FROM mysql.user';
            case 'databases':
                return 'SHOW DATABASES';
            default:
                return null;
        }
    }

    // }}}

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */

?>
