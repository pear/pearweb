<?php

require_once 'pear-database.php';

require_once 'PEAR.php';
require_once 'HTML/QuickForm.php';
require_once 'Mail.php';

define('PROPOSAL_STATUS_PROPOSAL_TIMELINE', (60 * 60 * 24 * 7), true); // 1 week
define('PROPOSAL_STATUS_VOTE_TIMELINE', (60 * 60 * 24 * 7), true); // 1 week
	
// define('PROPOSAL_STATUS_PROPOSAL_TIMELINE', (60), true); // 1 hour
// define('PROPOSAL_STATUS_VOTE_TIMELINE', (60), true); // 1 hour
	
/*
 // This runs PEPr in production mode
define('PROPOSAL_MAIL_PEAR_DEV', 'PEAR developer mailinglist <pear-dev@lists.php.net>', true);
define('PROPOSAL_MAIL_PEAR_GROUP', 'PEAR group <pear-group@php.net>', true);
define('PROPOSAL_MAIL_FROM', 'PEPr <pear-sys@php.net>', true);
*/
	
// This runs PEPr in testing mode
define('PROPOSAL_MAIL_PEAR_DEV', 'PEAR developer mailinglist <dotxp@php-applications.de>', true);
define('PROPOSAL_MAIL_PEAR_GROUP', 'PEAR group <dotxp@php-applications.de>', true);
define('PROPOSAL_MAIL_FROM', 'PEPr <pear-sys@php.net>', true);
	
// define('PROPOSAL_EMAIL_PREFIX', '[PEPr][TEST]', true);
define('PROPOSAL_EMAIL_PREFIX', '[PEPr]', true);
define('PROPOSAL_EMAIL_POSTFIX', "\n\n\nSent by PEPr\nAutomatic proposal system at http://pear.php.net", true);
	
function shorten_string ( $string ) {
    if (strlen($string) < 80) {
        return $string;
    }
    $string_new = substr($string, 0, 20);
    $string_new .= "..." . substr($string, (strlen($string) - 60));
    return $string_new;
}

	
global $proposalStatiMap;
$proposalStatiMap = array(
                          'draft' 	=> 'Draft',
                          'proposal'	=> 'Proposed',
                          'vote'		=> 'Called for votes',
                          'finished'	=> 'Finished'
                          );
	
class proposal {
		
    var $id;
		
    var $pkg_category;
	 
    var $pkg_name;
	 
    var $pkg_describtion;
	 
    var $pkg_deps;
	 	
    var $draft_date;
	 	
    var $proposal_date;
	 
    var $vote_date;
	 	
    var $longened_date;
	 
    var $status = 'draft';
	 	
    var $user_handle;
	 	
    var $links;
	 	
    var $votes;
	 	
    function proposal ( $dbhResArr ) {
        $this->fromArray($dbhResArr);
    }
	 	
    function fromArray( $dbhResArr ) {
        if (!is_array($dbhResArr)) {
            return false;
        }
        foreach ($dbhResArr as $name => $value) {
            $value = (is_string($value)) ? stripslashes($value) : $value;
            $this->$name = $value;
        }	
        return true;
    }
	 	
    function &get ( &$dbh, $id ) {
        $sql = "SELECT *, UNIX_TIMESTAMP(draft_date) as draft_date,
						UNIX_TIMESTAMP(proposal_date) as proposal_date,
						UNIX_TIMESTAMP(vote_date) as vote_date,
						UNIX_TIMESTAMP(longened_date) as longened_date
	 				FROM package_proposals WHERE id = ".$id;
        $res = $dbh->getRow($sql, null, DB_FETCHMODE_ASSOC);
        if (DB::isError($res)) {
            return $res;
        }
        return new proposal($res);	 		
    }
		
    function &getAll ( &$dbh, $status = null, $limit = null ) {
        $sql = "SELECT *, UNIX_TIMESTAMP(draft_date) as draft_date,
						UNIX_TIMESTAMP(proposal_date) as proposal_date,
						UNIX_TIMESTAMP(vote_date) as vote_date,
						UNIX_TIMESTAMP(longened_date) as longened_date
					FROM package_proposals";
        if (!empty($status)) {
            $sql .= " WHERE status = '".$status."'";
        }
        $sql .= " ORDER BY status ASC, draft_date DESC";
        if (!empty($limit)) {
            $sql .= " LIMIT $limit";
        }
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $result = array();
        while ($set = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $result[$set['id']] =& new proposal($set);
        }
        return $result;
    }
		
