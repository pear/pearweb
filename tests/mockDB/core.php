<?php

/**
 * Mock database driver, frontend-independent
 *
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  1997-2005 The PHP Group
 * @version    CVS: $Id$
 */

class MockDB_Core_QueryException extends Exception {}

/**
 */
class mockDB_core
{
    public static $failToConnect = false;
    public $affectedRows = 0;
    public $queries = array();
    public $failqueries = array();
    public $modqueries = array();
    public $alterqueries = array();
    public $dataqueries = array();
    private $_queryMap = array();
    /**
     * Mapping of preg for queries to the stored query
     *
     * @var array
     */
    private $_dynamicQuery = array();

    private function _normalize($query)
    {
        return trim(str_replace(array("\r\n", "\r", "\n"), array(' ', ' ', ' '), $query));
    }

    function addFailingQuery($query, $message, $code = 123, $timefield = false)
    {
        $this->_queryMap[$this->_normalize($query)] =
            array('res' => 'fail', 'msg' => $message, 'code' => $code);
        if ($timefield) {
            if (!is_string($timefield)) {
                throw new Exception('Dynamic preg-matching query $timefield must be ' .
                    'the query for failing queries, use array for others');
            }
            $this->_dynamicQuery[$timefield] =
                array('res' => 'fail');
        }
    }

