<?php response_header('Vote :: ' . htmlspecialchars($info['purpose'])); ?>
<h1>Vote in PEAR Election :: <?php echo htmlspecialchars($info['purpose']); ?></h1>

<?php if (isset($error)): ?>
<div class="errors"><?php echo $error; ?></div>
<?php endif; // if (isset($error)): ?>
<h2>Detail on the election</h2>
<?php echo $info['detail']; ?>
<h2>Your Vote:</h2>
<table>
 <tr>
  <td class="form-input">
   <table>
    <?php foreach ($info['choices'] as $choice): ?>
    <tr>
     <?php if (in_array($choice['choice'], $info['vote'], true)): ?>
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
<?php endif; // if ($info['abstain']): ?>
<h2>IMPORTANT:</h2>
<p>
 Your vote serial number for this election is <strong><?php echo $salt; ?></strong>.
 Please write this number down, as it is the only way to retrieve your vote from the database
 and will not be stored to maintain the secret ballot.  If you lose this number, there is
 no way to connect you to your actual vote.
</p>
<p>
 The database does store a record of whether you have voted however, so you will not be
 allowed to vote again in this election.  Thank you for voting!
</p>
<p>
 <a href="/election/">Return to Elections</a>
</p>