    function getLinks ( &$dbh ) {
        if (empty($this->id)) {
            return PEAR::raiseError("Not initialized");
        }
        $this->links = & ppLink::getAll($dbh, $this->id);
        return true;
    }
		
    function getVotes ( &$dbh ) {
        if (empty($this->id)) {
            return PEAR::raiseError("Not initialized");
        }
        $this->votes = & ppVote::getAll($dbh, $this->id);
        return true;
    }
			
    function store ( $dbh ) {
        if (isset($this->id)) {
            $sql = "UPDATE package_proposals SET
					pkg_category = '{$this->pkg_category}',
					pkg_name = '{$this->pkg_name}',
					pkg_describtion = '".mysql_escape_string($this->pkg_describtion)."',
					pkg_deps = '".mysql_escape_string($this->pkg_deps)."',
					draft_date = FROM_UNIXTIME({$this->draft_date}),
					proposal_date = FROM_UNIXTIME({$this->proposal_date}),
					vote_date = FROM_UNIXTIME({$this->vote_date}),
					longened_date = FROM_UNIXTIME({$this->longened_date}),
					status = '{$this->status}',
					user_handle = '{$this->user_handle}'
					WHERE id = ".$this->id;
            $res = $dbh->query($sql);
            if (DB::isError($dbh)) {
                return $res;
            }
        } else {
            $sql = "INSERT INTO package_proposals (pkg_category, pkg_name, pkg_describtion,
						pkg_deps, draft_date, status, user_handle) VALUES (
						'{$this->pkg_category}',
						'{$this->pkg_name}',
						'".mysql_escape_string($this->pkg_describtion)."',
						'{$this->pkg_deps}',
						FROM_UNIXTIME(".time()."),
						'".mysql_escape_string($this->status)."',
						'{$this->user_handle}')";
            $res = $dbh->query($sql);
            if (DB::isError($dbh)) {
                return $res;
            }
            $this->id = mysql_insert_id($dbh->connection);
        }
        ppLink::deleteAll($dbh, $this->id);
        foreach ($this->links as $link) {
            if (!empty($link->url)) {
                $res = $link->store($dbh, $this->id);
                if (DB::isError($res)) {
                    return $res;
                }
            }
        }
        if (!empty($this->comment)) {
            $this->comment->store($dbh, $this->id);
            unset($this->comment);
        }
        return true;			
    }
		
    function addVote ( $dbh, $vote ) {
        if (!empty($this->votes[$vote->user_handle])) {
            return PEAR::raiseError("You already voted!");
        }
        $vote->pkg_propop_id = $this->id;
        $this->votes[$vote->user_handle] =& $vote;
        $vote->store($dbh, $this->id);
        return true;
    }
		
    function addComment ( $comment ) {
        $commentData = array("pkg_prop_id" => $this->id,
                             "user_handle" => $_COOKIE['PEAR_USER'],
                             "comment" 	   => $comment);
								 
        $this->comment = new ppComment( $commentData );
        return true;
    }
		
    function addLink ( $dbh, $link ) {
        $link->pkg_prop_id = $this->id;
        $this->links[] =& $link;
        return true;
    }
		
    function isFromUser ( $handle ) {
        if (strtolower($this->user_handle) != strtolower($handle)) {
            return false;
        }
        return true;
    }
		
    function getStatus ( $humanReadable = false ) {
        if ($humanReadable) {
            return $GLOBALS['proposalStatiMap'][$this->status];
        }
        return $this->status;
    }
		
    function isEditable ( ) {
        switch ($this->status) {
        case 'draft':
        case 'proposal': return true;
        }
        return false;
    }
		
			
    function checkTimeline( ) {
        switch ($this->status) {
        case 'draft': return true;
            break;
				
        case 'proposal': if (($this->proposal_date + PROPOSAL_STATUS_PROPOSAL_TIMELINE) < time()) {
            return true;
        }
            return (int)($this->proposal_date + PROPOSAL_STATUS_PROPOSAL_TIMELINE);
            break;
				
        case 'vote': if (!empty($this->longened_date)) {
            if (($this->longened_date + PROPOSAL_STATUS_VOTE_TIMELINE) > time()) {
                return (int)($this->longened_date + PROPOSAL_STATUS_VOTE_TIMELINE);
            }
        } else {
            if (($this->vote_date + PROPOSAL_STATUS_VOTE_TIMELINE) > time()) {
                return (int)($this->vote_date + PROPOSAL_STATUS_VOTE_TIMELINE);
            }
        }
            return false;
            break;
        }
    }
		
    function delete ( &$dbh ) {
        if (empty($this->id)) {
            return PEAR::raiseError("Proposal does not exist!");
        }
        $sql = "DELETE FROM package_proposals WHERE id = ".$this->id;
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $sql = "DELETE FROM package_proposal_votes WHERE pkg_prop_id = ".$this->id;
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $sql = "DELETE FROM package_proposal_links WHERE pkg_prop_id = ".$this->id;
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        return true;
    }
				
    function sendActionEmail($event, $userType, $user_handle = null, $comment = "") {
        if (DEVBOX) {
            return true;
        }

        global $dbh;
        require 'pepr/pepr-emails.php';
        $email = $proposalEmailTexts[$event];
        if (empty($email)) {
            return PEAR::raiseError("Email template for $event not found");
        }
        switch ($userType) {
        case 'admin':
            $prefix = "[ADMIN]";
            break;
        case 'mixed':
            if (user::isAdmin($user_handle) && ($this->user_handle != $user_handle)) {
                $prefix = "[ADMIN]";
            } else {
                $prefix = "";
            }
            break;
        default:
            $prefix = "";
        }
        $prefix = PROPOSAL_EMAIL_PREFIX . $prefix . " ";
        $actorinfo = user::info($user_handle);
        $ownerinfo = user::info($this->user_handle);
        $this->getVotes($dbh);
        $vote = @$this->votes[$user_handle];
        if (isset($vote)) {
            $vote->value = ($vote->value > 0) ? "+".$vote->value : $vote->value;
        }
        $vote_url = "http://".$_SERVER['SERVER_NAME']."/pepr/pepr-vote-show.php?id=".$this->id."&handle=".$user_handle;
        $proposal_url = "http://".$_SERVER['SERVER_NAME']."/pepr/pepr-proposal-show.php?id=".$this->id;
        $end_voting_time = (@$this->longened_date > 0) ? $this->longened_date + PROPOSAL_STATUS_VOTE_TIMELINE : @$this->vote_date + PROPOSAL_STATUS_VOTE_TIMELINE;
        if (!isset($user_handle)) {
            $email['to'] = $email['to']['pearweb'];
        } else if (user::isAdmin($user_handle)) {
            $email['to'] = $email['to']['admin'];
        } else {
            $email['to'] = $email['to']['user'];
        }
        $email['subject'] = $prefix . $email['subject'];
        $replace = array(
                         "/\{pkg_category\}/", 
                         "/\{pkg_name\}/", 
                         "/\{owner_name\}/",	
                         "/\{owner_email\}/",	
                         "/\{owner_link\}/",	
                         "/\{actor_name\}/",
                         "/\{actor_email\}/",	
                         "/\{actor_link\}/",
                         "/\{proposal_url\}/", 
                         "/\{end_voting_time\}/", 
                         "/\{vote_value\}/", 
                         "/\{vote_url\}/",
                         "/\{email_pear_dev\}/",
                         "/\{email_pear_group\}/",
                         "/\{comment\}/"
                         );
        $replacements = array(
                              $this->pkg_category,
                              $this->pkg_name, 
                              (isset($ownerinfo['name'])) ? $ownerinfo['name'] : "", 
                              (isset($ownerinfo['email'])) ? $ownerinfo['email'] : "", 
                              (isset($ownerinfo['handle'])) ? user_link($ownerinfo['handle']) : "",
                              (isset($actorinfo['name'])) ? $actorinfo['name'] : "", 
                              (isset($actorinfo['email'])) ? $actorinfo['email'] : "", 
                              (isset($actorinfo['handle'])) ? "http://pear.php.net/user/".$actorinfo['handle'] : "",
                              $proposal_url, 
                              date("Y-m-d", $end_voting_time), 
                              (isset($vote)) ? $vote->value : 0, 
                              (isset($vote)) ? $vote_url : "",
                              PROPOSAL_MAIL_PEAR_DEV,
                              PROPOSAL_MAIL_PEAR_GROUP,
                              stripslashes($comment)
                              );
        $email = preg_replace($replace, $replacements, $email);
        $email['text'] .= PROPOSAL_EMAIL_POSTFIX;
        $to = explode(", ", $email['to']);
        $email['to'] = array_shift($to);
        $headers = "CC: ". implode(", ", $to) . "\n";
        $headers .= "From: " . PROPOSAL_MAIL_FROM . "\n";
        $headers .= "Reply-To: " . $actorinfo['email'] . "\n";
        $headers .= "X-Mailer: " . "PEPr, PEAR Proposal System" . "\n";
        $headers .= "X-PEAR-Category: " . $this->pkg_category . "\n";
        $headers .= "X-PEAR-Package: " . $this->pkg_name . "\n";
        $headers .= "X-PEPr-Status: " . $this->getStatus() . "\n";

        $res = mail($email['to'], $email['subject'], $email['text'], $headers, "-f pear-sys@php.net");
        if (!$res) {
            return PEAR::raiseError("Could not send notification email.");
        }
        return true;
    }
}

	
	
