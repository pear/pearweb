<?php
/*
 * +----------------------------------------------------------------------+
 * | PEAR Web site version 1.0                                            |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2003-2004 The PEAR Group                               |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.02 of the PHP license,      |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available at through the world-wide-web at                           |
 * | http://www.php.net/license/2_02.txt.                                 |
 * | If you did not receive a copy of the PHP license and are unable to   |
 * | obtain it through the world-wide-web, please send a note to          |
 * | license@php.net so we can mail you a copy immediately.               |
 * +----------------------------------------------------------------------+
 * | Authors: Martin Jansen <mj@php.net>                                  |
 * +----------------------------------------------------------------------+
 *
 * $Id$
 */

/**
 * Trying to find documentation URLs for PEAR packages in the peardoc Docbook sources
 *
 * @version $Revision$
 */
require_once dirname(dirname(__FILE__)) . '/include/pear-config.php';
require_once 'PEAR.php';
require_once 'VFS.php';
require_once 'VFS/file.php';
require_once 'HTTP/Request2.php';

require_once 'DB.php';

if (!file_exists('en/package')) {
    echo "Please cd into peardoc checkout\n";
    exit(2);
}
$basepath = getcwd() . '/en/package/';

$vfs = new VFS_file(array('vfsroot' => $basepath));

$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh =& DB::connect(PEAR_DATABASE_DSN, $options);
if (DB::isError($dbh)) {
    print $dbh->getMessage() . "\n";
    exit(1);
}

$host = 'http://' . PEAR_CHANNELNAME;

$sql = "
    UPDATE packages SET
        doc_link = ?
    WHERE name = ? AND
    (doc_link IS NULL OR doc_link NOT LIKE 'http://%' OR doc_link LIKE '" . $host . "/%')";
$update = $dbh->prepare($sql);

function checkDocumentation($path) {

    $dom = new DOMDocument();
    @$dom->loadHTML(file_get_contents($path)); // I know: @ is evil, but it's either that or load chapters.ent - 422K of DTD

    $document = simplexml_import_dom($dom);
    $titles = $document->xpath("//title");
    $books = $document->xpath("//book");

    if (empty($titles)) {
        throw new Exception("No //title element");
    }

    if (empty($books)) {
        throw new Exception("No //book element");
    }

    $attributes = $books[0]->attributes();

    if (empty($attributes['xml:id'])) {
        throw new Exception("Missing package xml:id attribute");
    }

    return array((string)$titles[0], $attributes['xml:id']);
}


// {{{ readFolder()
function readFolder($folder)
{
    global $vfs, $basepath, $dbh, $update, $host;

    static $level;
    $level++;

    $result = $vfs->listFolder($folder);

    if ($folder == '.') {
        $folder = '';
    }

    foreach ($result as $file) {
       if (is_dir($basepath . $folder . '/' . $file['name'])) {
            if ($folder == '') {
                $newfolder = $file['name'];
            } else {
                $newfolder = $folder . '/' . $file['name'];
            }
            readFolder($newfolder);
            $level--;
        } else {
            if ($level == 2 && preg_match("/\.xml$/", $file['name'])) {
                $path = $basepath . $folder . '/' . $file['name'];

                try {
                    list($title, $package) = checkDocumentation($path);

        
                    $url = '/manual/en/' . $package . '.php';

                    $request = new HTTP_Request2($host . $url);
                    $response = $request->send();

                    if ($response->getStatus() == 404) {
                        $new_url = preg_replace("=\.([^\.]+)\.php$=", ".php", $url);
                        $request->setURL($host . $new_url);
                        $response = $request->send();
                        $url = $response->getStatus() == 404 ? $new_url : '';
                    }

                    if ($url) {
                        $res = $dbh->execute($update, array($url, $title));
                    }
                } catch (Exception $e) {
                    print $e->getMessage() . "\n";
                }
            }
        }
    }
}

// }}}
//checkDocumentation(dirname(__FILE__) . '/sample.xml');
readFolder('.');
