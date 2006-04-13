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

/**
 * Basic PEAR website search class
 *
 * @author Martin Jansen <mj@php.net>
 * @package Damblan
 * @version $Revision$
 */
class Damblan_Search
{
    var $_dbh;
    var $_pager = null;
    var $_total = 0;
    var $_results = null;
    var $_title = '';

    function Damblan_Search(&$dbh)
    {
        $this->_dbh =& $dbh;
    }

    /**
     * Attempts to return a concrete search class instance based on $searchType
     *
     * @access public
     * @param  string The type of the search subclass to return
     * @param  object Instance of PEAR::DB. Will be passed to the subclass
     * @return object The concrete instance of the search subclass
     */
    function &factory($searchType, &$dbh)
    {
        switch ($searchType) {
            case 'users' :
                require_once 'Damblan/Search/Users.php';
                $s = new Damblan_Search_Users($dbh);
                break;
            case 'site' :
                require_once 'Damblan/Search/Site.php';
                $s = new Damblan_Search_Site;
                break;
            case 'pepr' :
                require_once 'Damblan/Search/PEPr.php';
                $s = new Damblan_Search_PEPr;
                break;
            case 'pear-dev':
            case 'pear-cvs':
            case 'pear-general':
                require_once 'Damblan/Search/Lists.php';
                $s = new Damblan_Search_Lists($searchType);
                break;
            case 'packages' :
            default :
                require_once 'Damblan/Search/Packages.php';
                $s = new Damblan_Search_Packages($dbh);
                break;
        }

        return $s;
    }

    /**
     * Get result set from search
     *
     * @access public
     * @return array
     */
    function getResults()
    {
        return $this->_results;
    }

    /**
     * Get total number of results
     *
     * @access public
     * @return int
     */
    function getTotal()
    {
        return $this->_total;
    }

    /**
     * Get title identifier of the search subclass
     *
     * @access public
     * @return string
     */
    function getTitle()
    {
        return $this->_title;
    }

    /**
     * Get Pager instance associated with the search
     *
     * @access public
     * @return object
     */
    function &getPager()
    {
        return $this->_pager;
    }
}
