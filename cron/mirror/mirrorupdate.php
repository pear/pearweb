<?php
// **** replace this with the mirror alias
$mirror = 'de';
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__FILE__) . '/rest/r',
    RecursiveDirectoryIterator::CURRENT_AS_FILEINFO)) as $file) {
    if ($file->isDir()) continue;
    if (!preg_match('/^\d+(?:\.\d+)*(?:[a-zA-Z]+\d*)?\.xml$/', $file->getFileName())) continue;
    $c = file_get_contents($file->getPathname());
    file_put_contents($file->getPathname(), str_replace('<g>http://pear.php.net/get',
        '<g>http://' . $mirror . '.pear.php.net/get', $c));
}
?>