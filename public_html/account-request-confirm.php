<?php
require_once 'election/pear-election-accountrequest.php';

$stripped = @array_map('strip_tags', $_GET);

response_header('Account confirmation');

print '<h1>Confirm account</h1>';

if (!empty($stripped['salt']) && strlen($salt = htmlspecialchars($stripped['salt'])) == 32) {
    $request = new PEAR_Election_Accountrequest();
    $request->confirmRequest($salt);
    report_success('Your account has been activated, you can now vote in
PEAR elections that are for the general PHP public');
} else {
    report_error('Unknown salt');
}
response_footer();
?>