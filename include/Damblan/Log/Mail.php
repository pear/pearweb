<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003 The PEAR Group                                    |
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

require_once "Log.php";
require_once "Log/observer.php";

/**
 * Observer class for logging via email
 *
 * @author Martin Jansen <mj@php.net>
 * @extends Log_observer
 * @version $Revision$
 * @package Damblan
 */
class Damblan_Log_Mail extends Log_observer {

    var $_recipients = "";
    var $_from = "\"PEAR System Administrators\" <pear-sys@php.net>";
    var $_reply_to = "";
    var $_subject = "";

    /**
     * Generate logging email
     *
     * @param array Array containing the log information
     * @return void
     */
    function notify($event) {
        $headers = "From: " . $this->_from;
        if (!empty($this->_reply_to)) {
            $headers .= "\r\nReply-To: " . $this->_reply_to;
        }

        $ok = mail($this->_recipients, $this->_subject, $event['message'], 
                   $headers, "-f pear-sys@php.net");

        if ($ok === false) {
            trigger_error("Email notification routine failed.", 
                          E_USER_WARNING);
        }
    }

    /**
     * Set mail recipients
     *
     * @access public
     * @param  string Recipients
     * @return void
     */
    function setRecipients($r) {
        $this->_recipients = $r;
    }

    /**
     * Set mail sender
     *
     * @access public
     * @param  string From line
     * @return void
     */
    function setFrom($f) {
        $this->_from = $f;
    }

    /**
     * Set mail sender
     *
     * @access public
     * @param  string From line
     * @return void
     */
    function setReplyTo($r) {
        $this->_reply_to = $r;
    }

    /**
     * Set mail subject
     *
     * @access public
     * @param  string Subject
     * @return void
     */
    function setSubject($s) {
        $this->_subject = $s;
    }
}
?>
