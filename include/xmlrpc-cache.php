<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Christian Dickmann <dickmann@php.net>                       |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once "Cache.php";

error_reporting(0);

/**
 * Class to cache XML_RPC requests
 *
 * This class employs the Cache package in order to cache the result of 
 * calls to the XML-RPC service.
 *
 * @author Christian Dickmann <dickmann@php.net>
 * @version $Revision$
 */
class XMLRPC_Cache
{
    var $cache;

    /**
     * Attempts to return a reference to a concrete XMLRPC_Cache instance
     *
     * This method will only create a new XMLRPC_Cache instance if no
     * instance already exists.
     *
     * @access public
     * @return object XMLRPC_Cache Concrete XMLRPC_Cache reference
     */
    function &singleton()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new XMLRPC_Cache();
        }

        return $instance;
    }

    /**
     * Constructor
     *
     * This method creates an instance of Cache_Lite for later use.
     */
    function XMLRPC_Cache()
    {
        $this->cache = new Cache('file', 
                                 array(
                                       'cache_dir'       => PEAR_TMPDIR . '/cache/',
                                       'filename_prefix' => 'cache_xmlrpc_',
                                       )
                                 );
    }

    /**
     * Get cached result of a previous method call
     *
     * @access public
     * @param  string Name of the method
     * @param  array  Arguments for the method
     * @param  int    Age in secondsprevious to which the cache content will be discarded.
     * @return string Result of the method call
     */
    function get($method, $args, $maxAge = null)
    {
        $id = $this->cache->generateID(array($method, $args));

        if ($maxAge != null) {
            $filename = $this->cache->container->getFilename($id, 'default');
            if (!file_exists($filename)) {
                return null;
            }
            $time = filemtime($filename);
            if ($maxAge > $time) {
                return "";
            }
        }

        return $this->cache->get($id);
    }

    /**
     * Caches a method call
     *
     * @access public
     * @param  string Name of the method
     * @param  array  Arguments for the method
     * @param  string Return value of the method call
     */
    function save($method, $args, $value)
    {
        $id = $this->cache->generateID(array($method, $args));

        return $this->cache->save($id, $value);
    }

    /**
     * Removes a method call result from the cache
     *
     * @access public
     * @param  string Name of the method
     * @param  array  Arguments for the method
     */
    function remove($method, $args)
    {
        $id = $this->cache->generateID(array($method, $args));

        return $this->cache->remove($id);
    }
}
?>
