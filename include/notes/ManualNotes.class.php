<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2007 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: David Coallier <davidc@php.net>                             |
   |                                                                      |
   +----------------------------------------------------------------------+
 */
// {{{ class Manual_Notes
/**
 * Manual Notes
 *
 * This class will be handling most of the 
 * manual notes adding, deleting, approving, etc
 *
 * @package pearweb
 * @author  David Coallier <davidc@php.net>
 * @uses    DB
 * @version 1.0
 */
class Manual_Notes
{
    // {{{ properties
    /**
     * Database Connection
     *
     * This variables holds the database connection
     * into a variable.
     *
     * @access protected
     * @var    Object    $dbc  Database Connection
     */
    var $dbc;

    /**
     * Notes table
     *
     * This is the variable that holds
     * the name of the manual notes table.
     *
     * @access protected
     * @var    string    $notesTableName The notes table name
     */
    var $notesTableName = 'manual_notes';

    // }}}
    // {{{ php5 Constructor
    function __construct() 
    {
        global $dbh;
        $this->dbc = $dbh;
    }
    // }}}
    // {{{ php4 Constructor
    function Manual_Notes()
    {
        $this->__construct();
    }
    // }}}
    // {{{ public function addComment
    /**
     * Add a comment
     *
     * This function will add a comment to the database
     * using the credentials passed to it.
     *
     * @access public
     * @param  string $pageUrl  The page url
     * @param  string $userName The user adding the comment
     * @param  string $note     The note to add
     * @param  string $approved Is it approved ? "Default: pending"
     */
    function addComment($pageUrl, $userName, $note, $approved = 'pending')
    {
        $pageUrl  = $this->_safeSql($pageUrl);
        $userName = $this->_safeSql($userName);
        $note     = $this->_safeSql($note);
        $aproved  = $this->_safeSql($approved);

        $sql = "
            INSERT INTO {$this->notesTableName}
            VALUES (null, $pageUrl, $userName, $note, NOW(), $approved, null, 0)
        ";

        $res = $this->dbc->query($sql);

        if (PEAR::isError($res)) {
            return $res;
        }

        return true;
    }
    // }}}
    // {{{ public function getPageComments
    /**
     * Get Page Comments
     *
     * This function will get the comments depending
     * on whether a method will need approved, unapproved
     * pending comments, etc. (Per manual page)
     *
     * @access public
     * @param  string $url    The url of the comments
     * @param  string $status The status of the comment.. whether
     *                        it's approved, unapproved, pending
     *
     * @return mixed  $res    It returns an error object if there was an error
     *                        executing the query, will return an empty array
     *                        if there was nothing returned from the query, or
     *                        this will return an associative array of the comments
     *                        per page.
     */
    function getPageComments($url, $status = '1')
    {
        $url    = $this->_safeSql($url);
        $status = $this->_safeSql($status);

        $sql = "
            SELECT *
             FROM {$this->notesTableName}
              WHERE page_url = $url
              AND note_approved = $status
        ";

        $res = $this->dbc->getAll($sql);

        if (PEAR::isError($res)) {
            return $res;
        }

        return (array)$res;
    }
    // }}}
    // {{{ public function updateComment
    /**
     * Update Comment
     *
     * This function will update a current comment (status, note text, url, 
     * username, etc)
     *
     * @access public
     * @param  integer $noteId   The id of the note to update
     * @param  string  $url      The url of the page that the
     *                           note belongs to.
     *
     * @param  string  $userName The user[name|address] of the author
     *                           of the note.
     *
     * @param  string $approved  The status of the note, default = 'pending'
     *
     * @return mixed  $res       An error if an error object occured with the query
     */
    function updateComment($noteId, $url, $userName, $approved)
    {
        $noteId   = $this->_safeSql($noteId);
        $url      = $this->_safeSql($url);
        $userName = $this->_safeSql($userName);
        $approved = $this->_safeSql($approved);

        $sql = "
            UPDATE {$this->notesTableName}
             SET page_url   = $url,
                 user_name  = $userName,
                 approved   = $approved
              WHERE note_id = $noteId
              LIMIT 1
        ";

        $res = $this->dbc->query($sql);

        if (PEAR::isError($res)) {
            return $res;
        }

        return true;
    }
    // }}}
    // {{{ public function deleteComments
    /**
     * Delete Comments
     *
     * This function will delete a comment by it's note_id
     * This function will mainly be used by administrators and 
     * people with enough karma to manage comments.
     *
     * @access public
     * @param  Array $note_ids  The array of the notes to delete
     *
     * @return Mixed   $res      An error object if query was erroneous, bool
     *                           if it was successful
     */
    function deleteComments($note_ids)
    {
        if (!is_array($note_ids)) {
            return false;
        }

        /**
         * Let's just format the note ids so they are simple to
         * read within an IN()
         */
        $notes = "'" . implode(', ', (int)$note_ids) . "'";

        $sql = "
            UPDATE {$this->notesTableName}
             SET note_deleted = 'yes'
              WHERE note_id IN($notes)
        ";

        $res = $this->dbc->query($sql);

        if (PEAR::isError($res)) {
            return $res;
        }

        return true;
    }
    // }}}
    // {{{ public function deleteSingleComment
    /**
     * Delete a single comment
     *
     * This function will delete a single comment
     * by it's id.
     *
     * @access public
     * @param  Integer $note_id  The note id to delete
     * @return Mixed   $res      Error object if query is an error
     *                           otherwise return a bool on success
     */
    function deleteSingleComment($note_id)
    {
        $res = $this->deleteComments(array($note_id));

        if (PEAR::isError($res)) {
            return $res;
        }

        return true;
    }
    // }}}
    // {{{ protected function _safeSql
    /**
     * Safe SQL 
     *
     * This is a rudimentary function that will 
     * make sure the string returned is safe...
     *
     * @access protected
     * @param  mixed     $var    String, int, etc
     * @return mixed     $newvar The new string, int, etc.
     */
    function _safeSql($var, $escape = true)
    {
        if (strlen(trim($var)) == 0) {
            return null;
        }

        if (!$escape) {
            return "'" . $var . "'";
        }

        if (ctype_digit($var)) {
            return (int)$var;
        }

        if (ctype_alpha($var)) {
            return "'$var'";
        }

        if (function_exists('mysql_real_escape_string')) {
            return "'" . mysql_real_escape_string($var) . "'";
        }

        return "'" . mysql_escape_string($var) . "'";
    }
    // }}}
}
// }}}
