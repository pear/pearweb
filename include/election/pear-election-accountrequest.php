<?php
class PEAR_Election_Accountrequest
{
    var $dbh;
    var $id;
    var $created_on;
    var $handle;
    var $salt;
    var $email;

    function PEAR_Election_Accountrequest()
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
            FROM election_account_request
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
    function addRequest($handle, $email, $firstName, $lastName, $additionnalData)
    {
        $data = array(
            'handle'     => $handle,
            'firstname'  => $firstName,
            'lastname'   => $lastName,
            'email'      => $email,
            'purpose'    => 'vote in general election'
        );

        $data = array_merge($additionnalData, $data);

        $useradd = user::add($data);

        if (is_array($useradd)) {
            return $useradd;
        }

        $salt = $this->_makeSalt($handle);
        $created_on = gmdate('Y-m-d H:i:s');

        $query = '
        insert into election_account_request (created_on, handle, email, salt)
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
        $query = 'delete from election_account_request where salt=?';

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
        $sth = $this->dbh->query($query, array($id, $this->handle, 'pear.voter', 'pearweb'));

        if (!DB::isError($sth)) {
            note::add("uid", $this->handle, "Account opened", 'pearweb');
            $msg = "Your PEAR voter account has been opened.\n"
                . "You can now participate in the elections  by going to\n"
                . "    http://" . PEAR_CHANNELNAME . "/election/";
            $xhdr = "From: pear-webmaster@lists.php.net";
            mail($user->email, "Your PEAR Account Request", $msg, $xhdr, "-f bounce-no-user@php.net");
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
        $old = gmdate('Y-m-d H:i:s', time() - 900);
        $findquery = '
            select handle from election_account_request
            where created_on < ?';
        $all = $this->dbh->getAll($findquery, array($old));
        // purge reserved usernames as well as their account requests
        if (is_array($all)) {
            foreach ($all as $data) {
                $this->dbh->query('
                    DELETE FROM users WHERE handle=?
                ', array($data[0]));
            }
        }
        $query = '
            delete from election_account_request
            where created_on < ?';
        // purge out-of-date account requests
        return $this->dbh->query($query, array($old));
    }
}
?>