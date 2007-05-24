<?php
$temp_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'rest';
function cleanall($dir = null)
{
    $statedir = $dir !== null ? $dir : $GLOBALS['statedir'];
    clearstatcache();
    if (file_exists($statedir)) {
        $dp = opendir($statedir);
        while ($ent = readdir($dp)) {
            if (in_array($ent, array('.', '..'))) {
                continue;
            }
            if (is_dir($statedir . DIRECTORY_SEPARATOR . $ent)) {
                cleanall($statedir . DIRECTORY_SEPARATOR . $ent);
                continue;
            }
            unlink($statedir . DIRECTORY_SEPARATOR . $ent);
        }
        closedir($dp);
        rmdir($statedir);
    }
}
cleanall($temp_path);
?>