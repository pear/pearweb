<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once "HTML/Form.php";
require_once "HTTP/Upload.php";
require_once "PEAR/Config.php";
require_once "PEAR/PackageFile.php";

auth_require();

response_header("PEAR Administration - Package Maintainers");

echo "<h1>PEAR Administration - Package Maintainers</h1>";

PEAR::pushErrorHandling(PEAR_ERROR_RETURN);

$upload = new HTTP_Upload;
$file = $upload->getFiles("f");

if (PEAR::isError($file) || !$file->isValid() || !preg_match("/^package[\-\d\w]*\.xml$/", $file->getProp("real"))) {

    echo "<p>Welcome to the interface for managing package maintainers. ";
    echo "In order to update the maintainer database you first have to ";
    echo "apply the changes to the <tt>package(2).xml</tt> file for the ";
    echo "package in question.  Afterwards this file can uploaded via the ";
    echo "form below, and the database will be updated accordingly.</p>";

    $form = new HTML_Form("/admin/package-maintainers.php", "post", "", "", "multipart/form-data");
    $form->addFile("f", "File:");
    $form->addSubmit();
    $form->display();

    echo "<p><strong>Warning:</strong> There is no confirmation screen, ";
    echo "so you should make sure that the information in ";
    echo "<tt>package(2).xml</tt> is correct before continuing.</p>";

} else {
    PEAR::popErrorHandling();

    $config = &PEAR_Config::singleton();
    $pf = &new PEAR_PackageFile($config);
    
    $pkg_info = $pf->fromPackageFile($file->getProp("tmp_name"), PEAR_VALIDATE_NORMAL);

    $pkg_id = package::info($pkg_info->getName(), "id");
    if (!$pkg_id) {
        PEAR::raiseError("No such package " . $pkg_info->getName());
    }

    $maintainers = array();
    foreach ($pkg_info->getMaintainers() as $m) {
        if (isset($m['active'])) {
            if (is_numeric($m['active'])) {
                $active = $m['active'];
            } else {
                $active = ($m['active'] == "yes" ? 1 : 0);
            }
        } else {
            $active = 1;
        }
        $maintainers[$m['handle']] = array("role" => $m['role'], "active" => $active);
    }

    echo "<div class=\"explain\">The following log messages were generated ";
    echo "by the update process:\n<ul>";
    $result = maintainer::updateAll($pkg_id, $maintainers, true);
    echo "</ul>\n</div>\n";
    if ($result) {
        echo "<br /><div class=\"success\">The maintainers database was updated successfully.</div>\n";
    }

    echo hdelim() . "<p><a href=\"/admin/package-maintainers.php\">Start again</a></p>\n";
}

response_footer();
