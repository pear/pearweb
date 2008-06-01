<?php
/**
 * Class to handle notes
 *
 * @class   note
 * @package pearweb
 * @author  Stig S. Bakken <ssb@fast.no>
 * @author  Tomas V.V. Cox <cox@php.net>
 * @author  Martin Jansen <mj@php.net>
 */
class note
{
    static function add($value, $note, $author = '')
    {
        global $dbh, $auth_user;
        if (empty($author)) {
            $author = $auth_user->handle;
        }

        $nid = $dbh->nextId('notes');
        $sql = 'INSERT INTO notes (id, uid, nby, ntime, note) VALUES(?,?,?,?,?)';
        $stmt = $dbh->prepare($sql);
        $res = $dbh->execute($stmt, array($nid, $value, $author,
                             gmdate('Y-m-d H:i'), $note));
        if (DB::isError($res)) {
            return $res;
        }
        return true;
    }

    static function remove($id)
    {
        global $dbh;
        $res = $dbh->query('DELETE FROM notes WHERE id = ' . (int)$id);
        if (DB::isError($res)) {
            return $res;
        }
        return true;
    }

    static function removeAll($value)
    {
        global $dbh;
        $res = $dbh->query("DELETE FROM notes WHERE uid = ". $dbh->quoteSmart($value));
        if (DB::isError($res)) {
            return $res;
        }
        return true;
    }

    static function getAll($user)
    {
        global $dbh;
        $sql = 'SELECT id, nby, ntime, note FROM notes WHERE uid = ? ORDER BY ntime';
        return $dbh->getAll($sql, array($user));
    }
}