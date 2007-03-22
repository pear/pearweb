<?php response_header('Approve Pending User Notes'); ?>
<h1>Note Management Area</h1>

<?php 
 // {{{ isset($error)
if (strlen(trim($error)) > 0):
?>
<div class="errors"><?php echo $error; ?></div>
<?php endif; // }}} ?>

<?php 
if (strlen(trim($message)) > 0):
?>
<div class="message"><?php echo $message; ?></div>
<?php endif; ?>

<form action="/notes/admin/trans.php" method="post">
<input type="hidden" name="action" value="updateMass" />
<table class="form-holder" cellspacing="1">
 <tr>
  <th class="form-label_left">Approve</th>
  <td class="form-input">Comment</td>
  <td class="form-input">Name/Email</td>
 </tr>
<?php
foreach ($pendingComments as $pendingComment):
?>
<tr>
 <th class="form-label_left"><input type="checkbox" name="noteIds[]" value="<?php echo $pendingComment['note_id']; ?>" /></th>
 <td class="form-input"><?php if (strlen($pendingComment['note_text']) > 200) {
     echo substr(htmlspecialchars($pendingComment['note_text']), 0, 200) . '...';
}  else {
    echo htmlspecialchars($pendingComment['note_text']);
} ?></td>
 <td class="form-input"><?php echo htmlspecialchars($pendingComment['user_name']); ?></td>
</tr>
<?php endforeach; ?>
<tr>
 <th class="form-label_left">Approve</th>
 <td class="form-input"><input type="submit" value="Approve selected comments" /></td>
 <td class="form-input"></td>
</table>
</form>
<?php response_footer(); ?>
