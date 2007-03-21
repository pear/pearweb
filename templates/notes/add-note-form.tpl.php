<?php response_header('UserNote'); ?>
<h1>Add User Notes</h1>
<form action="/notes/add-note.php?url=<?=$noteUrl?>">
<tbody>
 <tr>
   <td colspan="2">
  </td>
  </tr>
  <tr>
  <!-- We will care after about finding the user, this is quick fix. -->
   <th class="subr">Your email address (or name):</th>
   <td><input name="user" size="60" maxlength="40" value="user@example.com" type="text"></td>
  </tr>
  <tr>
   <th class="subr">Your notes:</th>

   <td><textarea name="note" rows="20" cols="60" wrap="virtual"></textarea>
   <br>
  </td>
  </tr>
  <tr>
   <th class="subr"><?=$spamQuestion?><br>
   </th>

   <td><input name="answer" size="60" maxlength="10" type="text"></td>
  
  </tr>
  <tr>
   <th colspan="2">
    <input name="action" value="Preview" type="submit">
    <input name="action" value="Add Note" type="submit">
   </th>

  </tr>
 </tbody> 
</form>
<?php if (isset($error)): ?>
<div class="errors"><?php echo $error; ?></div>
<?php endif; // if (isset($error)): ?>
<?php response_footer(); ?>
