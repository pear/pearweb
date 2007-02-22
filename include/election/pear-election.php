<?php
class PEAR_Election
{
    var $dbh;
    var $karma;
    var $user;
    function PEAR_Election()
    {
        $this->dbh = &$GLOBALS['dbh'];
        $this->user = isset($GLOBALS['auth_user']) ? $GLOBALS['auth_user']->handle : false;
        $this->karma =& new Damblan_Karma($this->dbh);
    }

    function listElections()
    {
        if ($this->karma->has($this->user, 'pear.admin')) {
            return $this->dbh->getAll('
                SELECT
                    IF(votestart > NOW(),"no","yes") as active, elections.*
                FROM elections
                ORDER BY votestart DESC
            ', array(), DB_FETCHMODE_ASSOC);
        } else {
            // if we aren't admin, we can't touch other people's elections
            return $this->dbh->getAll('
                SELECT 
                    IF(votestart > NOW(),"no","yes") as active, elections.*
                FROM elections WHERE
                    votestart > NOW() AND
                    creator=?
            ', array($this->user), DB_FETCHMODE_ASSOC);
        }
    }

    function electionExists($id)
    {
        return $this->dbh->getOne('SELECT id FROM elections WHERE id=?', array($id));
    }

    function setupChoices($id, $info)
    {
        $all = $this->dbh->getAssoc('
            SELECT choice,summary,summary_link
            FROM election_choices
            WHERE election_id=?
            ORDER BY choice
        ', false, array($id), DB_FETCHMODE_ASSOC);
        for ($i = 1; $i <= $_POST['choices']; $i++) {
            if (isset($all[$i])) {
                $info['summary' . $i] = $all[$i]['summary'];
                $info['summary_link' . $i] = $all[$i]['summary_link'];
            } else {
                $info['summary' . $i] = 
                    empty($_POST['summary' . $i]) ? '' : $_POST['summary' . $i];
                $info['summary_link' . $i] = 
                    empty($_POST['summary_link' . $i]) ? '' : $_POST['summary_link' . $i];
            }
        }
        $info['choices'] = $_POST['choices'];
        return $info;
    }

    function getInfo($id)
    {
        $all = $this->dbh->getAll('
            SELECT
                purpose, detail,
                YEAR(votestart) as year, MONTH(votestart) as month,
                DAYOFMONTH(votestart) as day, voteend - votestart as length,
                minimum_choices as minimum, maximum_choices maximum,
                eligiblevoters, COUNT(c.choice) as choices
            FROM elections e, election_choices c
            WHERE id=? AND c.election_id = e.id
            GROUP BY c.election_id
        ', array($id), DB_FETCHMODE_ASSOC);
        if (!count($all)) {
            return false;
        }
        $info = $all[0];
        return $info;
    }

    function validateStep1($new = true)
    {
        $error = array();
        if (empty($_POST['purpose'])) {
            $error[] = 'Election Purpose (summary) is required'; 
        }
        if (empty($_POST['choices'])) {
            $error[] = 'Number of Choices is required'; 
        } else {
            if (!is_numeric($_POST['choices']) ||
                  ((int) $_POST['choices'] != $_POST['choices'])) {
                $error[] = 'Number of Choices must be an integer';
            }
            if ($_POST['choices'] < 2 || $_POST['choices'] > 20) {
                $error[] = 'Number of Choices must be between 2 and 20';
            }
        }
        if (empty($_POST['eligiblevoters'])) {
            $error[] = 'Eligible Voters is required';
        } else {
            if (!is_numeric($_POST['eligiblevoters'])) {
                $error[] = 'Eligible Voters must be "PEAR Developers" or "General PHP Public"';
            }
            if ($_POST['eligiblevoters'] != 1 && $_POST['eligiblevoters'] != 2) {
                $error[] = 'Eligible Voters must be "PEAR Developers" or "General PHP Public"';
            }
        }
        if (empty($_POST['detail'])) {
            $error[] = 'Election detail is required'; 
        }
        $nextyear = date('Y') + 1;
        $thisyear = $nextyear - 1;
        if (empty($_POST['year'])) {
            $error[] = 'Year is required';
        } else {
            if (!is_numeric($_POST['year']) || $_POST['year'] != (int) $_POST['year']) {
                $error[] = 'Month is invalid';
            } elseif ($_POST['year'] != $nextyear && $_POST['year'] != $thisyear) {
                $error[] = 'Year is invalid, must be next year or this year';
            }
        }
        if (empty($_POST['month'])) {
            $error[] = 'Month is required';
        } else {
            if (!is_numeric($_POST['month']) || $_POST['month'] != (int) $_POST['month']) {
                $error[] = 'Month is invalid';
            } elseif ($_POST['month'] < 1 || $_POST['month'] > 12) {
                $error[] = 'Month is invalid, must be 1-12';
            } else {
                if ($_POST['month'] < 10 && $_POST['month'][0] != '0') {
                    $_POST['month'] = '0' . $_POST['month'];
                }
            }
        }
        if (empty($_POST['day'])) {
            $error[] = 'Day is required';
        } else {
            if (!is_numeric($_POST['day']) || $_POST['day'] != (int) $_POST['day']) {
                $error[] = 'Day is invalid';
            } elseif ($_POST['day'] < 1 || $_POST['day'] > date('d', strtotime(
                  $_POST['year'] . '-' . $_POST['month'] . '-01 +1 month -1 day'))) {
                $error[] = 'Day is invalid, must be 1-' . date('d', strtotime(
                    $_POST['year'] . '-' . $_POST['month'] . '-01 +1 month -1 day'));
            } else {
                if ($_POST['day'] < 10 && $_POST['day'][0] != '0') {
                    $_POST['day'] = '0' . $_POST['day'];
                }
            }
        }
        $date = $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'];
        if (strtotime($date) != strtotime(date('Y-m-d', strtotime($date)))) {
            $error[] = 'Full date is invalid';
        }
        if ($new && (strtotime($date) - time()) / 86400 < 29.0) {
            $error[] = 'Voting must start at least 30 days from today';
        }
        if (empty($_POST['length'])) {
            $error[] = 'Election length is required';
        } else {
            if (!is_numeric($_POST['length']) || $_POST['length'] != (int) $_POST['length']) {
                $error[] = 'Voting length is invalid';
            } elseif ($_POST['length'] < 1 || $_POST['length'] > 14) {
                $error[] = 'Voting length must be between 1 and 14 days';
            }
        }
        if (empty($_POST['minimum'])) {
            $error[] = 'Election minimum votes needed is required';
        } else {
            if (!is_numeric($_POST['minimum']) ||
                  $_POST['minimum'] != (int) $_POST['minimum']) {
                $error[] = 'Minimum votes needed is invalid';
            } elseif ($_POST['minimum'] < 1 || $_POST['minimum'] > 19) {
                $error[] = 'Minimum votes needed must be between 1 and 19';
            }
        }
        if (empty($_POST['maximum'])) {
            $error[] = 'Election maximum votes needed is required';
        } else {
            if (!is_numeric($_POST['maximum']) ||
                  $_POST['maximum'] != (int) $_POST['maximum']) {
                $error[] = 'Maximum votes needed is invalid';
            } elseif ($_POST['maximum'] < 1 || $_POST['maximum'] > 19) {
                $error[] = 'Maximum votes needed must be between 1 and 19';
            }
        }
        if ($_POST['maximum'] < $_POST['minimum']) {
            $error[] = 'Maximum votes needed must be greater or the same as minimum votes needed';
        }
        if ($_POST['minimum'] > $_POST['choices']) {
            $error[] = 'Minimum votes needed must be less than or equal to the number of choices';
        }
        if ($_POST['maximum'] > $_POST['choices']) {
            $error[] = 'Maximum votes allowed must be less than or equal to the number of choices';
        }
        return $error;
    }

    function validateStep2()
    {
        $error = array();
        if (isset($_POST['add1choice'])) {
            $error[] = 'Added one choice';
            $_POST['choices']++;
        } elseif (isset($_POST['delete1choice'])) {
            if ($_POST['choices'] <= $_POST['maximum']) {
                $error[] = 'Cannot delete, must have at least the as many choices as ' .
                    'the Maximum votes allowed';
            } else {
                $error[] = 'Deleted last choice';
                $_POST['choices']--;
            }
        } else {
            for ($i = 1; $i <= $_POST['choices']; $i++) {
                if (empty($_POST['summary' . $i])) {
                    $error[] = 'Summary for Choice #' . $i . ' must not be empty';
                }
                if (empty($_POST['summary_link' . $i])) {
                    $error[] = 'Link to more info for Choice #' . $i . ' must not be empty';
                }
            }
        }
        return $error;
    }

    function saveNewElection()
    {
        $startdate = date('Y-m-d',
            strtotime($_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day']));
        $enddate = date('Y-m-d',
            strtotime($startdate . ' +' . $_POST['length'] . ' days'));
        $this->dbh->query('
            INSERT INTO elections
             (purpose, detail, votestart, voteend, creator, createdate, minimum_choices,
              maximum_choices, eligiblevoters)
            VALUES(?,?,?,?,?,NOW(),?,?,?)
        ', array(
            $_POST['purpose'],
            $_POST['detail'],
            $startdate,
            $enddate,
            $this->user,
            $_POST['minimum'],
            $_POST['maximum'],
            $_POST['eligiblevoters']
            ));
        $id = $this->dbh->phptype == 'mysql' ?
            mysql_insert_id() : mysqli_insert_id($this->dbh->connection);
        for ($i = 1; $i <= $_POST['choices']; $i++) {
            $this->dbh->query('
                INSERT INTO election_choices
                    (election_id, choice, summary, summary_link)
                VALUES(?,?,?,?)
            ', array($id, $i, $_POST['summary' . $i], $_POST['summary_link' . $i]));
        }
    }

    function saveEditedElection()
    {
        $id = $_POST['election_id'];
        $info = $this->getInfo($id);
        $startdate = date('Y-m-d',
            strtotime($_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day']));
        $enddate = date('Y-m-d',
            strtotime($startdate . ' +' . $_POST['length'] . ' days'));
        $this->dbh->query('
            UPDATE elections
            SET
                purpose=?,
                detail=?,
                votestart=?,
                voteend=?,
                minimum_choices=?,
                maximum_choices=?,
                eligiblevoters=?
            WHERE
                id=?
        ', array(
            $_POST['purpose'],
            $_POST['detail'],
            $startdate,
            $enddate,
            $_POST['minimum'],
            $_POST['maximum'],
            $_POST['eligiblevoters'],
            $id
            ));
        $this->dbh->query('DELETE FROM election_choices WHERE election_id=?', array($id));
        for ($i = 1; $i <= $_POST['choices']; $i++) {
            $this->dbh->query('
                INSERT INTO election_choices
                    (election_id, choice, summary, summary_link)
                VALUES(?,?,?,?)
            ', array($id, $i, $_POST['summary' . $i], $_POST['summary_link' . $i]));
        }
    }

    function canEdit($id)
    {
        if ('yes' == $this->dbh->getOne('
            SELECT
                IF(votestart > NOW(),"no","yes") FROM elections
                WHERE id=?', array($id))) {
            // cannot edit active or old elections
            return false;
        }
        if ($this->karma->has($this->user, 'pear.admin')) {
            return true;
        }
        if (!$this->electionExists($id)) {
            return false;
        }
        if ($this->user == $this->dbh->getOne('SELECT creator FROM elections WHERE id=?', 
              array($id))) {
            return true;
        }
        return false;
    }
}