<?php
require_once 'tags/Manager.php';
$manager = new Tags_Manager;
auth_require('pear.dev');
$errors = array();
if (isset($_POST['addtag'])) {
    if (!isset($_POST['admintag'])) {
        $_POST['admintag'] = 0;
    }
    $errors = $manager->validateNewTag($_POST['tag'], $_POST['desc'], $_POST['admintag']);
    if (!count($errors)) {
        try {
            if ($_POST['admintag']) {
                $manager->createAdminTag($_POST['tag'], $_POST['desc']);
            } else {
                $manager->createRegularTag($_POST['tag'], $_POST['desc']);
            }
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
} elseif (isset($_POST['deltag'])) {
    if (isset($_POST['tags']) && is_array($_POST['tags'])) {
        foreach ($_POST['tags'] as $id => $unused) {
            try {
                $manager->deleteTag($id);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
    }
}
$tags = $manager->getTags();
$tagname = isset($_POST['tag']) ? strip_tags($_POST['tag']) : '';
$desc = isset($_POST['desc']) ? $_POST['desc'] : '';
$admin = auth_check('pear.admin');
require dirname(dirname(dirname(__FILE__))) . '/templates/tags/admin.tpl.php';