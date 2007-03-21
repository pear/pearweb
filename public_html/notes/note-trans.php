<?php

/**
 * Don't we all hate request ? 
 * For now ok.. as long as I keep
 * it secure.. I'll fix after.
 */
$defaultEncoding = 'UTF-8';
$action          = strtolower($_REQUEST['action']);

/**
 * Switch between the actions passed to the script.
 *
 * Trans is just a word for transition script.
 */
switch ($action) {
    case 'add':
        break;
    case 'form':
        if (isset($_GET['uri'])) {
            $uri = htmlentities($_GET['uri'], ENT_QUOTES, $defaultEncoding);
            header("Location: /notes/add-note-form.php?uri=$uri");
            exit;
        }
        break;
}
