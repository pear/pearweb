<?php response_header('Vote :: ' . htmlspecialchars($info['purpose'])); ?>
<?php
if ($info['maximum_choices'] > 1) {
    $inputtype = 'checkbox';
    $inputname = 'vote[]';
    if ($info['minimum_choices'] > 1) {
        if ($info['minimum_choices'] != $info['maximum_choices']) {
            $pleasechoose = 'from ' . $info['minimum_choices'] . ' to ' .
                $info['maximum_choices'] . ' choices';
        } else {
            $pleasechoose = 'exactly ' . $info['maximum_choices'] . ' choices';
        }
    } else {
        if ($info['minimum_choices'] != $info['maximum_choices']) {
            $pleasechoose = 'up to ' . $info['maximum_choices'];
        } else {
            $pleasechoose = 'exactly ' . $info['maximum_choices'] . ' choices';
        }
    }
} else {
    $inputtype = 'radio';
    $inputname = 'vote';
    $pleasechoose = 'one';
}
?>
<h1>Vote in PEAR Election :: <?php echo htmlspecialchars($info['purpose']); ?></h1>

<?php if (isset($error)): ?>
<div class="errors"><?php echo $error; ?></div>
<?php endif; // if (isset($error)): ?>
<h2>Detail on the election</h2>
<?php echo $info['detail']; ?>
<h2>Please choose <?php echo $pleasechoose; ?>:</h2>
<form action="/election-info.php" method="post">
<input type="hidden" name="confirm" value="1" />
<input type="hidden" name="election" value="<?php echo $info['id']; ?>" />
<table>
 <tr>
  <td>
   <?php foreach ($info['choices'] as $choice): ?>
   <input type="<?php echo $inputtype; ?>" name="<?php echo $inputname; ?>" value="<?php echo $choice['choice']; ?>" />
   <?php echo htmlspecialchars($choice['summary']); ?> <a href="<?php echo $choice['summary_link']; ?>">(more info)</a><br />
   <?php endforeach; // foreach ($info['choices'] as $choice): ?>
  </td>
 </tr>
</table>
<input type="submit" name="abstain" value="Abstain (pass)" /><input type="submit" name="votesubmit" value="Vote" /><input type="submit" value="cancel" name="cancel" />
</form>