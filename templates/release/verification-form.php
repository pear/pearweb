<?php response_header('Upload New Release :: Verify');
report_error($errors, 'errors', 'ERRORS:<br />'
             . 'You must correct your package.xml file:');
report_error($warnings, 'warnings', 'RECOMMENDATIONS:<br />'
             . 'You may want to correct your package.xml file:');

?>
<form action="release-upload.php" method="post" >
<table class="form-holder" cellspacing="1">
 <caption class="form-caption">
  Please verify that the following release information is correct:
 </caption>
 <tr>
  <th class="form-label_left">Package:</th>
  <td class="form-input">
  <?php echo htmlspecialchars($info->getPackage()); ?>

  </td>
 </tr>
 <tr>
  <th class="form-label_left">Version:</th>
  <td class="form-input">
  <?php echo htmlspecialchars($info->getVersion()); ?>

  </td>
 </tr>
 <tr>
  <th class="form-label_left">Summary:</th>
  <td class="form-input">
  <?php echo nl2br(htmlspecialchars($info->getSummary())); ?>

  </td>
 </tr>
 <tr>
  <th class="form-label_left">Description:</th>
  <td class="form-input">
  <?php echo nl2br(htmlspecialchars($info->getDescription())); ?>

  </td>
 </tr>
 <tr>
  <th class="form-label_left">Release State:</th>
  <td class="form-input">
  <?php echo htmlspecialchars($info->getState()); ?>

  </td>
 </tr>
 <tr>
  <th class="form-label_left">Release Date:</th>
  <td class="form-input">
  <?php echo htmlspecialchars($info->getDate()); ?>

  </td>
 </tr>
 <tr>
  <th class="form-label_left">Release Notes:</th>
  <td class="form-input">
  <?php echo nl2br(htmlspecialchars($info->getNotes())); ?>

  </td>
 </tr>
 <tr>
  <th class="form-label_left">Package Type:</th>
  <td class="form-input">
  <?php echo htmlspecialchars($type); ?>

  </td>
 </tr>
<?php if (!count($errors)) { ?>
 <tr>
  <th class="form-label_left">&nbsp;</th>
  <td class="form-input">
   <input type="submit" name="verify" value="Verify Release" />
  </td>
 </tr>
<?php } ?>
 <tr>
  <th class="form-label_left">&nbsp;</th>
  <td class="form-input">
   <input type="submit" name="cancel" value="Cancel" />
  </td>
 </tr>
</table>
<input type="hidden" name="distfile" value="<?php echo htmlspecialchars($tmpfile, ENT_QUOTES,
    'ISO-8859-15') ?>" />
<input type="hidden" name="_fields" value="verify:cancel:distfile" />
</form>

