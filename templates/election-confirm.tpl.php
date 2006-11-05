<?php response_header('Vote :: ' . htmlspecialchars($info['purpose'])); ?>
<h1>Vote in PEAR Election :: <?php echo htmlspecialchars($info['purpose']); ?></h1>

<?php if (isset($error)): ?>
<div class="errors"><?php echo $error; ?></div>
<?php endif; // if (isset($error)): ?>
<h2>Detail on the election</h2>
<?php echo nl2br(htmlspecialchars($info['detail'])); ?>
<h2>Your Vote:</h2>
<form action="/election-info.php" method="post">
<input type="hidden" name="election" value="<?php echo $info['id']; ?>" />
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
<?php if ($info['abstain']): ?>
<p><strong>(ABSTAIN)</strong></p>
<input type="hidden" name="abstain" value="1" />
<h1>are you SURE you wish to abstain?</h1>
<?php endif; ?>
<input type="submit" name="finalvote" value="<?php echo $info['abstain'] ? 'Abstain' : 'Confirm Vote'; ?>" /><input type="submit" value="cancel" />
</form>