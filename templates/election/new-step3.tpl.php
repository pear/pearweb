<?php
response_header('Election :: ' . ucfirst($new));
?>
<h1>Confirm Election Details [Step 3]</h1>
<?php
if ($error) {
    foreach ($error as $err) {
        echo '<div class="errors">' . htmlspecialchars($err) . '</div>';
    }
}
?>
<form name="newelection" action="/election/<?php echo $new ?>.php" method="post">
<input type="hidden" name="step" value="4" />
<?php if ($new == 'edit'): ?>
<input type="hidden" name="election_id" value="<?php echo $election_id ?>" />
<?php endif; // if ($new == 'edit'): ?>
<input type="hidden" name="choices" value="<?php echo $info['choices'] ?>" />
 <table>
 <?php for ($i = 1; $i <= $info['choices']; $i++): ?>
  <tr>
   <th class="form-label_left">Choice #<?php echo $i ?></th>
   <td class="form-input">
    Summary:<br />
    <input type="hidden" name="summary<?php echo $i ?>" value="<?php echo htmlspecialchars($info['summary' . $i]) ?>" />
    <?php echo htmlspecialchars($info['summary' . $i]) ?><br />
    Link to more info:<br />
    <input type="hidden" name="summary_link<?php echo $i ?>" value="<?php echo htmlspecialchars($info['summary_link' . $i]) ?>" />
    <a href="<?php echo htmlspecialchars($info['summary_link' . $i]) ?>"><?php echo htmlspecialchars($info['summary_link' . $i]) ?></a>
   </td>
  </tr>
 <?php endfor; // for ($i = 1; $i < $info['choices']; $i++): ?>
  <tr>
   <th class="form-label_left">Eligible Voters</th>
   <td class="form-input">
    <input type="hidden" name="eligiblevoters" value="<?php echo $info['eligiblevoters'] ?>" />
    <?php if ($info['eligiblevoters'] == 1) {
        echo 'PEAR Developers';
} else {
        echo 'General PHP Public';
} ?>
   </td>
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
    <?php echo htmlspecialchars($info['detail']) ?>
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
 <input type="submit" name="newelection" value="Save Election" /><input type="submit" name="cancel" value="Cancel" />
</form>
<?php response_footer();