    function _validateTimefield($timefield, $query)
    {
        if (!is_array($timefield)) {
            throw new Exception('timefield for query ' . $query . ' must be an array' .
            ' with indices "query" and "replace"');
        }
        if (!isset($timefield['query'])) {
            throw new Exception('timefield for query ' . $query . ' must be an array' .
            ' with indices "query" and "replace"');
        }
        if (!isset($timefield['replace'])) {
            throw new Exception('timefield for query ' . $query . ' must be an array' .
            ' with indices "query" and "replace"');
        }
        if (!is_callable($timefield['replace']) && $timefield['replace'] !== '') {
            throw new Exception('timefield for query ' . $query . ' replace must be
            either "" or a callback for modifying the query values');
        }
        if (false === @preg_match($timefield['query'], '')) {
            throw new Exception('Invalid preg pattern passed for query ' . $query .
                ', timefield ' . $timefield['query']);
        }
    }

    function addDeleteQuery($query, $modqueries, $modrows, $timefield = false)
    {
        return $this->addInsertQuery($query, $modqueries, $modrows, $timefield);
    }

    function addUpdateQuery($query, $modqueries, $modrows, $timefield = false)
    {
        return $this->addInsertQuery($query, $modqueries, $modrows, $timefield);
    }

    function addAlterQuery($query, $modqueries, $modrows, $timefield = false)
    {
        return $this->addInsertQuery($query, $modqueries, $modrows, $timefield, true);
    }

    function addInsertQuery($query, $modqueries, $modrows, $timefield = false, $alter = false)
    {
        if (!is_array($modqueries)) {
            throw new Exception('query ' . $query . ' $modqueries must be an array');
        }
        foreach ($modqueries as $q => $newrows) {
            if (!isset($this->_queryMap[$q])) {
                throw new Exception('query ' . $query . ' $modquery ' . $q .
                    ' has not been processed (out of order?)');
            }
            if (!is_array($newrows)) {
                throw new Exception('query ' . $query . ' $modquery ' . $q .
                    ' $newrows must be an array');
            }
            if (isset($newrows['cols'])) {
                $rowcols = $newrows['cols'];
                unset($newrows['cols']);
            } elseif (isset($this->_queryMap[$q]['rows'][0])) {
                $rowcols = array_keys($this->_queryMap[$q]['rows'][0]);
            } else {
                throw new Exception('query ' . $query . ' $modquery ' . $q .
                    ' $newrows must contain "cols" index containing an array of' .
                    ' column names like "cols" => array("id", "parent")');
            }
            foreach ($newrows as $i => $data) {
                if (!is_int($i)) {
                    throw new Exception('query ' . $query . ' $modquery ' . $q . ' rows must be int-indexed, we have ' .
                    $i . ' as an index');
                }
                if (!is_array($data)) {
                    throw new Exception('query ' . $query . ' $modquery ' . $q . ' data row must be an array');
                }
                if (count($data) != count($rowcols)) {
                    throw new Exception('query ' . $query . ' $modquery ' . $q . ' data rows do not match row
                        columns');
                }
                $t = 0;
                foreach ($data as $col => $val) {
                    if (!is_string($col)) {
                        throw new Exception('query ' . $query . ' $modquery ' . $q . ' data row must have ' .
                            'associative indices');
                    }
                    if (is_resource($val) || is_object($val) || is_array($val)) {
                        throw new Exception('query ' . $query . ' $modquery ' . $q . ' data row contains ' .
                            'an object, resource or array');
                    }
                    if ($col != $rowcols[$t++]) {
                        throw new Exception('query ' . $query . ' $modquery ' . $q . ' data row ' . $i .
                            ' column ' . $col . ' does not match expected ' . $rowcols[$i - 1]);
                    }
                }
            }
        }
        $res = $alter ? 'alter' : 'change';
        $this->_queryMap[$this->_normalize($query)] =
            array('res' => $res, 'modqueries' => $modqueries, 'affectedrows' => $modrows);
        if ($timefield) {
            $this->_validateTimefield($timefield, $query);
            $this->_dynamicQuery[$timefield['query']] =
                array('res' => 'change', 'query' => $this->_normalize($query),
                      'info' => $timefield);
        }
    }

    function addDataQuery($query, $rows, $rowcols, $timefield = false)
    {
        if (!is_array($rows)) {
            throw new Exception('query ' . $query . ' $rows must be an array');
        }
        if (!is_array($rowcols)) {
            throw new Exception('query ' . $query . ' $rowcols must be an array');
        }
        $rowcols = array_values($rowcols);
        foreach ($rowcols as $i => $col) {
            if (!is_string($col)) {
                throw new Exception('query ' . $query . ' $rowcols should only ' .
                    'contain expected row column names, and contains ' . $col . ' at' .
                    ' index ' . $i);
            }
        }
        foreach ($rows as $i => $data) {
            if (!is_int($i)) {
                throw new Exception('query ' . $query . ' rows must be int-indexed, we have ' .
                $i . ' as an index');
            }
            if (!is_array($data)) {
                throw new Exception('query ' . $query . ' data row must be an array');
            }
            if (count($data) != count($rowcols)) {
                throw new Exception('query ' . $query . ' data rows do not match row
                    columns');
            }
            $t = 0;
            foreach ($data as $col => $val) {
                if (!is_string($col)) {
                    throw new Exception('query ' . $query . ' data row must have ' .
                        'associative indices');
                }
                if (is_resource($val) || is_object($val) || is_array($val)) {
                    throw new Exception('query ' . $query . ' data row contains ' .
                        'an object, resource or array');
                }
                if ($col != $rowcols[$t++]) {
                    throw new Exception('query ' . $query . ' data row ' . $i .
                        ' column ' . $col . ' does not match expected ' . $rowcols[$i - 1]);
                }
            }
        }
        $this->_queryMap[$this->_normalize($query)] = array('res' => 'ok', 'rows' => $rows,
            'cols' => count($rowcols));
        if ($timefield) {
            $this->_validateTimefield($timefield, $query);
            $this->_dynamicQuery[$timefield['query']] =
                array('res' => 'ok', 'query' => $this->_normalize($query),
                      'info' => $timefield);
        }
    }

    function query($query)
    {
        if (isset($this->_queryMap[$this->_normalize($query)])) {
            $this->queries[] = $query;
            $old = $query;
            $query = $this->_normalize($query);
            switch ($this->_queryMap[$query]['res']) {
                case 'fail' :
                    $this->failqueries[] = $old;
                    throw new Exception($this->_queryMap[$query]['msg'],
                        $this->_queryMap[$query]['code']);
                case 'ok' :
                    $this->dataqueries[] = $old;
                    reset($this->_queryMap[$query]['rows']);
                    $this->affectedRows = 0;
                    return $this->_queryMap[$query]['rows'];
                case 'change' :
                case 'alter' :
                    if ($this->_queryMap[$query]['res'] == 'change') {
                        $this->modqueries[] = $old;
                    } else {
                        $this->alterqueries[] = $old;
                    }
                    foreach ($this->_queryMap[$query]['modqueries'] as $q => $new) {
                        if (!is_array($new) && !$new) {
                            unset($this->_queryMap[$q]);
                        }
                        $this->_queryMap[$this->_normalize($q)]['rows'] = $new;
                    }
                    $this->affectedRows = $this->_queryMap[$query]['affectedrows'];
            }
        } else {
            // see if this is a dynamic query that depends on current time
            foreach ($this->_dynamicQuery as $map => $actual) {
                if (preg_match($map, $query, $matches)) {
                    if ($actual['res'] == 'fail') {
                        return $this->query($actual['query']);
                    }
                    if (is_callable($actual['info']['replace'])) {
                        $ret =
                            call_user_func($actual['info']['replace'],
                                $this->_queryMap[$actual['query']]['rows'], $matches, $query);
                        $this->_queryMap[$actual['query']]['rows'] = $ret;
                    }
                    return $this->query($actual['query']);
                }
            }
            throw new MockDB_Core_QueryException($query);
        }
    }

    function nextRowNum($query)
    {
        $a = key($this->_queryMap[$this->_normalize($query)]['rows']);
        next($this->_queryMap[$this->_normalize($query)]['rows']);
        return $a;
    }

    function numRows($query)
    {
        if (!$this->rowExists($query, 0)) {
            return false;
        }
        return count($this->_queryMap[$this->_normalize($query)]['rows']);
    }

    function numCols($query)
    {
        if (!isset($this->_queryMap[$this->_normalize($query)])) {
            return false;
        }
        return $this->_queryMap[$this->_normalize($query)]['cols'];
    }

    function rowExists($query, $rownum)
    {
        return array_key_exists($rownum, $this->_queryMap[$this->_normalize($query)]['rows']);
    }

    function getRow($query, $rownum)
    {
        if (!$this->rowExists($query, $rownum)) {
            return false;
        }
        return $this->_queryMap[$this->_normalize($query)]['rows'][$rownum];
    }

    function escape($str)
    {
        return str_replace('\'', '\\\'', $str);
    }
}