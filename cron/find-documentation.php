<?php
/**
 * Trying to find documentation URLs for PEAR packages in the peardoc Docbook sources.
 * Updates the package doc links in database if they are empty.
 *
 * Parameters:
 *  --debug   Display debugging data
 *
 * @author  Martin Jansen <mj@php.net>
 * @license LGPL
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
$debug = in_array('--debug', $argv);

$vfs = new VFS_file(array('vfsroot' => $basepath));

$options = array(
    'persistent'  => false,
    'portability' => DB_PORTABILITY_ALL,
);
$dbh =& DB::connect(PEAR_DATABASE_DSN, $options);
if (DB::isError($dbh)) {
    print $dbh->getMessage() . "\n" . $dbh->getUserInfo() . "\n";
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
    checkDocLog('checkDocumentation of ' . $path);
    //our xml file contains entities that include other files
    // we need to remove them since loading them would take really really long
    $xmlstr = preg_replace(
        '/&[a-zA-Z0-9._-]+;/',
        '',
        file_get_contents($path)
    );

    $document = simplexml_load_string($xmlstr);
    $document->registerXPathNamespace('db', 'http://docbook.org/ns/docbook');
    $titles = $document->xpath("//db:title");
    $books = $document->xpath("//db:book");

    if (empty($titles)) {
        throw new Exception("No //title element");
    }

    if (empty($books)) {
        throw new Exception("No //book element");
    }

    $attributes = $books[0]->attributes('http://www.w3.org/XML/1998/namespace');
    if (empty($attributes['id'])) {
        throw new Exception('Missing package xml:id attribute');
    }

    return array((string)$titles[0], $attributes['id']);
}

function checkDocLog($msg)
{
    $GLOBALS['debug'] && print($msg . "\n");
}

// {{{ readFolder()
function readFolder($folder)
{
    global $vfs, $basepath, $dbh, $update, $host;

    static $level;
    $level++;

    if (substr($folder, -5) == '/.svn') {
        return;
    }

    checkDocLog('readFolder ' . $folder);
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

                    checkDocLog('trying  ' . $host . $url);
                    $request = new HTTP_Request2($host . $url);
                    $response = $request->send();

                    if ($response->getStatus() >= 400) {
                        $new_url = preg_replace("=\.([^\.]+)\.php$=", ".php", $url);
                        $request->setURL($host . $new_url);
                        checkDocLog('trying2 ' . $host . $new_url);
                        $response = $request->send();
                        $url = $response->getStatus() > 400 ? '' : $new_url;
                    }

                    if ($url) {
                        checkDocLog('Found doc url: ' . $url . ', title: ' . $title);
                        $res = $dbh->execute($update, array($url, $title));
                    } else {
                        checkDocLog('No url for ' . $title);
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
