<?php if (count($completedelections)): ?>
<h2>Election Results for Completed Elections:</h2>
<table>
 <tr>
  <th class="form-label_top">Election Issue</th>
  <th class="form-label_top">Election dates</th>
  <th class="form-label_top">Did you vote?</th>
 </tr>
<?php
foreach ($completedelections as $election):
    $class = 'vote-inactive';
    if ($election['voted'] == 'yes') {
        $class = 'vote-complete';
    }
?>
<tr>
 <td class="<?php echo $class; ?>"><a href="/election/info.php?election=<?php
    echo $election['id']; ?>&results=1"><?php echo htmlspecialchars($election['purpose']); ?></a></td>
 <td class="<?php echo $class; ?>"><?php echo $election['votestart'] . ' until ' .
    $election['voteend']; ?></td>
 <td class="<?php echo $class; ?>"><?php echo $election['voted']; ?></td>
</tr>
<?php
endforeach; // foreach ($completedelections as $election):
?>
</table>
<?php endif; // if (count($completedelections)): ?>