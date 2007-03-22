<?php response_header('UserNote'); ?>
<h1>Note addition</h1>
<?php 
    // {{{ isset($error)
    if (!empty($errors)) {
?>

<div class="errors">
<?php
foreach ($errors as $error) {
    echo $error . '<br />'; 
}
?>
</div>

<form action="/notes/add-note.php" method="post">
<input type="hidden" name="noteUrl" value="<?=$noteUrl?>" />
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
  <th class="subr">What is the result ? : "<?=$spamCheck?>"<br/></th>
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
<?php
} else {
?>
    <div>
    Note added, it will be reviewed soon and you will be contacted if there's any problem.
    Go back to the previous url <a href="http://pear.php.net/<?=$noteUrl?>" 
                                   title="Previous Note">http://<?=htmlentities($_SERVER['SERVER_NAME'], ENT_QUOTES, 'UTF-8');?><?=$noteUrl?></a>
    </div>
<?php 

}
response_footer(); 

?>
