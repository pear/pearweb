<?php response_header('PEAR :: Password Changed for ' . htmlspecialchars($user)); ?>
<h1>Password has changed for <?php echo htmlspecialchars($user) ?></h1>
<p>
 Your password has been changed.  You may now <a href="https://pear.php.net/login.php">Log in</a>.
</p>
<?php response_footer(); ?>