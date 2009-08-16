<?php if (count($patches)): ?>
<div class="explain">
<table class="patchlist">
 <thead>
 <tr>
  <td>
   <a href="bug.php?id=<?php echo urlencode($bug) ?>">Return to Bug #<?php echo clean($bug) ?></a>
   <?php if ($canpatch): ?> | <a href="patch-add.php?bug_id=<?php echo urlencode($bug) ?>">Add a Patch</a>
   <?php endif; //if ($canpatch) ?>
  </td>
 </tr>
 </thead>
 <tbody>
<?php
    foreach ($patches as $lpPatch => $lpRevisions) {
        $url = 'patch-display.php?bug_id=' .  urlencode($bug)
            . '&amp;patch=' . urlencode($lpPatch)
            . '&amp;revision=latest';
        $revobsolete = false;
?>
 <tr>
  <th class="details">
   Patch <a href="<?php echo $url;?>"><?php echo clean($lpPatch); ?></a>
  </th>
  <td>
<?php
        foreach ($lpRevisions as $rev) {
            $revurl = 'patch-display.php?bug_id=' . urlencode($bug)
                . '&amp;patch=' . urlencode($lpPatch)
                . '&amp;revision=' . $rev[0];
            if ($revobsolete) {
                echo '<span class="obsolete">';
            }
?>
   revision <a href="<?php echo $revurl ?>"><?php echo format_date($rev[0]) ?></a> by <a href="/user/<?php echo $rev[1] ?>"><?php echo $rev[1] ?></a><br />
    <?php 
            if ($revobsolete) {
                echo '</span>';
            }
            $revobsolete = true;
        } //foreach ($revisions as $rev)
    ?>
  </td>
 </tr>
<?php
    }//foreach ($patches as $name => $rev)
?>
 </thead>
</table>
</div>
<?php endif; //if (count($patches)) ?>