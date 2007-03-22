<?php response_header('UserNote'); ?>
<h1>Note Management Area</h1>

<?php 
// {{{ isset($error)
if (strlen(trim($error)) > 0):
?>
<div class="errors"><?php echo $error; ?></div>
<?php endif; ?>


<form action="/notes/admin/trans.php" method="post">
<input type="hidden" name="action" value="updateMass" />
<table>
 <tr>
  <th>Approve</th>
  <td>Comment</td>
  <td>Name/Email</td>
 </tr>
<?php
foreach ($pendingComments as $pendingComment):
?>
<tr>
 <th><input type="checkbox" name="noteIds[]" value="<?php echo $pendingComment['note_id']; ?>" /></th>
 <td><?php echo substr(htmlspecialchars($pendingComment['note_text']), 0, 200) . '...'; ?></td>
 <td><?php echo htmlspecialchars($pendingComment['user_name']); ?></td>
</tr>
<?php endforeach; ?>
</table>
</form>
<?php response_footer(); ?>
