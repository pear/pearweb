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
    private $_queryMap = array();

    private function _normalize($query)
    {
        return str_replace(array("\r\n", "\r", "\n"), array(' ', ' ', ' '), $query);
    }

    function addFailingQuery($query, $message, $code = 123)
    {
        $this->_queryMap[$this->_normalize($query)] =
            array('res' => 'fail', 'msg' => $message, 'code' => $code);
    }

    function addDeleteQuery($query, $modqueries, $modrows)
    {
        return $this->addInsertQuery($query, $modqueries, $modrows);
    }

    function addUpdateQuery($query, $modqueries, $modrows)
    {
        return $this->addInsertQuery($query, $modqueries, $modrows);
    }

    function addInsertQuery($query, $modqueries, $modrows)
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
            $rowcols = array_keys($this->_queryMap[$q]['rows'][0]);
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
        $this->_queryMap[$this->_normalize($query)] =
            array('res' => 'change', 'modqueries' => $modqueries, 'affectedrows' => $modrows);
    }

    function addDataQuery($query, $rows, $rowcols)
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
    }

    function query($query)
    {
        if (isset($this->_queryMap[$this->_normalize($query)])) {
            $this->queries[] = $query;
            $query = $this->_normalize($query);
            switch ($this->_queryMap[$query]['res']) {
                case 'fail' :
                    throw new Exception($this->_queryMap[$query]['msg'],
                        $this->_queryMap[$query]['code']);
                case 'ok' :
                    reset($this->_queryMap[$query]['rows']);
                    return $this->_queryMap[$query]['rows'];
                case 'change' :
                    foreach ($this->_queryMap[$query]['modqueries'] as $q => $new) {
                        if (!is_array($new) && !$new) {
                            unset($this->_queryMap[$q]);
                        }
                        $this->_queryMap[$this->_normalize($q)]['rows'] = $new;
                    }
                    $this->affectedRows = $this->_queryMap[$query]['affectedrows'];
            }
        } else {
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