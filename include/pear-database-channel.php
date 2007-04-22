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
    function add($name, $server)
    {
        global $dbh;
        $query = 'INSERT INTO channels (name) VALUES (?)';
        $err = $dbh->query($query, array($name));
        if (DB::isError($err)) {
            return $err;
        }
        // clear cache
        include_once 'xmlrpc-cache.php';
        $cache = new XMLRPC_Cache;
        $cache->remove('channel.listAll', array());
    }

    // {{{ proto array channel::listAll() API 1.0
    /**
     * List all registered channels
     * @return array Format: array(array(channel server), array(channel server),... )
     */
    function listAll()
    {
        global $dbh;
        $query = 'SELECT * FROM channels';
        return $dbh->getAll($query, null, DB_FETCHMODE_ORDERED);
    }
    // }}}
}