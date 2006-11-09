<?php
response_header('Election :: New');
?>
<h1>Create New Election [Step 1]</h1>
<?php
if ($error) {
    foreach ($error as $err) {
        echo '<div class="errors">' . htmlspecialchars($err) . '</div>';
    }
}
?>
<form name="newelection" action="/election-new.php" method="post">
<input type="hidden" name="step" value="2" />
 <table>
  <tr>
   <th class="form-label_left" colspan="2">(All entries are required)</th>
  </tr>
  <tr>
   <th class="form-label_left">Eligible Voters</th>
   <td class="form-input">
    <select name="eligiblevoters">
    <?php
     if ($info['eligiblevoters'] == '1') {
         $ev1 = ' selected="true"';
         $ev2 = '';
     } else {
         $ev2 = ' selected="true"';
         $ev1 = '';
     }
    ?>
     <option value="1"<?php echo $ev1 ?>>PEAR Developers</option>
     <option value="2"<?php echo $ev2 ?>>General PHP Public</option>
    </select>
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Election Purpose (summary)</th>
   <td class="form-input">
    <input type="text" name="purpose" size="100" maxlength="100" value="<?php
        echo htmlspecialchars($info['purpose']) ?>" />
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Number of items to choose from (items created in step 2)</th>
   <td class="form-input">
    <select name="choices">
<?php
    for ($i = 2; $i <= 20; $i++) {
        if ($info['choices'] == $i) {
            $sel = ' selected="selected"';
        } else {
            $sel = '';
        }
        echo '    <option' . $sel . '>' . $i . '</option>' . "\n";
    } ?>
    </select>
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Election detail (wiki markup)</th>
   <td class="form-input">
    <textarea name="detail" rows="20" cols="75"><?php echo $info['detail'] ?></textarea>
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Election start date (must be at least 30 days from now)</th>
   <td class="form-input">
    <select name="year">
    <?php
    foreach ($years as $year) {
        if ($info['year'] == $year) {
            $sel = ' selected="selected"';
        } else {
            $sel = '';
        }
        echo '    <option' . $sel . '>' . $year . '</option>' . "\n";
    } ?>
    </select>-<select name="month">
    <?php
    for ($i = 1; $i <= 12; $i++) {
        if ($info['month'] == $i) {
            $sel = ' selected="selected"';
        } else {
            $sel = '';
        }
        echo '    <option' . $sel . '>' . $i . '</option>' . "\n";
    } ?>
    </select>-<select name="day">
    <?php
    for ($i = 1; $i <= 31; $i++) {
        if ($info['day'] == $i) {
            $sel = ' selected="selected"';
        } else {
            $sel = '';
        }
        echo '    <option' . $sel . '>' . $i . '</option>' . "\n";
    } ?>
    </select>
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Length of election in days</th>
   <td class="form-input">
    <select name="length">
    <?php
    for ($i = 1; $i <= 7; $i++) {
        if ($info['length'] == $i) {
            $sel = ' selected="selected"';
        } else {
            $sel = '';
        }
        echo '    <option' . $sel . '>' . $i . '</option>' . "\n";
    } ?>
    </select>
   </td>
  </tr>
  <tr>
   <th class="form-label_left" colspan="2">
    The next two sections relate to the number of items
    that a single voter can choose.  For most elections, voters
    should choose only one (minimum=maximum=1).  For some
    elections, like QA group, the voter should choose up to 5
    developers to populate the QA group (minimum=1,maximum=5), and
    so on.
   </th>
  </tr>
  <tr>
   <th class="form-label_left">Minimum votes needed</th>
   <td class="form-input">
    <select name="minimum">
    <?php
    for ($i = 1; $i <= 19; $i++) {
        if ($info['minimum'] == $i) {
            $sel = ' selected="selected"';
        } else {
            $sel = '';
        }
        echo '    <option' . $sel . '>' . $i . '</option>' . "\n";
    } ?>
    </select>
   </td>
  </tr>
  <tr>
   <th class="form-label_left">Maximum votes allowed</th>
   <td class="form-input">
    <select name="maximum">
    <?php
    for ($i = 1; $i <= 19; $i++) {
        if ($info['maximum'] == $i) {
            $sel = ' selected="selected"';
        } else {
            $sel = '';
        }
        echo '    <option' . $sel . '>' . $i . '</option>' . "\n";
    } ?>
    </select>
   </td>
  </tr>
 </table>
 <input type="submit" name="newelection" value="Create New Election" />
</form>
<?php response_footer();