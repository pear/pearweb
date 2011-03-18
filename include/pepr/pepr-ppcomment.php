<?php
/**
 * Establishes the procedures, objects and variables used throughout PEPr.
 *
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

class ppComment
{
    var $pkg_prop_id;
    var $user_handle;
    var $timestamp;
    var $comment;
    var $table;

    function __construct($dbhResArr, $table = 'package_proposal_changelog')
    {
        foreach ($dbhResArr as $name => $value) {
            $this->$name = $value;
        }
        $this->table = $table;
    }

    function get($proposalId, $handle, $timestamp,
                 $table = 'package_proposal_changelog')
    {
        global $dbh;
        $sql = "SELECT *, timestamp FROM ".$table." WHERE pkg_prop_id = ".$proposalId." AND user_handle='".$handle."' AND timestamp = FROM_UNIXTIME(".$timestamp.")";
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $set = $res->fetchRow(DB_FETCHMODE_ASSOC);
        $comment = new ppComment($set);
        return $comment;
    }

    function &getAll($proposalId, $table = 'package_proposal_changelog')
    {
        global $dbh;
        $sql = "SELECT *, timestamp FROM ".$table." WHERE pkg_prop_id = ".$proposalId." ORDER BY timestamp";
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $comments = array();
        while ($set = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $comments[] = new ppVote($set);
        }
        return $comments;
    }

    function store($proposalId)
    {
        global $dbh;
        if (empty($this->user_handle)) {
            return PEAR::raiseError("Not initialized");
        }
        $sql = "INSERT INTO ".$this->table." (pkg_prop_id, user_handle, comment, timestamp)
                    VALUES (".$proposalId.", ".$dbh->quoteSmart($this->user_handle).", ".$dbh->quoteSmart($this->comment).", ".time().")";
        $res = $dbh->query($sql);
        return $res;
    }

    function delete()
    {
        global $dbh;
        if (empty($this->table) || empty($this->user_handle) || empty($this->pkg_prop_id) || empty($this->timestamp)) {
            return PEAR::raiseError("Inconsistant comment data. Can not delete comment.");
        }
        $sql = "DELETE FROM ".$this->table." WHERE user_handle = '".$this->user_handle."' AND pkg_prop_id = ".$this->pkg_prop_id." AND timestamp = ".$this->timestamp;
        $res = $dbh->query($sql);
        return true;
    }
}
