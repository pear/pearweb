<?php
class PEAR_Bug_Accountrequest
{
    var $dbh;
    var $id;
    var $created_on;
    var $handle;
    var $salt;
    var $email;

    function PEAR_Bug_Accountrequest()
    {
        $this->dbh = &$GLOBALS['dbh'];
        $this->user = isset($GLOBALS['auth_user']) ? $GLOBALS['auth_user']->handle : false;
        $this->cleanOldRequests();
    }

    function _makeSalt($handle)
    {
        list($usec, $sec) = explode(" ", microtime());
        return md5($handle . ((float)$usec + (float)$sec));
    }

    function find($salt)
    {
        $request = $this->dbh->getRow('
            SELECT id, created_on, salt, handle
            FROM bug_account_request
            WHERE salt=?
        ', array($salt), DB_FETCHMODE_ASSOC);

        if (count($request) > 0) {
            foreach ($request as $field => $value) {
                $this->$field = $value;
            }
            return true;
        }
        return false;
    }

    /**
     * Adds a request in the DB
     *
     * @return string salt
     */
    function addRequest($handle, $email, $name, $password, $password2, $ismd5)
    {
        if ($handle == $this->dbh->getOne('SELECT handle FROM users WHERE 
              handle=?', array($handle))) {
            $id = $this->dbh->nextId("karma");

            $query = "INSERT INTO karma VALUES (?, ?, ?, ?, NOW())";
            $sth = $this->dbh->query($query, array($id, $this->handle, 'pear.bug', 'pearweb'));
            return true;
        }

        list($firstname, $lastname) = explode(' ', $name, 2);
        $data = array(
            'handle'     => $handle,
            'firstname'  => $firstname,
            'lastname'   => $lastname,
            'email'      => $email,
            'purpose'    => 'bug tracker',
            'password'   => $password,
            'password2'  => $password2,
        );

        $useradd = user::add($data);
        
        if ($ismd5) {
            // password is double-md5()ed otherwise
            $this->dbh->query('UPDATE users set password=?', array($password));
        }

        if (is_array($useradd)) {
            return $useradd;
        }

        $salt = $this->_makeSalt($handle);
        $created_on = gmdate('Y-m-d H:i:s');

        $query = '
        insert into bug_account_request (created_on, handle, email, salt)
        values (?, ?, ?, ?)';

        $res = $this->dbh->query($query, array($created_on, $handle, $email, $salt));

        if (DB::isError($res)) {
            return $res;
        }

        //$this->find($salt);
        //$this->sendRequest();

        return $salt;
    }

    function deleteRequest()
    {
        $query = 'delete from bug_account_request where salt=?';

        return $this->dbh->query($query, array($this->salt));
    }

    function confirmRequest($salt)
    {
        if (!$this->find($salt)) {
            return PEAR::raiseError('cannot find request');
        }

        // activate the handle and grant karma
        // implicitly without human intervention
        // copied from the user class and Damblan_Karma

        $user =& new PEAR_User($this->dbh, $this->handle);
        if (@$user->registered) {
            return false;
        }
        @$arr = unserialize($user->userinfo);
        note::removeAll("uid", $this->handle);
        $user->set('registered', 1);
        if (is_array($arr)) {
            $user->set('userinfo', $arr[1]);
        }
        $user->set('created', gmdate('Y-m-d H:i'));
        $user->set('createdby', 'pearweb');
        $user->store();

        $id = $this->dbh->nextId("karma");

        $query = "INSERT INTO karma VALUES (?, ?, ?, ?, NOW())";
        $sth = $this->dbh->query($query, array($id, $this->handle, 'pear.bug', 'pearweb'));

        if (!DB::isError($sth)) {
            note::add("uid", $this->handle, "Account opened", 'pearweb');
            $msg = "Your PEAR bug tracker account has been opened.\n"
                . "Bugs you have opened will now be displayed, and you can\n"
                . "add new comments to existing bugs";
            $xhdr = "From: pear-webmaster@lists.php.net";
            mail($user->email, "Your PEAR Bug Tracker Account Request", $msg, $xhdr, "-f bounce-no-user@php.net");
            $this->deleteRequest();
            return true;
        }
        return false;
    }

    function listRequests()
    {
    }

    function cleanOldRequests()
    {
        $old = gmdate('Y-m-d H:i:s', time() - 90000);
        $findquery = '
            select handle from bug_account_request
            where created_on < ?';
        $all = $this->dbh->getAll($findquery, array($old));
        // purge reserved usernames as well as their account requests
        if (is_array($all)) {
            foreach ($all as $data) {
                $this->dbh->query('
                    DELETE FROM users WHERE handle=?
                ', array($data[0]));
                $this->dbh->query('
                    DELETE FROM bugdb WHERE handle=?
                ', array($data[0]));
                $this->dbh->query('
                    DELETE FROM bugdb_comments WHERE handle=?
                ', array($data[0]));
            }
        }
        $query = '
            delete from bug_account_request
            where created_on < ?';
        // purge out-of-date account requests
        return $this->dbh->query($query, array($old));
    }
}
?>