class ppComment {

    var $pkg_prop_id;
	 
    var $user_handle;
	 
    var $timestamp;
		
    var $comment;
		
    function ppComment ( $dbhResArr ) {
        foreach ($dbhResArr as $name => $value) {
            $value = (is_string($value)) ? stripslashes($value) : $value;
            $this->$name = $value;
        }
    }
		
    function get ( &$dbh, $proposalId, $handle, $timestamp ) {
        $sql = "SELECT *, UNIX_TIMESTAMP(timestamp) AS timestamp FROM package_proposal_changelog WHERE pkg_prop_id = ".$proposalId." AND user_handle='".$handle."' AND timestamp = FROM_UNIXTIME(".$timestamp.")";
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $set['comment'] = stripslashes($set['comment']);
        $set = $res->fetchRow(DB_FETCHMODE_ASSOC);
        $comment =& new ppComment($set);
        return $comment;
    }
		
    function &getAll ( &$dbh, $proposalId ) {
        $sql = "SELECT *, UNIX_TIMESTAMP(timestamp) AS timestamp FROM package_proposal_changelog WHERE pkg_prop_id = ".$proposalId;
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $comments = array();
        while ($set = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $set['comment'] = stripslashes($set['comment']);
            $comments[] =& new ppVote($set);
        }
        return $comments;
    }
		
