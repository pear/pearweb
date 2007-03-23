<?php response_header($title); ?>

<script src="/javascript/jquery.js"></script>
<script src="/javascript/thickbox.js"></script>

<style type="text/css">
@import "/css/thickbox.css";
</style>

<h1>Notes Management Area</h1>
<?php include dirname(dirname(dirname(__FILE__))) . '/templates/notes/note-manage-links.tpl.php'; ?>
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
 <input type="hidden" name="action" value="<?php echo $action ?>" />
 <input type="hidden" name="url" value="<?php echo htmlspecialchars($url) ?>" />
 <table class="form-holder" cellspacing="1">
  <tr>
   <th class="form-label_left">Status</th>
   <td class="form-input">Comment</td>
   <td class="form-input">Name/Email</td>
   <td class="form-input">View Note</td>
  </tr>
  <?php foreach ($pendingComments as $pendingComment): ?>
  <div id="noteView<?php echo $pendingComment['note_id'] ?>" style="visibility: hidden; display: none;">
   <?php echo htmlspecialchars($pendingComment['note_text']); ?>
  </div>
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
   <td class="form-input">
    <a class="thickbox" href="view-note.php?height=300&width=300&ajax=yes&noteId=<?php echo $pendingComment['note_id'] ?>" 
       title="See full comment">View</a> 
       <noscript>
        <a href="view-note.php?ajax=no&status=<?php echo $status ?>&noteId=<?php echo $pendingComment['note_id'] ?>" 
           title="No JS View">(no js)</a>
       </noscript>
  </tr>
  </tr>
 <?php endforeach; ?>
  <tr>
   <th class="form-label_left"><?php echo $caption ?></th>
   <td class="form-input">
    <input type="submit" name="<?php echo $name ?>" value="<?php echo $button ?>" />
   </td>
   <td class="form-input"></td>
   <td class="form-input"></td>
  </tr>
  <tr>
   <th class="form-label_left">Delete</th>
   <td class="form-input">
    <input type="submit" name="delete" value="Delete selected comments" />
   </td>
   <td class="form-input"></td>
   <td class="form-input"></td>
   
 </table>
</form>
<?php response_footer(); ?>
