<?php response_header('PEAR :: Register to Vote'); ?>
<h1>Register to Vote</h1>

<h2>
 If you already have a PEAR developer account, Please <a href="login.php?redirect=/election.php<?php
    if ($query) {
        echo urlencode($query);
    } ?>">Log in</a>, you are already registered to vote.
</h2>
<p>
 If you would like to vote in a general PEAR election, you need a user account.  Registration
 for an account is done at <a href="/account-request-vote.php">The Account Request Form.</a>
</p>