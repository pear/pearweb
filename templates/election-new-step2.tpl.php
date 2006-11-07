<?php
response_header('Election :: New');
?>
<h1>Create New Election [Step 2]</h1>
<?php
if ($error) {
    foreach ($error as $err) {
        echo '<div class="errors">' . htmlspecialchars($err) . '</div>';
    }
}
?>
<form name="newelection" action="/election-new.php" method="post">
<input type="hidden" name="step" value="3" />
<input type="hidden" name="choices" value="<?php echo $info['choices'] ?>" />
 <table>
 <?php for ($i = 1; $i <= $info['choices']; $i++): ?>
  <tr>
   <th class="form-label_left">Choice #<?php echo $i ?></th>
   <td class="form-input">
    Summary:<br /><input name="summary<?php echo $i ?>" value="<?php echo htmlspecialchars($info['summary' . $i]) ?>" size="100" maxlength="100" /><br />
    Link to more info:<br /><input name="summary_link<?php echo $i ?>" value="<?php echo htmlspecialchars($info['summary_link' . $i]) ?>" size="100" maxlength="255" />
   </td>
  </tr>
 <?php endfor; // for ($i = 1; $i < $info['choices']; $i++): ?>
  <tr>
   <td colspan="2">
    <input type="submit" name="add1choice" value="Add another choice" />
    <?php if ($info['choices'] > 2): ?>
    <input type="submit" name="delete1choice" value="Remove last choice" />
    <?php endif; ?>
   </td>
  </tr>
  <tr>
   <th class="form-label_left" colspan="2">Entered data (can be edited later)</th>
  </tr>
  <tr>
   <th class="form-label_left">Election Purpose</th>
   <td class="form-input">
    <input type="hidden" name="purpose" value="<?php echo htmlspecialchars($info['purpose']); ?>" />
    <?php echo htmlspecialchars($info['purpose']) ?>
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Election detail</th>
   <td class="form-input">
    <input type="hidden" name="detail" value="<?php echo htmlspecialchars($info['detail']); ?>" />
    <?php echo htmlspecialchars($info['purpose']) ?>
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Election start date</th>
   <td class="form-input">
    <input type="hidden" name="year" value="<?php echo date('Y', strtotime($info['year'])) ?>" />
    <input type="hidden" name="month" value="<?php echo date('m', strtotime($info['year'] . '-' . $info['month'] . '-' . $info['day'])) ?>" />
    <input type="hidden" name="day" value="<?php echo date('d',
        strtotime($info['year'] . '-' . $info['month'] . '-' . $info['day'])); ?>" />
    <?php echo $info['year'] . '-' . $info['month'] . '-' . $info['day'] ?>
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Length of election in days</th>
   <td class="form-input">
    <input type="hidden" name="length" value="<?php echo $info['length'] ?>" />
    <?php echo $info['length']; ?>
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Minimum votes needed</th>
   <td class="form-input">
    <input type="hidden" name="minimum" value="<?php echo $info['minimum'] ?>" />
    <?php echo $info['minimum'] ?>
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Maximum votes allowed</th>
   <td class="form-input">
    <input type="hidden" name="maximum" value="<?php echo $info['maximum'] ?>" />
    <?php echo $info['maximum'] ?>
   </td>
  </tr>
 </table>
 <input type="submit" name="newelection" value="Create New Election" />
</form>
<?php response_footer();