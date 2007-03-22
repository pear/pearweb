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
        $user = isset($GLOBALS['auth_user'])
        ? $GLOBALS['auth_user']->handle : '';
        if ($user) {
            $sql = "
                INSERT INTO {$this->notesTableName}
                (page_url, user_name, user_handle, note_text, note_time,
                 note_approved, note_approved_by, note_deleted)
                VALUES (?, ?, ?, ?, NOW(), ?, ?, 0)
            ";

            // always approve pear.dev account holder comments, moderate others
            $res = $this->dbc->query($sql, array($pageUrl, $userName, $user, $note,
                auth_check('pear.dev') ? 'yes' : $approved,
                auth_check('pear.dev') ? $user : ''));
        } else {
            $sql = "
                INSERT INTO {$this->notesTableName}
                (page_url, user_name, user_handle, note_text, note_time,
                 note_approved, note_approved_by, note_deleted)
                VALUES (?, ?, ?, ?, NOW(), ?, null, 0)
            ";

            $res = $this->dbc->query($sql, array($pageUrl, $userName, $user, $note, $approved));
        }

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
     * @param  string      $url    The url of the comments
     * @param  string|bool $status The status of the comment.. whether
     *                             it's approved, unapproved, pending.  If
     *                             a boolean is passed in, determine whether to
     *                             display approved/pending, or just approved
     * @param  bool        $all    if true, return all comments matching this status
     *
     * @return mixed  $res    It returns an error object if there was an error
     *                        executing the query, will return an empty array
     *                        if there was nothing returned from the query, or
     *                        this will return an associative array of the comments
     *                        per page.
     */
    function getPageComments($url, $status = '1', $all = false)
    {

        if ($all) {
            $sql = "
                SELECT *
                 FROM {$this->notesTableName}
                  WHERE
                  note_approved = ?
            ";

            $res = $this->dbc->getAll($sql, array($status), DB_FETCHMODE_ASSOC);
        } else {
            if ($status === true) {
                $sql = "
                    SELECT *
                     FROM {$this->notesTableName}
                      WHERE page_url = ?
                      AND note_approved = 'yes' OR note_approved = 'pending'
                ";
                $res = $this->dbc->getAll($sql, array($url), DB_FETCHMODE_ASSOC);
            } elseif ($status === false) {
                $sql = "
                    SELECT *
                     FROM {$this->notesTableName}
                      WHERE page_url = ?
                      AND note_approved = 'yes'
                ";
                $res = $this->dbc->getAll($sql, array($url), DB_FETCHMODE_ASSOC);
            } else {
                $sql = "
                    SELECT *
                     FROM {$this->notesTableName}
                      WHERE page_url = ?
                      AND note_approved = ?
                ";

                $res = $this->dbc->getAll($sql, array($url, $status), DB_FETCHMODE_ASSOC);
            }
        }

        if (PEAR::isError($res)) {
            return $res;
        }

        return (array)$res;
    }
    // }}}
    // {{{ public function updateCommentList
    /**
     * Update Comment List
     *
     * This function will update a current comment (status, note text, url, 
     * username, etc)
     *
     * @access public
     * @param  integer $noteId   The id of the note to update
     * 
     * @param  string $status    The status of the note, default = 'pending'
     *
     * @return mixed  $res       An error if an error object occured with the query
     */
    function updateCommentList($noteIds, $status)
    {
        $qs = array();
        $noteIdList = array($status);
        foreach ($noteIds as $noteId) {
            $noteIdList[]   = $noteId;
            $qs[] = 'note_id = ?';
        }
        $qs = implode(' OR ', $qs);

        $sql = "
            UPDATE {$this->notesTableName}
             SET note_approved   = ?
              WHERE $qs
              LIMIT ?
        ";
        $noteIdList[] = count($noteIdList);

        $res = $this->dbc->query($sql, $noteIdList);

        if (PEAR::isError($res)) {
            return $res;
        }

        return true;
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
        $sql = "
            UPDATE {$this->notesTableName}
             SET page_url   = ?,
                 user_name  = ?,
                 note_approved   = ?
              WHERE note_id = ?
              LIMIT 1
        ";

        $res = $this->dbc->query($sql, array($url, $userName, $approved, $noteId));

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
             SET note_deleted = 1, note_approved='no'
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

    function display($comment)
    {
        $time       = date('d-M-Y H:i', strtotime($comment['note_time']));
        $noteId     =  (int)$comment['note_id'];
        $userHandle = $comment['user_handle'] ? 
            '<a href="/user/' . $comment['user_handle'] . '">' . $comment['user_handle'] .
            '</a>' :
            htmlentities($comment['user_name']);
        $pending    = $comment['note_approved'] == 'pending';
        $id = $comment['page_url'];

        /**
         * For now then we can implement more things like
         * code highlight, etc.
         */
        $comment    = nl2br(htmlentities($comment['note_text']));
        $linkUrl    = '<a href="#' . $noteId . '">' . $time . '</a>';
        $linkName   = '<a name="#' . $noteId . '"></a>';
        include dirname(dirname(dirname(__FILE__))) . '/templates/notes/note.tpl.php';
    }
}
// }}}
