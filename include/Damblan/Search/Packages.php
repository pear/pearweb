<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2005 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Author: Martin Jansen <mj@php.net>                                   |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once "Damblan/Search.php";

define("ITEMS_PER_PAGE", 10);

/**
 * Package search class
 *
 * @author Martin Jansen <mj@php.net>
 * @package Damblan
 * @version $Revision$
 * @extends Damblan_Search
 */
class Damblan_Search_Packages extends Damblan_Search {

    var $_placeholders = "";
    var $_where;
    var $_title = "Packages";

    function search($term) {
        if (empty($term)) {
            return;
        }

        $this->_where = $this->getWhere($term);

        // Get number of overall results
        $query = "SELECT COUNT(*) FROM packages WHERE " . $this->_where;
        $this->_total = $this->_dbh->getOne($query);
    }

    function getResults($pager) {
        if (!is_array($this->_results)) {
            // Select all results
            $query = "SELECT * FROM packages WHERE " . $this->_where . " ORDER BY name";
            $query .= " LIMIT " . (($pager->getCurrentPageID() - 1) * ITEMS_PER_PAGE) . ", " . ITEMS_PER_PAGE;

            $this->_results = $this->_dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
        }

        return $this->_results;
    }

    function getWhere($term) {
        $elements = preg_split("/\s/", $term, -1, PREG_SPLIT_NO_EMPTY);

        // we are only interested in the first 3 search words
        $elements = array_slice($elements, 0, 3);

        foreach ($elements as $t) {
            foreach (array("name", "summary") as $field) {
                $ors[] = $field . " LIKE " . $this->_dbh->quote("%" . $t . "%");
            }
            $where[] = "(" . implode(" OR ", $ors) . ")";
            $ors = array();
        }

        return implode(" AND ", $where) . " AND approved = 1 AND package_type = 'pear'";
    }
}
