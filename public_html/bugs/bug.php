<?php /* vim: set noet ts=4 sw=4: : */
error_reporting(E_ALL ^ E_NOTICE);
$id = (int)$_GET['id'];
if (!$id) {
    header('Location: /');
    exit;
}
$edit = (int)$edit;

require_once 'bugs/prepend.inc';
require_once 'bugs/cvs-auth.inc';
require_once 'bugs/trusted-devs.inc';

if (isset($_COOKIE['PEAR_USER']) && isset($_COOKIE['PEAR_PW'])) {
    $user = $_COOKIE['PEAR_USER'];
    $pw   = $_COOKIE['PEAR_PW'];
}

# fetch info about the bug into $bug
$query = 'SELECT id,package_name,bug_type,email,passwd,sdesc,ldesc,
        php_version,php_os,status,ts1,ts2,assign,
        UNIX_TIMESTAMP(ts1) AS submitted, UNIX_TIMESTAMP(ts2) AS modified,
        COUNT(bug=id) AS votes,
        SUM(reproduced) AS reproduced,SUM(tried) AS tried,
        SUM(sameos) AS sameos, SUM(samever) AS samever,
        AVG(score)+3 AS average,STD(score) AS deviation
        FROM bugdb LEFT JOIN bugdb_votes ON id=bug WHERE id=' . (int)$id . '
        GROUP BY bug';

$res = $dbh->query($query);

if ($res) $bug = $res->fetchRow();
if (!$res || !$bug) {
    response_header('No such bug.');
    echo '<h1 class="error">No such bug #'.$id.'!</h1>';
    response_footer();
    exit;
}

# Delete comment
if ($edit == 1 && isset($delete_comment)) {
    $addon = '';
    if (in_array($user, $trusted_developers) && verify_password($user,stripslashes($pw))) {
        delete_comment($id, $delete_comment);
        $addon = 'thanks=1';
    }
    header('Location:' . $_SERVER['PHP_SELF'] . "?id=$id&edit=1$addon");
    exit();
}

# handle any updates, displaying errors if there were any
$success = !isset($_POST['in']);
$errors = array();

