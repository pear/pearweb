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

@session_start();
$csrf_token_name = 'pear_csrf_token_' . basename(__FILE__, '.php');

include_once 'HTML/QuickForm2.php';
include_once 'HTML/Table.php';
require_once 'Damblan/Karma.php';
require_once 'Damblan/Mailer.php';

auth_require('global.karma.manager');

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
    $form = new HTML_QuickForm2('karma_edit', 'post');
    $form->removeAttribute('name');

    include_once 'pear-database-user.php';
    $list = user::listAll(true);

    $users = array();
    foreach ($list as $user) {
        $users[$user['handle']] = $user['handle'] . ' (' . $user['name'] . ')';
    }
    $form->addElement('select', 'handle')->setLabel('Handle:&nbsp;')->loadOptions($users);
    $form->addElement('submit', 'submit')->setLabel('Submit Changes');
    print $form;
} else {

    if (!empty($_GET['action'])) {
        if (!validate_csrf_token($csrf_token_name)) {
            report_error('Invalid token.');
            response_footer();
            exit();
        }

        include_once 'pear-database-note.php';
        switch ($_GET['action']) {

        case "remove" :
            $res = $karma->remove($handle, $_GET['level']);
            if ($res) {
                echo "Successfully <b>removed</b> karma &quot;"
                        . htmlspecialchars($_GET['level'])
                        . "&quot;<br /><br />";
                note::add($handle, 'removed ' . $_GET['level'] . ' karma', $auth_user->handle);
            }
            break;

        case "grant" :
            $res = $karma->grant($handle, $_POST['level']);
            if ($res) {
                echo "Successfully <b>added</b> karma &quot;"
                        . htmlspecialchars($_POST['level'])
                        . "&quot;<br /><br />";

                note::add($handle, 'added ' . $_POST['level'] . ' karma', $auth_user->handle);
            }
            break;
        }
    }

    $user_karma = $karma->get($handle);
    if (count($user_karma) == 0) {
        echo 'No karma yet';
    } else {
        $table = new HTML_Table('style="width: 90%"');
        $table->setCaption('Karma levels for ' . htmlspecialchars($handle), 'style="background-color: #CCCCCC;"');
        $table->addRow(array("Level", "Added by", "Added at", "Remove"), null, 'th');
        foreach ($user_karma as $item) {
            $remove = sprintf("karma.php?action=remove&amp;handle=%s&amp;level=%s",
                              htmlspecialchars($handle),
                              htmlspecialchars($item['level']));

            $table->addRow(array(htmlspecialchars($item['level']),
                          htmlspecialchars($item['granted_by']),
                          htmlspecialchars($item['granted_at']),
                          make_link($remove, make_image("delete.gif"),
                                                         false,
                                                         'onclick="javascript:return confirm(\'Do you really want to remove the karma level ' . htmlspecialchars($item['level' ]) . '?\');"')
            ));
        }
        echo $table->toHTML();
    }

    echo "<br /><br />";

    $table = new HTML_Table('style="width: 100%"');
    $table->setCaption("Grant karma to " . htmlspecialchars($handle), 'style="background-color: #CCCCCC;"');

    $form = new HTML_QuickForm2('karma_grant', 'post', array('action' => 'karma.php?action=grant'));
    $form->removeAttribute('name');

    $form->addElement('text', 'level')->setLabel('Level:&nbsp;');
    $form->addElement('hidden', 'handle')->setValue(htmlspecialchars($handle));
    $form->addElement('submit', 'submit')->setLabel('Submit Changes');
    $csrf_token_value = create_csrf_token($csrf_token_name);
    $form->addElement('hidden', $csrf_token_name)->setValue($csrf_token_value);
    $table->addRow(array((string)$form));
    echo $table->toHTML();
}

echo "<p>&nbsp;</p><hr />";

$table = new HTML_Table('style="width: 90%"');
$table->setCaption("Karma Statistics", 'style="background-color: #CCCCCC;"');


if (!empty($_GET['a']) && $_GET['a'] == "details" && !empty($_GET['level'])) {
    $table->addRow(array('Handle', 'Granted'), null, 'th');
    foreach ($karma->getUsers($_GET['level']) as $user) {
        $detail = sprintf("Granted by <a href=\"/user/%s\">%s</a> on %s",
                          htmlspecialchars($user['granted_by']),
                          htmlspecialchars($user['granted_by']),
                          htmlspecialchars($user['granted_at'])
                          );
        $table->addRow(array(make_link("/user/" . htmlspecialchars($user['user']),
                      htmlspecialchars($user['user'])),
                      $detail));
    }
} else {
    $table->addRow(array('Level', '# of users'));
    foreach ($karma->getLevels() as $level) {
        $table->addRow(array(make_link("karma.php?a=details&amp;level=" . htmlspecialchars($level['level']),
                                htmlspecialchars($level['level'])),
                      htmlspecialchars($level['sum'])));
    }
}

echo $table->toHTML();

echo '<br /><br />';
echo make_link('/admin/karma.php', 'Back');

response_footer();
