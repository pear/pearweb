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

require_once "pear-manual.php";
require_once "HTML/Form.php";
require_once "Damblan/Log.php";

auth_require("doc.chm-upload");
response_header("CHM documentation upload");

echo "<h1>CHM documentation upload</h1>";

if (!empty($_POST['submit'])) {
    include_once 'HTTP/Upload.php';
    $upload_obj = new HTTP_Upload("en");
    $logger = new Damblan_Log;

    foreach ($doc_languages as $shortcut => $name) {
        $file = $upload_obj->getFiles("chm_" . $shortcut);

        if ($file->isValid()) {
            $file->setName("pear_manual_" . $shortcut . ".chm");
            $res = $file->moveTo(PEAR_CHM_DIR);
            if (PEAR::isError($res)) {
                display_error($res->getMessage());
                $result = "failed";
            } else {
                echo "Upload of <b>" . $shortcut . "</b> successful<br />";
                $result = "succeeded";
            }

            $logger->log("CHM upload (" . $shortcut . ") by " . $auth_user->handle . ": " . $result);
        }
    }
}

$form = new HTML_Form("chm-upload.php", "POST");
$form->setDefaultFromInput(false);

foreach ($doc_languages as $shortcut => $name) {
    $form->addFile("chm_" . $shortcut, $name . ":", 5242880);
}

$form->addPlainText("", "(Leave the field blank if there is no file for the language)");
$form->addSubmit("submit", "Upload");
$form->display();

response_footer();
?>