if ($_POST['in'] && $edit == 3) {
    if (!preg_match("/[.\\w+-]+@[.\\w-]+\\.\\w{2,}/i",$_POST['in']['commentemail'])) {
        $errors[] = 'You must provide a valid email address.';
    }

    # Don't allow comments by the original report submitter
    if (stripslashes($_POST['in']['commentemail']) == $bug['email']) {
        header('Location:' . $_SERVER['PHP_SELF'] . "?id=$id&edit=2");
        exit();
    }

    # check that they aren't using a php.net mail address without
    # being authenticated (oh, the horror!)
    if (preg_match('/^(.+)@php\.net/i', stripslashes($_POST['in']['commentemail']), $m)) {
        if ($user != stripslashes($m[1]) || !verify_password($user,$pass)) {
            $errors[] = 'You have to be logged in as a developer to use your php.net email address.';
        }
    }

    $ncomment = trim($ncomment);
    if (!$ncomment) {
        $errors[] = 'You must provide a comment.';
    }

    if (!$errors) {
        $query = 'INSERT INTO bugdb_comments (bug,email,ts,comment) VALUES
               (' . (int)$id .',
               ' . $dbh->quoteSmart($_POST['in']['commentemail']) .',
               NOW(),
               ' . $dbh->quoteSmart($ncomment) .')';
               
        $success = $dbh->query($query);
    }
    $from = stripslashes($_POST['in']['commentemail']);

} elseif ($_POST['in'] && $edit == 2) {
    if (!$bug['passwd'] || $bug['passwd'] != stripslashes($pw)) {
        $errors[] = 'The password you supplied was incorrect.';
    }

    $ncomment = trim($ncomment);
    if (!$ncomment) {
        $errors[] = 'You must provide a comment.';
    }

    # check that they aren't being bad and setting a status they
    # aren't allowed to (oh, the horrors.)
    if ($_POST['in']['status'] != $bug['status'] && $state_types[$_POST['in']['status']] != 2) {
        $errors[] = 'You aren\'t allowed to change a bug to that state.';
    }

    # check that they aren't changing the mail to a php.net address
    # (gosh, somebody might be fooled!)
    if (preg_match('/^(.+)@php\.net/i', $_POST['in']['email'], $m)) {
        if ($user != $m[1] || !verify_password($user,$pass)) {
            $errors[] = 'You have to be logged in as a developer to use your php.net email address.';
        }
    }

    $from = ($bug['email'] != $_POST['in']['email'] && !empty($_POST['in']['email'])) ? $_POST['in']['email'] : $bug['email'];

    if (!$errors && !($errors = incoming_details_are_valid($_POST['in']))) {
        /* update bug record */
        $query = 'UPDATE bugdb SET 
                sdesc='        . $dbh->quoteSmart($_POST['in']['sdesc']) .',
                status='       . $dbh->quoteSmart($_POST['in']['status']) .', 
                package_name=' . $dbh->quoteSmart($_POST['in']['package_name']) .', 
                bug_type='     . $dbh->quoteSmart($_POST['in']['bug_type']) .',
                php_version='  . $dbh->quoteSmart($_POST['in']['php_version']) .', 
                php_os='       . $dbh->quoteSmart($_POST['in']['php_os']) .', 
                ts2=NOW(), 
                email='        . $dbh->quoteSmart($from) .'
                WHERE id = '   . (int)$id;
        $success = $dbh->query($query);

        /* add comment */
        if ($success && !empty($ncomment)) {
            $query = "INSERT INTO bugdb_comments (bug, email, ts, comment) 
                    VALUES ($id,'$from',NOW(),'$ncomment')";
            $success = $dbh->query($query);
        }
    }

} elseif ($_POST['in'] && $edit == 1) {
    if (!verify_password($user, stripslashes($pw))) {
        $errors[] = 'You have to login first in order to edit the bug report.';
    }

    if ((($_POST['in']['status'] == 'Bogus' && $bug['status'] != 'Bogus') || $RESOLVE_REASONS[$_POST['in']['resolve']]['status'] == 'Bogus')
            && strlen(trim($ncomment)) == 0) {
        $errors[] = 'You must provide a comment when marking a bug \'Bogus\'';
    } elseif ($_POST['in']['resolve']) {
        if (!$trytoforce && $RESOLVE_REASONS[$_POST['in']['resolve']]['status'] == $bug['status']) {
            $errors[] = "The bug is already marked '$bug[status]'. (Submit again to ignore this.)";
        } elseif (!$errors)  {
            if ($_POST['in']['status'] == $bug['status']) {
                $_POST['in']['status'] = $RESOLVE_REASONS[$_POST['in']['resolve']]['status'];
            }
            $ncomment = addslashes($RESOLVE_REASONS[$_POST['in']['resolve']]['message'])
                      . "\n\n$ncomment";
        }
    }

    $from = $user . '@php.net';
    $query = "SELECT email FROM users WHERE handle = '" . $user . "'";
    $from = $dbh->getOne($query);
    if (!$errors && !($errors = incoming_details_are_valid($_POST['in']))) {
        (!empty($_POST['in']['assign']) && $bug['status'] == 'Open') ? $status = 'Assigned' : $status = $_POST['in']['status'];

        $query = 'UPDATE bugdb SET ';
        $query.= ($bug['email'] != $_POST['in']['email'] && !empty($_POST['in']['email'])) ? 'email=' . $dbh->quoteSmart($_POST['in']['email']) .', ' : '';
        $query.= 'sdesc='      . $dbh->quoteSmart($_POST['in']['sdesc']) .', 
                status='       . $dbh->quoteSmart($status) .', 
                package_name=' . $dbh->quoteSmart($_POST['in']['package_name']) .', 
                bug_type='     . $dbh->quoteSmart($_POST['in']['bug_type']) .', 
                assign='       . $dbh->quoteSmart($_POST['in']['assign']) .', 
                php_version='  . $dbh->quoteSmart($_POST['in']['php_version']) .', 
                php_os='       . $dbh->quoteSmart($_POST['in']['php_os']) .', 
                ts2=NOW() 
                WHERE id= '    . (int)$id;
                
        $success = $dbh->query($query);
        if ($success && !empty($ncomment)) {
            $query = "INSERT INTO bugdb_comments (bug, email, ts, comment) 
                    VALUES ($id,'" . $from . "',NOW(),'$ncomment')";
            $success = $dbh->query($query);
        }

    }
}