    function store ( $dbh, $proposalId ) {
        if (empty($this->user_handle)) {
            return PEAR::raiseError("Not initialized");
        }
        $sql = "INSERT INTO package_proposal_changelog (pkg_prop_id, user_handle, comment)
					VALUES (".$proposalId.", '".$this->user_handle."', '".mysql_escape_string($this->comment)."')";
        $res = $dbh->query($sql);
        return $res;
    }		
}

global $proposalReviewsMap;
$proposalReviewsMap = array(
                            'cursory'	=> 'Cursory source review',
                            'deep'		=> 'Deep source review',
                            'test'		=> 'Run examples');

class ppVote {

    var $pkg_prop_id;
	 
    var $user_handle;
		
    var $value;
	 
    var $reviews;
	 
    var $is_conditional;
	 
    var $comment;
	 
    var $timestamp;
		
    function ppVote ( $dbhResArr ) {
        foreach ($dbhResArr as $name => $value) {
        	$value = (is_string($value)) ? stripslashes($value) : $value;
            $this->$name = $value;
        }
    }
		
    function get ( &$dbh, $proposalId, $handle ) {
        $sql = "SELECT *, UNIX_TIMESTAMP(timestamp) AS timestamp FROM package_proposal_votes WHERE pkg_prop_id = ".$proposalId." AND user_handle='".$handle."'";
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $set = $res->fetchRow(DB_FETCHMODE_ASSOC);
        $set['reviews'] = unserialize($set['reviews']);
        $vote =& new ppVote($set);
        return $vote;
    }
		
    function &getAll ( &$dbh, $proposalId ) {
        $sql = "SELECT *, UNIX_TIMESTAMP(timestamp) AS timestamp FROM package_proposal_votes WHERE pkg_prop_id = ".$proposalId." ORDER BY timestamp ASC";
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $votes = array();
        while ($set = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $set['reviews'] = unserialize($set['reviews']);
            $votes[$set['user_handle']] =& new ppVote($set);
        }
        return $votes;
    }
		
    function store ( $dbh, $proposalId ) {
        if (empty($this->user_handle)) {
            return PEAR::raiseError("Not initialized");
        }
        $sql = "INSERT INTO package_proposal_votes (pkg_prop_id, user_handle, value, is_conditional, comment, reviews)
					VALUES (".$proposalId.", '".$this->user_handle."', ".$this->value.", ".(int)$this->is_conditional.", '".mysql_escape_string($this->comment)."', '".serialize($this->reviews)."')";
        $res = $dbh->query($sql);
        return $res;
    }
		
