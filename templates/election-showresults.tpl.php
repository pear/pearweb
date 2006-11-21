<?php response_header('Results'); ?>
<h2>Election Results for <?php echo $info['purpose'] ?>:</h2>
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
  <th class="form-label_left">Results (<?php echo count($info['winners']) ?> winners)</th>
  <td class="form-input">
   <table>
    <tr><th>Vote percentage</th><th>Choice</th><th>Votes</th></td></tr>
   <?php
  foreach ($info['results'] as $i => $result) {
      if (in_array($result['choice'], $info['winners'])) {
          echo '<tr><td class="vote-winner">' . number_format($result['votepercent'] * 100, 2) .
              '%</td><td class="vote-winner"><a href="' . $result['summary_link'] . '">' .
              htmlspecialchars($result['summary']) .
              '</a></td><td class="vote-winner">' . number_format($result['votetotal']) . 
              '</td></tr>';
      } else {
          echo '<tr><td>' . number_format($result['votepercent'] * 100, 2) .
              '%</td><td><a href="' . $result['summary_link'] . '">' .
              htmlspecialchars($result['summary']) .
              '</a></td><td>' . number_format($result['votetotal']) . 
              '</td></tr>';
      }
  }
   ?>
    <tr><td><?php echo number_format($info['abstain'] * 100, 2) ?>%</td><td>Abstained</td><td><?php echo number_format($info['abstaincount']) ?></td></tr>
   </table>
  </td>
 </tr>
</table>