if ($_POST['in'] && !$errors && $success) {
    mail_bug_updates($bug,$_POST['in'],$from,$ncomment,$edit);
    header('Location:' .  $_SERVER['PHP_SELF'] . "?id=$id&thanks=$edit");
    exit;
}

response_header("#$id: ".htmlspecialchars($bug['sdesc']));
# the lol
echo '<style type="text/css">'; include('./style.css'); echo '</style>';

/* DISPLAY BUG */
if ($thanks == 1 || $thanks == 2) {
  echo '<div class="thanks">The bug was updated successfully.</div>';
} elseif ($thanks == 3) {
  echo '<div class="thanks">Your comment was added to the bug successfully.</div>';
} elseif ($thanks == 4) {?>
<div class="thanks">
Thank you for your help! If the status of the bug report you submitted changes,
you will be notified. You may return here and check on the status or update
your report at any time. That URL for your bug report is: <a
href="/bugs/bug.php?id=<?php echo $id?>">http://pear.php.net/bugs/bug.php?id=<?php echo $id; ?></a>.
</div>
<?php
} elseif ($thanks == 6) {?>
<div class="thanks">
Thanks for voting! Your vote should be reflected in the
statistics below.
</div>
<?php
}
show_bugs_menu($bug['package_name']);
?>

<div id="bugheader">
 <table id="details">
  <tr id="title">
   <th class="details" id="number">Bug&nbsp;#<?php echo $id?></th>
   <?php $bug['bug_type'] != 'Bug' ? $summary = '[FCR] ' : $summary = ''; ?>
   <td id="summary" colspan="5"><?php echo clean($summary.$bug['sdesc'])?></td>
  </tr>
  <tr id="submission">
   <th>Submitted:</th><td><?php echo format_date($bug['submitted'])?></td>
<?php if ($bug['modified']) {?>
   <th class="details">Modified:</th><td> <?php echo format_date($bug['modified'])?></td>
<?php }?>
  </tr>
  <tr id="submitter">
   <th class="details">From:</th><td><?php echo htmlspecialchars(spam_protect($bug['email']))?></td>
  </tr>
  <tr id="categorization">
   <th class="details">Status:</th><td><?php echo htmlspecialchars($bug['status'])?></td>
   <th class="details">Package:</th><td colspan="3"><?php echo htmlspecialchars($bug['package_name'])?></td>
  </tr>
  <tr id="situation">
   <th class="details">Version:</th><td><?php echo htmlspecialchars($bug['php_version'])?></td>
   <th class="details">OS:</th><td colspan="4"><?php echo htmlspecialchars($bug['php_os'])?></td>
  </tr>

<?php if ($bug['votes']) {?>
  <tr id="votes">
   <th class="details">Votes:</th><td><?php echo $bug['votes'];?></td>
   <th class="details">Avg. Score:</th><td><?php printf("%.1f &plusmn; %.1f", $bug['average'], $bug['deviation'])?></td>
   <th class="details">Reproduced:</th><td><?php printf("%d of %d (%.1f%%)",$bug['reproduced'],$bug['tried'],$bug['tried']?($bug['reproduced']/$bug['tried'])*100:0);?></td>
  </tr>
<?php if ($bug['reproduced']) {?>
  <tr id="reproduced">
   <td colspan="2"></td>
   <th class="details">Same Version:</th><td><?php printf("%d (%.1f%%)",$bug['samever'],($bug['samever']/$bug['reproduced'])*100);?></td>
   <th class="details">Same OS:</th><td><?php printf("%d (%.1f%%)",$bug['sameos'],($bug['sameos']/$bug['reproduced'])*100);?></td>
  </tr>
<?php }?>
<?php }?>
</table>
</div>

