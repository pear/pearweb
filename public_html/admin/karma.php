<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003 The PEAR Group                                    |
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
require_once "HTML/Form.php";

auth_require("global.karma.manager");

$karma = new Damblan_Karma($dbh);

response_header("PEAR Administration :: Karma Management");

echo "<h1>Karma Management</h1>\n";

if (empty($_POST['handle']) && empty($_GET['handle'])) {
    $form = new HTML_Form($_SERVER['PHP_SELF'], "post");
    $form->addText("handle", "Handle: ");
    $form->display();
} else {
    if (!empty($_POST['handle'])) {
        $handle = trim($_POST['handle']);
    } else {
        $handle = trim($_GET['handle']);
    }

    if (!empty($_GET['action'])) {
        switch ($_GET['action']) {

        case "remove" :
            $res = $karma->remove($handle, $_GET['level']);
            if ($res) {
                echo "Successfully <b>removed</b> karma &quot;" . $_GET['level'] . "&quot;<br /><br />";
            }
            break;

        case "grant" :
            $res = $karma->grant($handle, $_POST['level']);
            if ($res) {
                echo "Successfully <b>added</b> karma &quot;" . $_POST['level'] . "&quot;<br /><br />";
            }
            break;
        }
    }

    $user_karma = $karma->get($handle);
    if (count($user_karma) == 0) {
        echo "No karma yet";
    } else {
        $bb = new BorderBox("Karma levels for " . $handle, "90%", "", 4, true);
        $bb->HeadRow("Level", "Added by", "Added at", "Remove");
        foreach ($user_karma as $item) {
            $remove = sprintf($_SERVER['PHP_SELF'] . "?action=remove&amp;handle=%s&amp;level=%s",
                              $handle, $item['level']);

            $bb->plainRow($item['level'], $item['granted_by'], 
                          $item['granted_at'], make_link($remove, make_image("delete.gif")));
        }
        $bb->end();
    }

    echo "<br /><br />";

    $bb = new BorderBox("Grant karma to " . $handle);
    $form = new HTML_Form($_SERVER['PHP_SELF'] . "?action=grant", "post");
    $form->addText("level", "Level: ");
    $form->addHidden("handle", $handle);
    $form->addSubmit();
    $form->display();
    $bb->end();
}

echo "<p>&nbsp;</p>" . hdelim();

$bb = new BorderBox("Karma Statistics", "90%", "", 2, true);

if (!empty($_GET['a']) && $_GET['a'] == "details" && !empty($_GET['level'])) {
    $bb->headRow("Handle", "Granted");
    foreach ($karma->getUsers($_GET['level']) as $user) {
        $detail = sprintf("Granted by <a href=\"/user/%s\">%s</a> on %s",
                          $user['granted_by'],
                          $user['granted_by'],
                          $user['granted_at']
                          );
        $bb->plainRow(make_link("/user/" . $user['user'], $user['user']), $detail);
    }
} else {
    $bb->headRow("Level", "# of users");
    foreach ($karma->getLevels() as $level) {
        $bb->plainRow(make_link($_SERVER['PHP_SELF']. "?a=details&amp;level=" . $level['level'], $level['level']), $level['sum']);
    }
}

$bb->end();

echo "<br /><br />";
print_link("/admin/karma.php", "Back");

response_footer();
?>
