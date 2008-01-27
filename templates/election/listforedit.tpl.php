<?php response_header('Edit Elections'); ?>
<h1>Edit PEAR Elections</h1>

<?php if (isset($error)): ?>
<div class="errors"><?php echo $error; ?></div>
<?php endif; // if (isset($error)): ?>
Current date is <strong><?php echo date('Y-m-d'); ?></strong>

<p>
 If you are a developer with pear.admin karma, you can edit any election except
 for active elections.  Otherwise you can only edit elections that you have created.
</p>
<?php if (!count($elections)): ?>
<h2>No Editable Elections for your user/karma level</h2>
<?php else: // if (!count($elections)): ?>
<h2>Editable Elections:</h2>
<table>
 <tr>
  <th class="form-label_top">Election Issue</th>
  <th class="form-label_top">Election dates</th>
  <th class="form-label_top">Creator</th>
 </tr>
<?php
foreach ($elections as $election):
    $class = $election['active'] == 'yes' ? 'vote-active' : 'vote-inactive';
?>
<tr>
 <td class="<?php echo $class; ?>"><a href="/election/edit.php?election=<?php
    echo $election['id']; ?>"><?php echo htmlspecialchars($election['purpose']); ?></a></td>
 <td class="<?php echo $class; ?>"><?php echo $election['votestart'] . ' until ' .
    $election['voteend']; ?></td>
 <td class="<?php echo $class; ?>"><?php echo $election['creator']; ?></td>
</tr>
<?php
endforeach; // foreach ($elections as $election):
?>
</table>
<?php endif; // if (count($elections))
response_footer();