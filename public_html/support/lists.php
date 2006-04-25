<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2004-2005 The PEAR Group                               |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Author: Martin Jansen <mj@php.net>                                   |
   +----------------------------------------------------------------------+
   $Id$
*/

response_header("Support - Mailing Lists");

echo "<h1>Support</h1>";

include 'tabs_list.php';

/*
 * array of lists (
 *     list,
 *     name,
 *     short desc.,
 *     moderated,
 *     archive,
 *     digest,
 *     newsgroup
 * )
 */
$mailing_lists = array(
    'PEAR Mailing Lists',

    array (
        'pear-general',
        'PEAR general list',
        'A list for people with questions on how to use PEAR',
        false,
        true,
        true,
        'php.pear.general'
    ),

    array (
        'pear-dev',
        'PEAR developers list',
        'A list for the people who make PEAR packages',
        false,
        true,
        true,
        'php.pear.dev'
    ),

    array (
        'pear-cvs',
        'PEAR CVS list',
        'All commits to PEAR\'s CVS repository get automatically posted to this list',
        false,
        true,
        true,
        'php.pear.cvs'
    ),

    array (
        'pear-doc',
        'PEAR documentation list',
        'A list for discussing topics related to the PEAR documentation.',
        false,
        true,
        true,
        'php.pear.doc'
    ),

    array (
        'pear-qa',
        'PEAR QA list',
        'A list for managing PEAR\'s Quality Assurance process',
        false,
        true,
        true,
        'php.pear.qa'
    ),

    array (
        'pear-core',
        'PEAR Core development list',
        'A list for the people who make PEAR\'s core infrastructure',
        false,
        true,
        true,
        'php.pear.core'
    ),

    array (
        'pear-webmaster',
        'PEAR webmaster list',
        'A list for the people managing PEAR\'s website',
        false,
        true,
        true,
        'php.pear.webmaster'
    )
);

if (isset($_POST['action'])) {
    # should really grab some email validating routine and use it here.
    if (empty($_POST['email']) || $_POST['email'] == 'user@example.com') {
        echo '<div class="errors">';
        echo 'You forgot to specify an email address to be added to the ';
        echo 'list. Go back and try again.';
        echo '</div>';
        response_footer();
        exit;
    } elseif (!isset($_POST['maillist'])) {
        echo '<div class="errors">';
        echo 'You forgot to choose an mailing list. Go back and try again.';
        echo '</div>';
        response_footer();
        exit;
    } else if (!DEVBOX) {
        $request = strtolower($_POST['action']);
        if ($request != 'subscribe' && $request != 'unsubscribe') {
            $request = 'subscribe';
        }
        $sub = str_replace('@', '=', $_POST['email']);

        foreach ($_POST['maillist'] as $list => $type) {
            if ($type == 'digest') {
                $list = $list . '-digest';
            }
            mail("$list-$request-$sub@lists.php.net",
                 'Website Subscription',
                 'This was a request generated from the form at'
                 . 'http://' . PEAR_CHANNELNAME . '/support/lists.php.',
                 "From: {$_POST['email']}\r\n");
        }

        report_success('A request has been entered into the mailing list'
                       . ' processing queue. You should receive '
                       . (count($_POST['maillist']) == 1 ? 'an email' : 'emails' )
                       . ' at ' . $_POST['email'] . ' shortly describing'
                       . ' how to complete your request.');
    }
}

?>

<h2>&raquo; <a name="lists" id="lists">Mailing Lists</a></h2>

<p>
 There are <?php echo count($mailing_lists)-1; ?> PEAR-related mailing
 lists available. Most of them have archives available, and they are
 also available as newsgroups on our
 <a href="news://news.php.net">news server</a>. The archives are
 searchable. The lists are described in more detail in the
 <a href="/manual/en/support.php">manual</a>.
</p>

<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
<table class="form-holder" cellpadding="5" cellspacing="1">

<?php

while (list(, $listinfo) = each($mailing_lists)) {
    if (!is_array($listinfo)) {
        echo ' <tr>' . "\n";
        echo '  <th class="form-label_top_center">' . $listinfo . '</th>' . "\n";
        echo '  <th class="form-label_top_center">Moderated</th>' . "\n";
        echo '  <th class="form-label_top_center">Archive</th>' . "\n";
        echo '  <th class="form-label_top_center">Newsgroup</th>' . "\n";
        echo '  <th class="form-label_top_center">Normal</th>' . "\n";
        echo '  <th class="form-label_top_center">Digest</th>' . "\n";
        echo ' </tr>' . "\n";
    } else {
        echo ' <tr>' . "\n";
        echo '  <td class="form-input"><strong>' . $listinfo[1] . '</strong><br /><small>'. $listinfo[2] . "</small></td>\n";
        echo '  <td class="form-input_center">' . ($listinfo[3] ? 'yes' : 'no') . "</td>\n";
        echo '  <td class="form-input_center">' . ($listinfo[4] ? make_link("http://beeblex.com/search.php?s=l%3Aphp.".str_replace('-', '.', $listinfo[0]).'&amp;o=1', 'yes') : 'n/a') . "</td>\n";
        echo '  <td class="form-input_center">' . ($listinfo[6] ? make_link("news://news.php.net/".$listinfo[6], 'yes') . ' ' . make_link("http://news.php.net/group.php?group=".$listinfo[6], 'http') : 'n/a') . "</td>\n";
        echo '  <td class="form-input_center"><input name="maillist[' . $listinfo[0] . ']" type="radio" value="normal" /></td>';
        echo '  <td class="form-input_center">' . ($listinfo[5] ? '<input name="maillist[' . $listinfo[0] . ']" type="radio" value="digest" />' : 'n/a' ) . "</td>\n";
        echo ' </tr>' . "\n";
    }
}

?>

</table>

<p style="text-align: center;">
 <strong>Email:</strong>
 <input type="text" name="email" size="30" value="user@example.com" />
 <input type="submit" name="action" value="Subscribe" />
 <input type="submit" name="action" value="Unsubscribe" />
</p>

</form>

<p>
 You will be sent a confirmation mail at the address you wish to
 be subscribed or unsubscribed, and only added to the list after
 following the directions in that mail.
</p>

<p>
 There are a variety of commands you can use to modify your subscription.
 Either send a message to pear-<tt>whatever</tt>@lists.php.net (as in,
 pear-general@lists.php.net) or you can view the commands for
 ezmlm <a href="http://www.ezmlm.org/ezman-0.32/ezman1.html">here</a>.
</p>

<p><a href="/support/">&laquo; Back to the Support overview</a></p>

<?php
response_footer();
?>
