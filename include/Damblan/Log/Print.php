<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003-2005 The PEAR Group                               |
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

require_once "Log/observer.php";

/**
 * Observer class for logging via email
 *
 * @author Martin Jansen <mj@php.net>
 * @extends Log_observer
 * @version $Revision$
 * @package Damblan
 */
class Damblan_Log_Print extends Log_observer {

    function Damblan_Log_Print() {
        $this->Log_observer();
    }
    
    /**
     * Generate logging email
     *
     * @param array Array containing the log information
     * @return void
     */
    function notify($event) {
        echo "<li>" . $event['message'] . "</li>\n";
    }

    /**
     * Logging method
     *
     * @access public
     * @param  string Log message
     * @return boolean
     */
    function log($text) {
        $event['message'] = $text;
        return $this->notify($event);
    }
}
