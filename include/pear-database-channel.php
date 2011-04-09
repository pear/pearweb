<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Gregory Beaver <cellog@php.net>                             |
   +----------------------------------------------------------------------+
   $Id$
*/

class channel
{
    /**
     * Add a new channel to the database
     * @todo add server validation
     *       - verify it is a valid server name
     *       - connect and retrieve the channel.xml
     *         to verify that this is possible
     */
    static function add($name)
    {
        global $dbh;
        $query = 'INSERT INTO channels (name, is_active) VALUES (?, 0)';
        $err = $dbh->query($query, array($name));
        if (DB::isError($err)) {
            return $err;
        }
    }

    static function edit($name, $project_label, $project_link, $contact_name, $contact_email)
    {
        global $dbh;
        $query = 'UPDATE channels SET project_label = ?, project_link = ?, contact_name = ?, contact_email = ? WHERE name = ?';
        $err = $dbh->query($query, array($project_label, $project_link, $contact_name, $contact_email, $name));
        if (DB::isError($err)) {
            return $err;
        }
    }

    static function activate($name) 
    {
        global $dbh;
        $query = "UPDATE channels SET is_active = 1 WHERE name = ?";
        $err = $dbh->query($query, array($name));
        if (DB::isError($err)) {
            return $err;
        }        
    }

    static function deactivate($name) 
    {
        global $dbh;
        $query = "UPDATE channels SET is_active = 0 WHERE name = ?";
        $err = $dbh->query($query, array($name));
        if (DB::isError($err)) {
            return $err;
        }        
    }

    static function remove($name) 
    {
        global $dbh;
        $query = "DELETE FROM channels WHERE name = ?";
        $err = $dbh->query($query, array($name));
        if (DB::isError($err)) {
            return $err;
        }        
    }

    static function exists($name) 
    {
        global $dbh;
        $query = "SELECT * FROM channels WHERE name = ?";
        $err = $dbh->query($query, array($name));
        if (DB::isError($err)) {
            return $err;
        }

        return $err->numRows();
    }

    /**
     * List all registered channels
     * @return array Format: array(array(channel server), array(channel server),... )
     */
    static function listAll()
    {
        global $dbh;
        $query = 'SELECT name, project_label, project_link, contact_name, contact_email FROM channels';
        return $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }

    /**
     * List all registered channels that are approved
     * @return array Format: array(array(channel server), array(channel server),... )
     */
    static function listActive()
    {
        global $dbh;
        $query = 'SELECT name, project_label, project_link, contact_name, contact_email FROM channels WHERE is_active = 1';
        return $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }


    /**
     * List all registered channels pending approval
     * @return array Format: array(array(channel server), array(channel server),... )
     */
    static function listInactive()
    {
        global $dbh;
        $query = 'SELECT name, project_label, project_link, contact_name, contact_email FROM channels WHERE is_active = 0';
        return $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }

    /** A method to validate a channel */
    static function validate(HTTP_Request2 $req, PEAR_ChannelFile $chan) 
    {
        $response = $req->send();
        if ($response->getStatus() != 200) {
            throw new Exception("Invalid channel site");
        }

        if (!$response->getBody()) {
            throw new Exception("Empty channel.xml");
        }


        if (strlen($response->getBody()) > 100000) {
            throw new Exception("Channel.xml too large");
        }

        if (!$chan->fromXmlString($response->getBody())) {
            throw new Exception("Invalid xml");
        }

        if (!$chan->validate()) {
            throw new Exception("Invalid channel file");
        }


        return true;
    }
}
