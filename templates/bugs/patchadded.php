<?php
$p = clean($package);
$b = clean($bug);
response_header('Patch Added :: ' . $p . ' :: Bug #' . $b);
report_success('Patch added');

include dirname(__FILE__) . '/listpatches.php';
response_footer();
?>