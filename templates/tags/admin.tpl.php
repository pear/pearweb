<?php response_header('PEAR :: Administer Tags'); ?>
<h1>Administer Tags</h1>
<?php
if (count($errors)) {
    foreach ($errors as $error) {
        echo '<div class="errors">', htmlspecialchars($error), '</div>';
    }
}
?>
<form name="tagsform" action="/tags/admin.php" method="post">
<table>
<tbody>
 <tr>
  <th class="form-label_left">Tag: (letters/numbers/underscore/period)</th>
  <td class="form-input">
   <input name="tag" size="20" maxlength="50" value="<?php echo htmlspecialchars($tagname) ?>" type="text" />
  </td>
 </tr>
 <tr>
  <th class="form-label_left">Description:</th>
  <td class="form-input">
   <input name="desc" size="50" maxlength="200" value="<?php echo htmlspecialchars($desc) ?>" type="text" />
  </td>
 </tr>
 <?php if ($admin): ?>
 <tr>
  <th class="form-label_left">Administrative? (Can only be deleted by admins):</th>
  <td class="form-input">
   <input name="admintag" type="checkbox" value="1" />
  </td>
 </tr>
 <?php endif; ?>
</tbody>
</table>
<input type="submit" name="addtag" value="Add Tag" /><br />
<?php foreach ($tags as $tag) {
    if (($admin && $tag['adminkey']) || !$tag['adminkey']) {
        echo '<input type="checkbox" id="tags[',$tag['tagid'],']" name="tags[',
             $tag['tagid'],']" />';
    } else {
        echo '&nbsp;&nbsp;&nbsp;';
    }
    echo '<label for="tags[',$tag['tagid'],']">',$tag['tagname'],' (',
         htmlspecialchars($tag['tagdesc']),')</label><br />';
}
?>
<input type="submit" name="deltag" value="Delete Selected Tags"
onclick="javascript:return confirm('Really delete selected tags?');"/><br />
</form>
<?php response_footer();
