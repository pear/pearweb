<?php response_header('PEAR :: Password Reset for ' . htmlspecialchars($user)); ?>
<h1>Password reset for <?php echo htmlspecialchars($user) ?></h1>
<p>
 Your password has been marked for change.  You must confirm this change, instructions
 have been sent in an email to you, please check your email.
</p>
<div class="explain">
<strong>WARNING</strong>: your password will NOT be changed until you confirm the
request through instructions in your email.
</div>
<?php response_footer(); ?>