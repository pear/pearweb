<?php response_header('Add a User Note'); ?>
<h1>Add a Note to <?php echo htmlspecialchars($noteUrl) ?></h1>
<?php
require 'pear-manual.php';
// {{{ isset($error)
if (isset($errors)) {
?>

<?php
    foreach ($errors as $error) {
        echo '<div class="errors">', htmlspecialchars($error), '</div>';
    }
}
// }}}
?>

<p>
 You can contribute your helpful information to the PEAR manual using this simple
 form.  Please proof-read your note.
</p>
<p>
 There is no need to hide or otherwise obfuscate your email address (like joeSPAM @#@at Gronk
 dawt net), as this will be done automatically.
</p>
<p>
 Please, please ONLY add actual information.  Questions, feature requests
 ("You guys should document this!@#"), or bug reports should not be posted as a note.
 We have a great bug tracker for bugs/feature requests at
 <a href="<?php echo getBugReportLink(getPackageNameForId($noteUrl)); ?>">this location</a>.
 Questions can be answered through our <a href="http://pear.php.net/support">support
 channels</a>.
</p>
<form action="/notes/add-note.php" method="post">
<input type="hidden" name="noteUrl" value="<?php echo htmlspecialchars($noteUrl) ?>" />
<input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect) ?>" />
<table>
<tbody>
 <tr>
  <td colspan="2">
 <?php if (!$loggedin) { ?>
 </td></tr>
 <tr>
  <!-- We will care after about finding the user, this is quick fix. -->
  <th class="form-label_left">Your email address (or name):</th>
  <td class="form-input">
   <input name="user" size="40" maxlength="40" value="<?php echo htmlspecialchars($email) ?>" type="text" />
  </td>
 </tr>
 <?php } // if ($loggedin) ?>
 <tr>
  <th class="form-label_left">Your notes:</th>
  <td class="form-input"><textarea name="note" rows="16" cols="60" wrap="virtual"><?php
  echo htmlspecialchars($note);
  ?></textarea>
   <br />
  </td>
 </tr>
 <?php if (!$loggedin) { ?>
 <tr>
  <th class="form-label_left">Solve this: "<?php echo $spamCheck?> = ?"<br/></th>
  <td class="form-input"><input name="answer" size="40" maxlength="10" type="text"></td>
 </tr>
 <?php } // if ($loggedin) ?>
 <tr>
  <th colspan="2">
   <!-- I'll add the preview soon -->
   <input name="action" value="Add Note" type="submit">
  </th>
 </tr>
</tbody>
</table>
</form>

<?php response_footer(); ?>
