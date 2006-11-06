<?php response_header('Results'); ?>
<h2>Election Results for <?php echo $info['purpose'] ?>:</h2>
<table>
 <tr>
  <th class="form-label_left">Election Issue</td>
  <td class="form-input"><?php echo htmlspecialchars($info['detail']) ?></td>
 </tr>
 <tr>
  <th class="form-label_left">Election dates</th>
  <td class="form-input"><?php echo $info['votestart'] . ' until ' .
    $info['voteend']; ?></td>
 </tr>
 <tr>
  <th class="form-label_left">Results</th>
  <td class="form-input">
   <table><?php 
  foreach ($info['results'] as $result) {
      echo '<tr><td>' . number_format($result['votepercent'] * 100, 2) . '%</td><td><a href="' . $result['summary_link'] . '">' . htmlspecialchars($result['summary']) .
        '</a></td></tr>';
  }
   ?>
   </table>
  </td>
 </tr>
</table>
