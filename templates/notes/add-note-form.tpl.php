<?php response_header('UserNote'); ?>
<h1>Add User Notes</h1>
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

<form action="/notes/add-note.php" method="post">
<input type="hidden" name="noteUrl" value="<?php echo $noteUrl ?>" />
<tbody>
 <tr>
  <td colspan="2"></td>
 </tr>
 <tr>
  <!-- We will care after about finding the user, this is quick fix. -->
  <th class="subr">Your email address (or name):</th>
  <td>
   <input name="user" size="60" maxlength="40" value="user@example.com" type="text" />
  </td>
 </tr>
 <tr>
  <th class="subr">Your notes:</th>
  <td><textarea name="note" rows="20" cols="60" wrap="virtual"></textarea>
   <br />
  </td>
 </tr>
 <tr>
  <th class="subr">What is the result of this ? "<?php echo $spamCheck?>"<br/></th>
  <td><input name="answer" size="60" maxlength="10" type="text"></td>
 </tr>
 <tr>
  <th colspan="2">
   <!-- I'll add the preview soon -->
   <input name="action" value="Add Note" type="submit">
  </th>
 </tr>
</tbody>
</form>

<?php response_footer(); ?>
