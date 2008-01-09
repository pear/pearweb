<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003-2006 The PEAR Group                               |
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

require_once "Damblan/Karma.php";
require_once "Damblan/Mailer.php";
require_once "HTML/Form.php";
require_once "pear-format-html-form.php";

auth_require("global.karma.manager");

$karma = new Damblan_Karma($dbh);

response_header("PEAR Administration :: Karma Management");

echo "<h1>Karma Management</h1>\n";

$handle = null;
if (!empty($_REQUEST['handle'])) {
    $handle = trim($_REQUEST['handle']);

    if (!preg_match(PEAR_COMMON_USER_NAME_REGEX, $handle)) {
        $handle = null;
    }
}

if ($handle === null || empty($handle)) {
    $form = new PEAR_Web_Form("karma.php", "post");
    $form->setDefaultFromInput(false);

    $form->addUserSelect("handle", "Handle: ");
    $form->addSubmit();
    $form->display();
} else {

    if (!empty($_GET['action'])) {
        include_once 'pear-database-note.php';
        switch ($_GET['action']) {

        case "remove" :
            $res = $karma->remove($handle, $_GET['level']);
            if ($res) {
                echo "Successfully <b>removed</b> karma &quot;"
                        . htmlspecialchars($_GET['level'])
                        . "&quot;<br /><br />";
                note::add('uid', $handle, 'removed ' . $_GET['level'] . ' karma',
                    $auth_user->handle);
            }
            break;

        case "grant" :
            $res = $karma->grant($handle, $_POST['level']);
            if ($res) {
                echo "Successfully <b>added</b> karma &quot;"
                        . htmlspecialchars($_POST['level'])
                        . "&quot;<br /><br />";

                note::add('uid', $handle, 'added ' . $_POST['level'] . ' karma',
                    $auth_user->handle);
            }
            break;
        }
    }

    $user_karma = $karma->get($handle);
    if (count($user_karma) == 0) {
        echo "No karma yet";
    } else {
        $bb = new BorderBox("Karma levels for " . htmlspecialchars($handle), "90%", "", 4, true);
        $bb->HeadRow("Level", "Added by", "Added at", "Remove");
        foreach ($user_karma as $item) {
            $remove = sprintf("karma.php?action=remove&amp;handle=%s&amp;level=%s",
                              htmlspecialchars($handle),
                              htmlspecialchars($item['level']));

            $bb->plainRow(htmlspecialchars($item['level']),
                          htmlspecialchars($item['granted_by']),
                          htmlspecialchars($item['granted_at']),
                          make_link($remove, make_image("delete.gif"),
                                                         false,
                                                         'onclick="javascript:return confirm(\'Do you really want to remove the karma level ' . htmlspecialchars($item['level' ]) . '?\');"'));
        }
        $bb->end();
    }

    echo "<br /><br />";

    $bb = new BorderBox("Grant karma to " . htmlspecialchars($handle));

    $form = new HTML_Form("karma.php?action=grant", "post");
    $form->setDefaultFromInput(false);

    $form->addText("level", "Level: ");
    $form->addHidden("handle", htmlspecialchars($handle));
    $form->addSubmit();
    $form->display();
    $bb->end();
}

echo "<p>&nbsp;</p><hr />";

$bb = new BorderBox("Karma Statistics", "90%", "", 2, true);

if (!empty($_GET['a']) && $_GET['a'] == "details" && !empty($_GET['level'])) {
    $bb->headRow("Handle", "Granted");
    foreach ($karma->getUsers($_GET['level']) as $user) {
        $detail = sprintf("Granted by <a href=\"/user/%s\">%s</a> on %s",
                          htmlspecialchars($user['granted_by']),
                          htmlspecialchars($user['granted_by']),
                          htmlspecialchars($user['granted_at'])
                          );
        $bb->plainRow(make_link("/user/" . htmlspecialchars($user['user']),
                      htmlspecialchars($user['user'])),
                      $detail);
    }
} else {
    $bb->headRow("Level", "# of users");
    foreach ($karma->getLevels() as $level) {
        $bb->plainRow(make_link("karma.php?a=details&amp;level=" . htmlspecialchars($level['level']),
                                htmlspecialchars($level['level'])),
                      htmlspecialchars($level['sum']));
    }
}

$bb->end();

echo "<br /><br />";
print_link("/admin/karma.php", "Back");

response_footer();
?>
