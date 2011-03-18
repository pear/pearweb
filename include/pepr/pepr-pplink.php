<?php
/**
 * Establishes the procedures, objects and variables used throughout PEPr.
 *
 * The <var>$proposalTypeMap</var> arrays is defined here.
 *
 * NOTE: Proposal constants are defined in pearweb/include/pear-config.php.
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  pearweb
 * @package   PEPr
 * @author    Tobias Schlitt <toby@php.net>
 * @author    Daniel Convissor <danielc@php.net>
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

global $proposalTypeMap;
$proposalTypeMap = array(
                         'pkg_file'             => "PEAR package file (.tgz)",
                         'pkg_source'           => "Package source file (.phps/.htm)",
                         'pkg_example'          => "Package example (.php)",
                         'pkg_example_source'   => "Package example source (.phps/.htm)",
                         'pkg_doc'              => "Package documentation");

class ppLink
{
    var $pkg_prop_id;
    var $type;
    var $url;

    function __construct($dbhResArr)
    {
        foreach ($dbhResArr as $name => $value) {
            $this->$name = $value;
        }
    }

    function &getAll(&$dbh, $proposalId)
    {
        $sql = 'SELECT * FROM package_proposal_links WHERE pkg_prop_id = ? ORDER BY type';
        $res = $dbh->query($sql, array($proposalId));
        if (DB::isError($res)) {
            return $res;
        }
        $links = array();
        while ($set = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $links[] = new ppLink($set);
        }
        return $links;
    }

    function deleteAll($dbh, $proposalId)
    {
        $sql = 'DELETE FROM package_proposal_links WHERE pkg_prop_id = ?';
        $res = $dbh->query($sql, array($proposalId));
        return $res;
    }

    function store($dbh, $proposalId)
    {
        $sql = "INSERT INTO package_proposal_links (pkg_prop_id, type, url)
                    VALUES (".$proposalId.", ".$dbh->quoteSmart($this->type).", ".$dbh->quoteSmart($this->url).")";
        $res = $dbh->query($sql);
        return $res;
    }

    function getType($humanReadable = false)
    {
        if ($humanReadable) {
            return $GLOBALS['proposalTypeMap'][$this->type];
        }

        return $this->type;
    }
}
