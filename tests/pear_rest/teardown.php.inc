<?php
$temp_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rest';
function cleanall($dir = null)
{
    $statedir = $dir !== null ? $dir : $statedir = $GLOBALS['statedir'];
    clearstatcache();
    if (file_exists($statedir)) {
        $dp = opendir($statedir);
        while ($ent = readdir($dp)) {
            if (in_array($ent, array('.', '..'))) {
                continue;
            }

            $dir = $statedir . DIRECTORY_SEPARATOR . $ent;
            if (is_dir($dir)) {
                cleanall($dir);
                continue;
            }
            unlink($dir);
        }
        closedir($dp);
        rmdir($statedir);
    }
}
cleanall($temp_path);