<div id="controls">
<?php
function control($num,$desc) {
  $active = ($GLOBALS['edit'] == $num);
  echo "<span id=\"control_$num\" class=\"control", ($active ? ' active' : ''), "\">",
       !$active ? "<a href=\"{$_SERVER['PHP_SELF']}?id={$GLOBALS['id']}".($num ? "&amp;edit=$num" : '')."\">" : '',
       $desc, !$active ? '</a>' : '', '</span> ';
}

control(0,'View');
if ($edit != 2) {
    control(3,'Add Comment');
}
control(1,'Developer');
control(2,'Edit Submission');
?>
</div>
<br clear="all" />

<?php
if ($errors) display_errors($errors);
if (!$errors && !$success) {?>
<div class="errors">
Some sort of database error has happened. Maybe this will be illuminating:
<?php echo mysql_error();?> This was the last query attempted: <tt><?php echo htmlspecialchars($query)?></tt>
</div>
<?php
}

if ($edit == 1 || $edit == 2) {?>
<form id="update" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $_GET['id']; ?>" method="post">
<?php
if ($edit == 2) {
    if (!$_POST['in'] && $pw && $bug['passwd'] && stripslashes($pw)==$bug['passwd']) {?>
<div class="explain">
Welcome back! Since you opted to store your bug's password in a cookie, you can
just go ahead and add more information to this bug or edit the other fields.
</div>
<?php } else { ?>
<div class="explain">
<?php if (!$_POST['in']) {?>
Welcome back! If you're the original bug submitter, here's where you can edit
the bug or add additional notes. If this is not your bug, you can 
<a href="<?php echo $_SERVER['PHP_SELF'] . "?id=$id&amp;edit=3"?>">add a comment by following
this link</a> or the box above that says 'Add Comment'. If this is your bug,
but you forgot your password, <a href="bug-pwd-finder.php">you can retrieve
your password here</a>.
<?php } ?>
<table>
 <tr>
  <th class="details">Password:</th>
  <td><input type="password" name="pw" value="<?php echo clean($pw)?>" size="10" maxlength="20" /></td>
  <th class="details">
   <label for="save">Check to remember your password for next time:</label>
  </th>
  <td>
   <input type="checkbox" id="save" name="save"<?php if ($save) echo ' checked="checked"'?> />
  </td>
 </tr>
</table>
</div>
<?php
    }
} else {
    if ($user && $pw && verify_password($user,stripslashes($pw))) {
        if (!$_POST['in']) { ?>
<div class="explain">
Welcome back, <?php echo $user;?>! (Not <?php echo $user;?>? <a href="/?logout=1">Log out.</a>)
</div>
<?php
        }
    } else { ?>
<div class="explain">
<?php if (!$in) {?>
Welcome! If you don't have a CVS account, you can't do anything here. You can
<a href="<?php echo $_SERVER['PHP_SELF'] . "?id=$id&amp;edit=3"?>">add a comment by following
this link</a> or if you reported this bug, you can <a href="<?php echo
$_SERVER['PHP_SELF'] . "?id=$id&amp;edit=2"?>">edit this bug over here</a>.
<?php }?>
<!--
<table>
 <tr>
  <th class="details">CVS Username:</th>
  <td><input type="text" name="user" value="<?php echo clean($user)?>" size="10" maxlength="20" /></td>
  <th class="details">CVS Password:</th>
  <td><input type="password" name="pw" value="<?php echo clean($pw)?>" size="10" maxlength="20" /></td>
  <th class="details">
   <label for="save">Remember:</label>
  </th>
  <td>
   <input type="checkbox" id="save" name="save"<?php if ($save) echo ' checked="checked"'?> />
  </td>
 </tr>
</table>
-->
</div>
<?php
  }
}
?>
<table>
<?php if ($edit == 1) {?>
 <tr>
  <th class="details"><a href="http://bugs.php.net/quick-fix-desc.php">Quick Fix:</a></th>
  <td colspan="5"><select name="in[resolve]"><?php show_reason_types($_POST['in']['resolve'],1);?></select><?php if ($_POST['in']['resolve']) {?><input type="hidden" name="trytoforce" value="1" /><?php }?></td>
 </tr>
<?php }?>
 <tr>
  <th class="details"><label for="statuslist" accesskey="s"><u>S</u>tatus:</label></th>
  <td><select name="in[status]" id="statuslist"><?php show_state_options($_POST['in']['status'],$edit,$bug['status'])?></select></td>
<?php if ($edit == 1) {?>
  <th class="details">Assign to:</th>
  <td><input type="text" size="10" maxlength="16" name="in[assign]" value="<?php echo field('assign')?>" /></td>
<?php }?>
  <td><input type="hidden" name="id" value="<?php echo $id?>" /><input type="hidden" name="edit" value="<?php echo $edit?>" /><input type="submit" value="Submit" /></td>
 </tr>
 <tr>
  <th class="details">Category:</th>
  <td colspan="3"><select name="in[package_name]"><?php show_types($_POST['in']['package_name'],0,$bug['package_name'])?></select></td>
  </tr><tr>
  <th class="details">Bug Type:</th>
   <td colspan="3">
        <select name="in[bug_type]">
            <option value="Bug">Bug</option>
            <option value="Feature/Change Request">Feature/Change Request</option>
        </select>
    </td>
<?php /* severity goes here. */ ?>
 </tr>
 <tr>
  <th class="details">Summary:</th>
  <td colspan="5"><input type="text" size="60" maxlength="80" name="in[sdesc]" value="<?php echo rinse(field('sdesc'))?>" /></td>
 </tr>
 <tr>
  <th class="details">From:</th>
  <td colspan="5"><?php echo spam_protect(field('email')); ?></td>
 </tr>
 <tr>
  <th class="details">New email:</th>
  <td colspan="5"><input type="text" size="40" maxlength="40" name="in[email]" value="" /></td>
 </tr>
 <tr>
  <th class="details">Version:</th>
  <td><input type="text" size="20" maxlength="100" name="in[php_version]" value="<?php echo field('php_version')?>" /></td>
  <th class="details">OS:</th>
  <td colspan="3"><input type="text" size="20" maxlength="32" name="in[php_os]" value="<?php echo field('php_os')?>" /></td>
 </tr>
</table>
<label for="ncomment" accesskey="o"><strong>New<?php if ($edit==1) echo "/Additional"?> C<u>o</u>mment:</strong></label><br />
<textarea cols="60" rows="8" name="ncomment" id="ncomment"><?php echo clean($ncomment)?></textarea>
<br /><input type="submit" value="Submit" />
</form>
<?php }?>
<?php if ($edit == 3) {?>
<form id="comment" action="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $id; ?>" method="post">
<?php if (!$_POST['in']) {?>
<div class="explain">
Anyone can comment on a bug. Have a simpler test case? Does it work for you on
a different platform? Let us know! Just going to say 'Me too!'? Don't clutter
the database with that please
<?php if (canvote()) { echo " &mdash; but make sure to <a href=\"{$_SERVER['PHP_SELF']}?id=$id\">vote on the bug</a>"; } ?>!
</div>
<?php }?>
<table>
 <tr>
  <th class="details">Your email address:</th>
  <td><input type="text" size="40" maxlength="40" name="in[commentemail]" value="<?php echo clean($_POST['in']['commentemail'])?>" /></td>
  <td><input type="hidden" name="id" value="<?php echo $id?>" /><input type="hidden" name="edit" value="<?php echo $edit?>" /><input type="submit" value="Submit" /></td>
 </tr>
</table>
<div>
 <textarea cols="60" rows="10" name="ncomment"><?php echo clean($ncomment);?></textarea>
 <br /><input type="submit" value="Submit" />
</div>
</form>
<?php }?>
<?php if (!$edit && canvote()) {?>
  <form id="vote" method="post" action="vote.php">
  <div class="sect">
   <fieldset>
    <legend>Have you experienced this issue?</legend>
    <div>
     <input type="radio" id="rep-y" name="reproduced" value="1" onchange="show('canreproduce')" /> <label for="rep-y">yes</label>
     <input type="radio" id="rep-n" name="reproduced" value="0" onchange="hide('canreproduce')" /> <label for="rep-n">no</label>
     <input type="radio" id="rep-d" name="reproduced" value="2" onchange="hide('canreproduce')" checked="checked" /> <label for="rep-d">don't know</label>
    </div>
   </fieldset>
   <fieldset>
    <legend>Rate the importance of this bug to you:</legend>
    <div>
     <label for="score-5">high</label>
     <input type="radio" id="score-5" name="score" value="2" />
     <input type="radio" id="score-4" name="score" value="1" />
     <input type="radio" id="score-3" name="score" value="0" checked="checked" />
     <input type="radio" id="score-2" name="score" value="-1" />
     <input type="radio" id="score-1" name="score" value="-2" />
     <label for="score-1">low</label>
    </div>
   </fieldset>
  </div>
  <div id="canreproduce" class="sect" style="display: none">
   <fieldset>
    <legend>Are you using the same PHP version?</legend>
    <div>
     <input type="radio" id="ver-y" name="samever" value="1" /> <label for="ver-y">yes</label>
     <input type="radio" id="ver-n" name="samever" value="0" checked="checked" /> <label for="ver-n">no</label>
    </div>
   </fieldset>
   <fieldset>
    <legend>Are you using the same operating system?</legend>
    <div>
     <input type="radio" id="os-y" name="sameos" value="1" /> <label for="os-y">yes</label>
     <input type="radio" id="os-n" name="sameos" value="0" checked="checked" /> <label for="os-n">no</label>
    </div>
   </fieldset>
  </div>
  <div id="submit" class="sect">
   <input type="hidden" name="id" value="<?php echo $id?>" />
   <input type="submit" value="Vote" />
  </div>
  </form>
  <br clear="all" />
<?php }

