<?php
/**
 * Download a patch
 */
$revision = isset($_GET['revision']) ?
    filter_var($_GET['revision'], FILTER_SANITIZE_STRING) : null;
$patch    = isset($_GET['patch'])    ?
    filter_var($_GET['patch'], FILTER_SANITIZE_STRING) : null;
$id       = isset($_GET['id'])       ? (int)$_GET['id'] : null;

if (!$revision || !$patch || !$id) {
    header('HTTP/1.0 400 Bad Request');
    header('Content-Type: text/plain');
    response_header('Error :: File does not exist');
    report_error('Please specify id, patch and revision');
    response_footer();
    exit();
}

require_once 'bugs/patchtracker.php';
$pt = new Bugs_Patchtracker();

if (!$pt->isPatchValid($id, $patch, $revision)) {
    header('HTTP/1.0 404 Not Found');
    response_header('Error :: Patch not found');
    report_error('Patch or revision does not exist');
    response_footer();
    exit;
}

$path = $pt->getPatchFullpath($id, $patch, $revision);
if (!file_exists($path)) {
    header('HTTP/1.0 404 Not Found');
    response_header('Error :: File does not exist');
    report_error('File does not exist on server');
    response_footer();
    exit;
}

require_once 'HTTP.php';
header('Last-modified: ' . HTTP::date(filemtime($path)));
header('Content-type: application/octet-stream');
header('Content-disposition: attachment; filename="' . $patch . '.patch.txt"');
header('Content-length: '.filesize($path));
readfile($path);

?>
