
<h1>Confirm Password Change</h1>

<p>
 You have received an email describing how to confirm your password change request.
 Please enter your username and password reset code below.  If you have not received
 an email after several hours, please mail <a href="mailto:pear-qa@lists.php.net">pear-qa@lists.php.net</a>
 for further assistance.
</p>
<?php
if (count($errors)) {
echo '<div class="errors">';
    foreach ($errors as $error) {
        echo htmlspecialchars($error) . "<br />\n";
    }
}
echo '</div>';
?>
<form name="confirmreset" method="POST" action="password-confirm-change.php">
<table>
  <tr>
   <th class="form-label_left">Username</th>
   <td class="form-input">
    <input type="text" size="20" maxlength="20" value="<?php echo htmlspecialchars($handle); ?>" name="handle" />
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Password reset code</th>
   <td class="form-input">
    <input type="password" size="40" value="" name="resetcode" />
   </td>
  </tr>
</table>
<input type="submit" name="confirm" value="Reset Password" />
</form>
<?php
response_footer();
?>