/* ORIGINAL REPORT */
if ($bug['ldesc']) {
  output_note(0, $bug['submitted'], $bug['email'], $bug['ldesc']);
}

/* COMMENTS */
$query = "SELECT id,email,comment,UNIX_TIMESTAMP(ts) AS added"
       . " FROM bugdb_comments WHERE bug=$id ORDER BY ts";
$res = $dbh->query($query);
if ($res) {
    while ($row = $res->fetchRow()) {
        output_note($row['id'], $row['added'], $row['email'], $row['comment']);
    }
}

response_footer();

function output_note($com_id, $ts, $email, $comment)
{
    global $edit, $id, $trusted_developers, $user;

    echo '<div class="comment">';
    echo '<strong>[',format_date($ts),'] ', htmlspecialchars(spam_protect($email)), '</strong>' . "\n";
    echo ($edit == 1 && $com_id !== 0 && in_array($user, $trusted_developers)) ? "<a href=\"{$_SERVER['PHP_SELF']}?id=$id&amp;edit=1&amp;delete_comment=$com_id\">[delete]</a>\n" : '';
    echo '<pre class="note">';
    $note = addlinks(preg_replace("/(\r?\n){3,}/","\n\n",wordwrap($comment,72,"\n",1)));
    echo preg_replace('/(bug\ *#([0-9]+))/i', "<a href=\"{$_SERVER['PHP_SELF']}?id=\\2\">\\1</a>", $note);
    echo "</pre>\n";
    echo '</div>';
}

function delete_comment($id, $com_id)
{
    $query = "DELETE FROM bugdb_comments WHERE bug=$id AND id=$com_id";
    $res = $dbh->query($query);
}

function canvote()
{
    return false;
    global $thanks, $bug;
    return ($thanks != 4 && $thanks != 6 && $bug['status'] != 'Closed' && $bug['status'] != 'Bogus' && $bug['status'] != 'Duplicate');
}
