<?php
function unlinkdir($path)
{
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path,
    RecursiveDirectoryIterator::KEY_AS_PATHNAME),
    RecursiveIteratorIterator::CHILD_FIRST) as $file => $info) {
        if ($info->isDot()) continue;
        if ($info->isFile()) {
            unlink($file);
        } else {
            rmdir($file);
        }
    }
}
