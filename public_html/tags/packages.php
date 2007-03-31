<?php
require_once 'tags/Manager.php';
$tags = new Tags_Manager;
if (isset($_GET['tag'])) {
    if (!$tags->tagExists($_GET['tag'])) {
        throw new Exception('Unknown tag "' . $_GET['tag'] . '"');
    }
    $tag = strip_tags($_GET['tag']);
    $packages = $tags->getPackages($tag);
    require dirname(dirname(dirname(__FILE__))) . '/templates/tags/package.tpl.php';
} else {
    $cloud = $tags->getGlobalTagCloud();
    require dirname(dirname(dirname(__FILE__))) . '/templates/tags/cloud.tpl.php';
}
?>