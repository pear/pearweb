<?php response_header('Add a User Note'); ?>
<h1>Add a Note to <?php echo htmlspecialchars($noteUrl) ?></h1>
<?php 
    // {{{ isset($error)
    if (isset($error)) {
?>

<div class="errors"><?php echo $error; ?></div>

<?php 
    /**
     * If there's no uri then let's just quit..
     */
    response_footer(); 
    exit;
    
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
 <a href="http://pear.php.net/bugs/report.php?package[]=Documentation">This Location</a>.
 Questions can be answered through our <a href="http://pear.php.net/support">Support
 Channels</a>.
</p>
<form action="/notes/add-note.php" method="post">
<input type="hidden" name="noteUrl" value="<?php echo htmlspecialchars($noteUrl) ?>" />
<input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect) ?>" />
<table>
<tbody>
 <tr>
  <td colspan="2"></td>
 </tr>
 <tr>
  <!-- We will care after about finding the user, this is quick fix. -->
  <th class="form-label_left">Your email address (or name):</th>
  <td class="form-input">
   <input name="user" size="40" maxlength="40" value="user@example.com" type="text" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Your notes:</th>
  <td class="form-input"><textarea name="note" rows="16" cols="60" wrap="virtual"></textarea>
   <br />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Solve this: "<?php echo $spamCheck?> = ?"<br/></th>
  <td class="form-input"><input name="answer" size="40" maxlength="10" type="text"></td>
 </tr>
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
