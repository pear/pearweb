<?php
require 'Text/Wiki.php';
class PEAR_Voter
{
    var $dbh;
    var $user = false;
    var $voteSalt;
    function PEAR_Voter()
    {
        $this->dbh = &$GLOBALS['dbh'];
        $this->user = isset($GLOBALS['auth_user']) ? $GLOBALS['auth_user']->handle : false;
    }

    function listCurrentElections()
    {
        if ($this->user) {
            $all = $this->dbh->getAll('
                SELECT
                    IF(e.votestart > NOW(),"no","yes") as active,
                    e.purpose,
                    e.votestart,
                    e.voteend,
                    e.id
                FROM
                    elections e
                WHERE
                    e.votestart > NOW() OR
                     (e.votestart < NOW() AND e.voteend >= NOW())
                ORDER BY e.votestart
            ', array(), DB_FETCHMODE_ASSOC);
            foreach ($all as $i => $election) {
                $vote = $this->dbh->getOne('SELECT COUNT(*) FROM
                    election_handle_votes
                    WHERE election_id=? AND handle=?', array($election['id'], $this->user));
                $all[$i]['voted'] = $vote ? 'yes' : 'no';
            }
        } else {
            $all = $this->dbh->getAll('
                SELECT
                    IF(e.votestart > NOW(),"no","yes") as active,
                    "no" as voted,
                    e.purpose,
                    e.votestart,
                    e.voteend,
                    e.id
                FROM
                    elections e
                WHERE
                    e.votestart > NOW() OR
                     (e.votestart < NOW() AND e.voteend >= NOW())
                ORDER BY e.votestart
            ', array(), DB_FETCHMODE_ASSOC);
        }
        if (!is_array($all)) {
            return array();
        }
        return $all;
    }

    function listCompletedElections()
    {
        if ($this->user) {
            $all = $this->dbh->getAll('
                SELECT
                    e.purpose,
                    e.votestart,
                    e.voteend,
                    e.id
                FROM
                    elections e, election_results r
                WHERE
                    e.id = r.election_id AND
                    e.voteend < NOW()
                GROUP BY e.id
                ORDER BY e.voteend DESC
            ', array(), DB_FETCHMODE_ASSOC);
            foreach ($all as $i => $election) {
                $results = $this->dbh->getAll('
                    SELECT * FROM election_results
                    WHERE election_id=? ORDER BY votepercent DESC
                ', array($election['id']), DB_FETCHMODE_ASSOC);
                $vote = $this->dbh->getOne('SELECT COUNT(*) FROM
                    election_handle_votes
                    WHERE election_id=? AND handle=?', array($election['id'], $this->user));
                $all[$i]['voted'] = $vote ? 'yes' : 'no';
                $all[$i]['results'] = $results;
            }
        } else {
            $all = $this->dbh->getAll('
                SELECT
                    "no" as voted,
                    e.purpose,
                    e.votestart,
                    e.voteend,
                    e.id,
                    c.summary as winner,
                    c.summary_link as winnerlink
                    e.id
                FROM
                    elections e, election_choices c, election_results r
                WHERE
                    e.id = r.election_id AND
                    c.election_id = e.id AND
                    e.voteend < NOW()
                ORDER BY e.voteend DESC
            ', array(), DB_FETCHMODE_ASSOC);
            foreach ($all as $i => $election) {
                $results = $this->dbh->getAll('
                    SELECT * FROM election_results
                    WHERE election_id=? ORDER BY votepercent DESC
                ', array($election['id']), DB_FETCHMODE_ASSOC);
                $all[$i]['results'] = $results;
            }
        }
        if (!is_array($all)) {
            return array();
        }
        return $all;
    }

    function listAllElections()
    {
        if (!$this->user) {
            return array();
        }
        $all = $this->dbh->getAll('
            SELECT
                e.purpose,
                e.id
            FROM
                elections e, election_handle_votes v
            WHERE
                v.election_id = e.id AND
                v.handle=?
            ORDER BY e.voteend DESC
        ', array($this->user), DB_FETCHMODE_ASSOC);
        if (!is_array($all)) {
            return array();
        }
        return $all;
    }

    function electionExists($id)
    {
        return $this->dbh->getOne('SELECT COUNT(id) FROM elections WHERE id=?', array($id));
    }

    function electionInfo($id)
    {
        $info = $this->dbh->getAll('
            SELECT * FROM elections WHERE id = ?
            ', array($id), DB_FETCHMODE_ASSOC);
        if (!is_array($info)) {
            return false;
        }
        $info = $info[0];
        $choices = $this->dbh->getAll('
            SELECT * FROM election_choices WHERE election_id = ?
            ORDER BY choice
            ', array($id), DB_FETCHMODE_ASSOC);
        $info['choices'] = $choices;
        $info['results'] = $this->dbh->getAll('
            SELECT e.votepercent, c.summary, c.summary_link
            FROM election_results e, election_choices c
            WHERE e.election_id = ? AND
                c.election_id = e.election_id AND
                c.choice = e.choice
            ORDER BY e.votepercent DESC
        ', array($id), DB_FETCHMODE_ASSOC);
        $abstain = $this->dbh->getOne('
            SELECT COUNT(*) FROM election_votes_abstain
            WHERE election_id=?
        ', array($id));
       $wiki =& new Text_Wiki();
       $wiki->disableRule('wikilink');
       $info['detail'] = $wiki->transform($info['detail']);
        if ($info['maximum_choices'] > 1) {
            $total = $this->dbh->getOne('
                SELECT COUNT(*) FROM election_votes_multiple WHERE
                election_id=?
            ', array($id));
        } else {
            $total = $this->dbh->getOne('
                SELECT COUNT(*) FROM election_votes_single WHERE
                election_id=?
            ', array($id));
        }
        // percentage of abstaining voters
        if ($total + $abstain > 0) {
            $info['abstain'] = $abstain / ($total + $abstain);
        } else {
            $info['abstain'] = 0;
        }
        return $info;
    }

    function hasVoted($id)
    {
        return $this->dbh->getOne('
            SELECT COUNT(*) FROM election_handle_votes WHERE election_id=?
            AND handle=?', array($id, $this->user));
    }

    function canVote($id)
    {
        if ($this->hasVoted($id)) {
            return false;
        }
        $info = $this->electionInfo($id);
        if (strtotime($info['votestart']) - time() > 0) {
            // election is not active
            return false;
        }
        if (strtotime($info['voteend']) - time() < 0) {
            // election is finished
            return false;
        }
    }

    function getVoteSalt()
    {
        if (!isset($this->voteSalt)) {
            $this->voteSalt = date('YmdHis') . mt_rand(1, 999);
        }
        return $this->voteSalt;
    }

    function vote($id, $votes)
    {
        if (!$this->user) {
            return false;
        }
        if (!$this->electionExists($id)) {
            return false;
        }
        if ($this->hasVoted($id)) {
            return false;
        }
        if (!is_array($votes)) {
            return false;
        }
        $info = $this->electionInfo($id);
        if ($info['maximum_choices'] > 1) {
            if (count($votes) > $info['maximum_choices'] || count($votes) <
                  $info['minimum_choices']) {
                return false;
            }
            $table = 'election_votes_multiple';
        } else {
            if (count($votes) != 1) {
                return false;
            }
            $table = 'election_votes_single';
        }
        $vote_hash = md5($this->user . $this->getVoteSalt());
        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
        foreach ($votes as $vote) {
            $err = $this->dbh->query('
                    INSERT INTO ' . $table . '
                      (election_id, vote, vote_hash)
                      VALUES(?,?,?)', array($id, $vote, $vote_hash));
            if (PEAR::isError($err)) {
                // "rollback" the query
                $this->dbh->query('DELETE FROM ' . $table . '
                    WHERE election_id=? AND vote_hash=?', array($id, $vote_hash));
                PEAR::popErrorHandling();
                return false;
            }
        }
        $err = $this->dbh->query('
            INSERT INTO election_handle_votes
                (election_id, handle) VALUES (?,?)', array($id, $this->user));
        if (PEAR::isError($err)) {
            // "rollback" the query
            $this->dbh->query('DELETE FROM ' . $table . '
                WHERE election_id=? AND vote_hash=?', array($id, $vote_hash));
            $this->dbh->query('DELETE FROM election_handle_votes
                WHERE election_id=? AND handle=?', array($id, $this->user));
            PEAR::popErrorHandling();
            return false;
        }
        PEAR::popErrorHandling();
        return true;
    }

    function abstain($id)
    {
        if (!$this->user) {
            return false;
        }
        if (!$this->electionExists($id)) {
            return false;
        }
        if ($this->hasVoted($id)) {
            return false;
        }
        $vote_hash = md5($this->user . $this->getVoteSalt());
        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
        $err = $this->dbh->query('
            INSERT INTO election_votes_abstain
                (election_id, vote_hash) VALUES (?,?)', array($id, $vote_hash));
        if (PEAR::isError($err)) {
            PEAR::popErrorHandling();
            return false;
        }
        $err = $this->dbh->query('
            INSERT INTO election_handle_votes
                (election_id, handle) VALUES (?,?)', array($id, $this->user));
        if (PEAR::isError($err)) {
            $this->dbh->query('DELETE FROM election_votes_abstain
                WHERE election_id=? and vote_hash=?', array($id, $vote_hash));
            PEAR::popErrorHandling();
            return false;
        }
        PEAR::popErrorHandling();
        return true;
    }

    function retrieveVote($id, $salt)
    {
        if (!$this->electionExists($id)) {
            return false;
        }
        if (!$this->user) {
            return false;
        }
        if (!$this->hasVoted($id)) {
            return false;
        }
        $this->voteSalt = md5($this->user . $salt);
        if ($vote = $this->dbh->getOne('
             SELECT * FROM election_votes_abstain WHERE election_id=? AND vote_hash=?',
             array($id, $this->voteSalt))) {
            return array('(abstain)');
        }
        if ($vote = $this->dbh->getOne('
             SELECT choice FROM election_votes_single e, election_choices c
              WHERE e.election_id=?
              AND e.vote_hash=?
              AND e.election_id = c.election_id
              AND e.vote = c.choice', array($id, $this->voteSalt))) {
            return array($vote);
        }
        $votes = $this->dbh->getAll('
            SELECT choice FROM election_votes_multiple e, election_choices c
             WHERE e.election_id=?
             AND e.vote_hash=?
             AND e.election_id = c.election_id
             AND e.vote = c.choice', array($id, $this->voteSalt));
        $ret = array();
        foreach ($votes as $vote) {
            $ret[] = $vote[0];
        }
        if (!$ret) {
            return false;
        }
        return $ret;
    }
}