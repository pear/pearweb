<?php response_header('Results'); ?>
<h2>Election Results for <?php echo $info['purpose'] ?>:</h2>
<table>
 <tr>
  <th class="form-label_left">Election Issue</td>
  <td class="form-input"><?php echo $info['detail'] ?></td>
 </tr>
 <tr>
  <th class="form-label_left">Election dates</th>
  <td class="form-input"><?php echo $info['votestart'] . ' until ' .
    $info['voteend']; ?></td>
 </tr>
 <tr>
  <th class="form-label_left">Results</th>
  <td class="form-input">
   <table>
    <tr><td class="vote-winner">Winners</td><td class="vote-winner"><?php echo $info['maximum_choices'] ?></td></tr>
   <?php
   $winners = array();
  foreach ($info['results'] as $i => $result) {
      $winners[number_format($result['votepercent'] * 100, 2)] = 1;
      if (count($winners) <= $info['maximum_choices']) {
          echo '<tr><td class="vote-winner">' . number_format($result['votepercent'] * 100, 2) .
              '%</td><td class="vote-winner"><a href="' . $result['summary_link'] . '">' .
              htmlspecialchars($result['summary']) .
              '</a></td></tr>';
      } else {
          echo '<tr><td>' . number_format($result['votepercent'] * 100, 2) .
              '%</td><td><a href="' . $result['summary_link'] . '">' .
              htmlspecialchars($result['summary']) .
              '</a></td></tr>';
      }
  }
   ?>
    <tr><td><?php echo number_format($info['abstain'] * 100, 2) ?>%</td><td>Abstained</td></tr>
   </table>
  </td>
 </tr>
</table>
