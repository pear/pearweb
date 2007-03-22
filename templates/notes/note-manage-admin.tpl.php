<?php response_header('Approve Pending User Notes'); ?>
<h1>Note Management Area</h1>

<?php if (strlen(trim($error)) > 0): // {{{ error ?>
<div class="errors"><?php echo $error; ?></div>
<?php endif; // }}} ?>


<?php if (isset($message) && strlen(trim($message)) > 0): // {{{ message?>
<div class="message"><?php echo $message; ?></div>
<?php endif; ?>

<?php
if (isset($url) && !empty($url)) {
    echo '<a href="/manual/en/', 
          urlencode(htmlspecialchars($url)), 
         '">Return to manual</a>';
}
?>

<form action="/notes/admin/trans.php" method="post">
 <input type="hidden" name="action" value="updateMass" />
 <input type="hidden" name="url" value="<?php echo htmlspecialchars($url) ?>" />
 <table class="form-holder" cellspacing="1">
  <tr>
   <th class="form-label_left">Approve</th>
   <td class="form-input">Comment</td>
   <td class="form-input">Name/Email</td>
  </tr>
  <?php foreach ($pendingComments as $pendingComment): ?>
  <tr>
  <th class="form-label_left">
   <input type="checkbox" name="noteIds[]" value="<?php echo $pendingComment['note_id']; ?>" />
   </th>
   <td class="form-input">
   <?php 
     if (strlen($pendingComment['note_text']) > 200) {
        echo substr(htmlspecialchars($pendingComment['note_text']), 0, 200) . '...';
     } else {
         echo htmlspecialchars($pendingComment['note_text']);
     } 
   ?></td>
   <td class="form-input">
   <?php echo htmlspecialchars($pendingComment['user_name']); ?>
   </td>
  </tr>
 <?php endforeach; ?>
  <tr>
   <th class="form-label_left">Approve</th>
   <td class="form-input">
    <input type="submit" name="approve" value="Approve selected comments" />
   </td>
   <td class="form-input"></td>
  </tr>
  <tr>
   <th class="form-label_left">Delete</th>
   <td class="form-input">
    <input type="submit" name="delete" value="Delete selected comments" />
   </td>
   <td class="form-input"></td>
  </tr>
 </table>
</form>
<?php response_footer(); ?>
