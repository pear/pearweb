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

response_header("PEAR Administration :: Karma Management");

echo "<h1>Karma Management</h1>\n";

if (empty($_POST['handle']) && empty($_GET['handle'])) {
    $form = new HTML_Form($_SERVER['PHP_SELF'], "post");
    $form->addText("handle", "Handle: ");
    $form->display();
} else {
    $karma = new Damblan_Karma($dbh);

    if (!empty($_POST['handle'])) {
        $handle = $_POST['handle'];
    } else {
        $handle = $_GET['handle'];
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

    $karma = $karma->get($handle);
    if (count($karma) == 0) {
        echo "No karma yet";
    } else {
        $bb = new BorderBox("Karma levels for " . $handle, "90%", "", 4, true);
        $bb->HeadRow("Level", "Added by", "Added at", "Remove");
        foreach ($karma as $item) {
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
    $form->display();
    $bb->end();
}


response_footer();
?>