    function getReviews ( $humanReadable = false ) {
        if ($humanReadable) {
            foreach ($this->reviews as $review) {
                $res[] = $GLOBALS['proposalReviewsMap'][$review];
            }
            return $res;
        }
        return $this->reviews;
    }
		
    function getSum ( $dbh, $proposalId ) {
        $sql = "SELECT SUM(value) FROM package_proposal_votes WHERE pkg_prop_id = ".$proposalId." GROUP BY pkg_prop_id";
        $res = $dbh->getOne($sql);
        return (!empty($res)) ? $res: " 0";
    }
		
    function getCount ( $dbh, $proposalId ) {
        $sql = "SELECT COUNT(user_handle) FROM package_proposal_votes WHERE pkg_prop_id = ".$proposalId." GROUP BY pkg_prop_id";
        $res = $dbh->getOne($sql);
        return (!empty($res)) ? $res: " 0";
    }
		
    function hasVoted ( $dbh, $userHandle, $proposalId ) {
        $sql = "SELECT count(pkg_prop_id) as votecount FROM package_proposal_votes
					WHERE pkg_prop_id = ".$proposalId." AND user_handle = '".$userHandle."' 
					GROUP BY pkg_prop_id";
        $votes = $dbh->query($sql);
        return (bool)($votes->numRows());
    }
		
}
	
global $proposalTypeMap;
$proposalTypeMap = array(
                         'pkg_file'				=> "PEAR package file (.tgz)", 
                         'pkg_source' 			=> "Package source file (.phps/.htm)",
                         'pkg_example'			=> "Package example (.php)", 
                         'pkg_example_source'	=> "Package example source (.phps/.htm)", 
                         'pkg_doc'				=> "Package documentation");
	
class ppLink {
		
    var $pkg_prop_id;
	 
    var $type;
	 
    var $url;
		
    function ppLink ( $dbhResArr ) {
        foreach ($dbhResArr as $name => $value) {
            $this->$name = $value;
        }
    }
		
    function &getAll ( &$dbh, $proposalId ) {
        $sql = "SELECT * FROM package_proposal_links WHERE pkg_prop_id = ".$proposalId." ORDER BY type";
        $res = $dbh->query($sql);
        if (DB::isError($res)) {
            return $res;
        }
        $links = array();
        while ($set = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
            $links[] =& new ppLink($set);
        }
        return $links;
    }
		
    function deleteAll ( $dbh, $proposalId) {
        $sql = "DELETE FROM package_proposal_links WHERE pkg_prop_id = ".$proposalId;
        $res = $dbh->query($sql);
        return $res;
    }
		
    function store ( $dbh, $proposalId ) {
        $sql = "INSERT INTO package_proposal_links (pkg_prop_id, type, url)
					VALUES (".$proposalId.", '".$this->type."', '".mysql_escape_string($this->url)."')";
        $res = $dbh->query($sql);
        return $res;
    }
		
    function getType ( $humanReadable = false ) {
        if ($humanReadable) {
            return $GLOBALS['proposalTypeMap'][$this->type];
        }
        return $this->type;
    }
}

if (function_exists('make_image')) {
    $SIDEBAR_DATA='
		<h3>PEPr</h3>
		<p>
		PEAR Package Proposal System.
		</p>
		<p>
		Automatic handling of package proposals.
		<br />
		</p>
		<p>
		<br />'.
        make_image("box-1.gif") . '<b>' . make_link("pepr-overview.php","Browse proposals")
        .'<br />';
		
    if (isset($_COOKIE['PEAR_USER'])) {
        $SIDEBAR_DATA.=
            make_image("box-1.gif") . '<b>' . make_link("pepr-proposal-edit.php","New proposal");
    } else {
        $SIDEBAR_DATA.='<br />
			<small>Login to create or edit proposals</small>
			';
    }


    $SIDEBAR_DATA.='<br /><br />'.
        make_image("box-1.gif") . '<b>' . make_link("/bugs/search.php?cmd=display&bug_type[]=PEPr&status=Open","View bugs")
        .'<br />'.
        make_image("box-1.gif") . '<b>' . make_link("/bugs/report.php?package=PEPr","Report bug")
        .'<br />'
        .'<br />'.
        make_image("box-1.gif") . '<b>' . make_link("/","Main site")
        .'
		-';
}
?>
