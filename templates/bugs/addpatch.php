<?php response_header('Add Patch :: ' . clean($package)); ?>
<h2>Add a Patch to Bug #<?php echo clean($bug) ?> for Package <?php echo clean($package); ?></h2>
<form name="patchform" method="post" action="patch-add.php" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="10240" />
<input type="hidden" name="bug" value="<?php echo clean($bug) ?>" />
<?php
if ($errors) {
    foreach ($errors as $err) {
        echo '<div class="errors">' . htmlspecialchars($err) . '</div>';
    }
}
?>
<table>
 <tr>
  <th class="form-label_left">
   Choose an existing Patch to update, or add a new one
  </th>
  <td class="form-input">
   <input type="text" maxlength="40" name="name" value="<?php echo clean($name) ?>" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">
   Patch File
  </th>
  <td class="form-input">
   <input type="file" name="patch"/><br />
   <small>The patch name must be shorter than 40 characters and it must only contain alpha-numeric characters or hyphens.</small>
  </td>
 </tr>
</table>
<input type="submit" name="addpatch" value="Save" />
</form>
<h2>Existing patches:</h2>
<?php
$canpatch = false;
require dirname(__FILE__) . '/listpatches.php';
response_footer(); ?>