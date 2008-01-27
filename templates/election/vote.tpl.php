<?php response_header('Vote'); ?>
<h1>Vote in a PEAR Election</h1>

<?php if (isset($error)): ?>
<div class="errors"><?php echo $error; ?></div>
<?php endif; // if (isset($error)): ?>
Current date is <strong><?php echo date('Y-m-d'); ?></strong>

<?php if (count($currentelections)): ?>
<h2>Current Elections:</h2>
<table>
 <tr>
  <th class="form-label_top">Active</th>
  <th class="form-label_top">Election Issue</th>
  <th class="form-label_top">Election dates</th>
  <th class="form-label_top">Have you voted?</th>
 </tr>
<?php
foreach ($currentelections as $election):
    $class = $election['active'] == 'yes' ? 'vote-active' : 'vote-inactive';
    if ($election['voted'] == 'yes') {
        $class = 'vote-complete';
    }
?>
<tr>
 <td class="<?php echo $class; ?>"><?php echo $election['active']; ?></td>
 <?php if ($election['voted'] == 'yes'): ?>
 <td class="<?php echo $class; ?>"><?php echo htmlspecialchars($election['purpose']); ?></td>
 <?php else: // if ($election['voted'] == 'yes'): ?>
 <td class="<?php echo $class; ?>"><a href="/election/info.php?election=<?php
    echo $election['id']; ?>&vote=1"><?php echo htmlspecialchars($election['purpose']); ?></a></td>
 <?php endif; // if ($election['voted'] == 'yes'): ?>
 <td class="<?php echo $class; ?>"><?php echo $election['votestart'] . ' until ' .
    $election['voteend']; ?></td>
 <td class="<?php echo $class; ?>"><?php echo $election['voted']; ?></td>
</tr>
<?php
endforeach; // foreach ($currentelections as $election):
?>
</table>
<?php endif; // if (count($currentelections))
require dirname(dirname(__FILE__)) . '/templates/election-results.tpl.php';
if (count($allelections)):
if ($retrieval && isset($info)): ?>
<h3>Your vote information:</h3>
<table>
 <tr>
  <td class="form-input">
   <table>
    <?php foreach ($info['choices'] as $choice): ?>
    <tr>
     <?php if (in_array($choice['choice'], $info['vote'], true)): ?>
     <input type="hidden" name="vote[]" value="<?php echo $choice['choice']; ?>" />
     <td><strong>X</strong></td>
     <?php else: // if (in_array($choice['choice'], $info['vote'], true)): ?>
     <td>&nbsp;</td>
     <?php endif; // if (in_array($choice['choice'], $info['vote'], true)): ?>
    <td>
     <?php echo htmlspecialchars($choice['summary']); ?>
    </td>
   </tr>
   <?php endforeach; // foreach ($info['choices'] as $choice): ?>
   </table>
  </td>
 </tr>
</table>
<?php
if ($info['vote'] == array('(abstain)')) {
    echo '<p><strong>(ABSTAIN)</strong></p>';
};
endif; // if ($retrieval && isset($info)) ?>
<h3>Retrieve your vote in an election:</h3>
<form name="checkvote" action="/election/index.php" method="post">
<table>
 <tr>
  <th class="form-label_top">Election Issue</th>
  <th class="form-label_top">Vote Salt (was emailed to you)</th>
 </tr>
 <tr>
  <td class="form-input">
   <select name="election">
   <?php foreach ($allelections as $election): ?>
    <option value="<?php echo $election['id']; ?>"><?php echo htmlspecialchars($election['purpose']); ?></option>
   <?php endforeach; // foreach ($allelections as $elections): ?>
   </select>
  </td>
  <td class="form-input">
   <input type="text" size="17" name="salt" value="<?php echo date('YmdHis') . mt_rand(1,999) ?>"/>
  </td>
 </tr>
</table>
<input type="submit" value="Retrieve Vote" />
</form>
<?php endif; // if (count($allelections))
response_footer();