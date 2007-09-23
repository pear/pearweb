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
            'purpose'    => 'vote in general election',
            'fromt_site' => 'pear',
        );

        $data = array_merge($additionnalData, $data);

        include_once 'pear-database-user.php';
        $useradd = user::add($data, false, true);

        if (is_array($useradd) || DB::isError($useradd)) {
            return $useradd;
        }

        $salt = $this->_makeSalt($handle);
        $created_on = gmdate('Y-m-d H:i:s');

        $query = '
        INSERT INTO election_account_request (created_on, handle, email, salt)
        VALUES (?, ?, ?, ?)';

        $res = $this->dbh->query($query, array($created_on, $handle, $email, $salt));
        if (DB::isError($res)) {
            user::remove($handle);
            return $res;
        }

        //$this->find($salt);
        //$this->sendRequest();

        return $salt;
    }

    function deleteRequest()
    {
        $query = 'DELETE FROM election_account_request WHERE salt = ?';
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

        include_once 'pear-database-user.php';
        $user = user::info($this->handle, null, 0);
        if (!isset($user['registered'])) {
            return PEAR::raiseError('Error - user request was deleted, please try again');
        }

        @$arr = unserialize($user['userinfo']);
        include_once 'pear-database-note.php';
        note::removeAll("uid", $this->handle);

        $data = array();
        $data['registered'] = 1;
        if (is_array($arr)) {
            $data['userinfo'] = $arr[1];
        }
        $data['created']   = gmdate('Y-m-d H:i');
        $data['createdby'] = 'pearweb';

        if (PEAR::isError($e = user::update($data, true))) {
            return $e;
        }

        $query = 'INSERT INTO karma VALUES (?, ?, ?, ?, NOW())';

        $id = $this->dbh->nextId('karma');
        $sth = $this->dbh->query($query, array($id, $this->handle, 'pear.voter', 'pearweb'));
        $id = $this->dbh->nextId('karma');
        $sth = $this->dbh->query($query, array($id, $this->handle, 'pear.bug', 'pearweb'));

        if (!DB::isError($sth)) {
            note::add("uid", $this->handle, "Account opened", 'pearweb');
            $msg = "Your PEAR voter account has been opened.\n"
                . "You can now participate in the elections  by going to\n"
                . "    http://" . PEAR_CHANNELNAME . "/election/";
            $xhdr = "From: pear-webmaster@lists.php.net";
            if (!DEVBOX){
                mail($user['email'], "Your PEAR Account Request", $msg, $xhdr, "-f bounce-no-user@php.net");
            }
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
        $old = gmdate('Y-m-d H:i', strtotime('-15 minutes'));
        $findquery = 'SELECT handle FROM election_account_request WHERE created_on < ?';
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
        $query = 'DELETE FROM election_account_request WHERE created_on < ?';
        // purge out-of-date account requests
        return $this->dbh->query($query, array($old));
    }
}
?>