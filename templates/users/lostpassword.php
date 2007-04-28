
<h1>Forgot your password?</h1>

<p>
 Forgot your password for logging in to the website?  Don&#39;t 
 worry &mdash; this happens to the best of us.
</p>
<p>
 Please follow these steps to reset your password:
 <ol>
  <li>Fill out the form below</li>
  <li>Check your email, follow the instructions.</li>
 </ol>
</p>
<p>
 If your email address is no longer valid,
 please mail <a href="mailto:pear-qa@lists.php.net">pear-qa@lists.php.net</a> and
 explain the situation, your password can only be reset manually.
</p>
<?php
if (count($errors)) {
    echo '<div class="errors">';
    foreach ($errors as $error) {
        echo htmlspecialchars($error) . "<br />\n";
    }
    echo '</div>';
}
?>
<form name="resetpassword" method="POST" action="forgot-password.php">
<table>
  <tr>
   <th class="form-label_left">Username</th>
   <td class="form-input">
    <input type="text" size="20" maxlength="20" value="<?php echo htmlspecialchars($handle); ?>" name="handle" />
   </td>
  </tr>
  <tr>
   <th class="form-label_left">New Password</th>
   <td class="form-input">
    <input type="password" size="20" value="" name="password" />
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Confirm New Password</th>
   <td class="form-input">
    <input type="password" size="20" value="" name="password2" />
   </td>
  </tr>
</table>
<input type="submit" name="resetpass" value="Reset Password" />
</form>
<?php
response_footer();
?>
