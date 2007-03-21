<?php response_header('Results'); ?>
<h2>Pending Election for <?php echo $info['purpose'] ?>:</h2>
<table>
 <tr>
  <th class="form-label_left">Election Issue</td>
  <td class="form-input"><?php echo $info['detail'] ?></td>
 </tr>
 <tr>
  <th class="form-label_left">Eligible Voters</td>
  <td class="form-input"><?php echo $info['eligiblevoters'] == 1 ? 
    'PEAR Developers' : 'General PHP Public' ?></td>
 </tr>
 <tr>
  <th class="form-label_left">Election dates</th>
  <td class="form-input"><?php echo $info['votestart'] . ' until ' .
    $info['voteend']; ?></td>
 </tr>
 <tr>
  <th class="form-label_left">Voter turnout</th>
  <td class="form-input"><?php echo number_format($info['turnout'] * 100, 2) ?>%</td>
 </tr>
 <tr>
  <th class="form-label_left">Choices</th>
  <td class="form-input">
   <table>
    <tr><th>Choice</th><th>More Info</th></td></tr>
   <?php
  foreach ($info['choices'] as $choice) {
      echo '<tr><td>' , htmlspecialchars($choice['summary']) , '</td><td><a href="' ,
           $choice['summary_link'] , '">', htmlspecialchars($choice['summary_link']) ,
           '</a></td></tr>';
  }
   ?>
   </table>
  </td>
 </tr>
</table>
<a href="/election/"><< Back to elections list</a>
<?php response_footer();
