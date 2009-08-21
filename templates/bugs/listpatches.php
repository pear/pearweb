<?php if (count($patches)): ?>
<table class="patchlist">
 <tbody>
<?php
    foreach ($patches as $lpPatch => $lpRevisions) {
        $url = 'bug.php?id=' .  urlencode($bug)
            . '&amp;edit=12'
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
            $revurl = 'bug.php?id=' . urlencode($bug)
                . '&amp;edit=12'
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
<?php endif; //if (count($patches)) ?>