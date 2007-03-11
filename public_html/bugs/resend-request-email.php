<?php
if (!isset($_GET['handle'])) {
    response_header('Error: no handle selected');
    report_error('Error: no handle selected for display');
    response_footer();
    exit;
}
require 'bugs/pear-bug-accountrequest.php';
$account = new PEAR_Bug_Accountrequest($_GET['handle']);
if ($account->pending()) {
    $account->sendEmail();
} else {
    response_header('Error: handle does not need verification');
    report_error('Error: handle is either already verified or does not exist');
    response_footer();
    exit;
}
response_header('PEAR :: email re-sent');?>
<h1>Verification email resent for handle <?php echo htmlspecialchars($_GET['handle']) ?></h1>
<?php
response_footer();