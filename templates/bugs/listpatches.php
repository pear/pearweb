<table>
 <tr>
  <td>
   <a href="bug.php?id=<?php echo urlencode($bug) ?>">Return to Bug #<?php echo clean($bug) ?></a>
   <?php if ($canpatch): ?> | <a href="patch-add.php?bug=<?php echo urlencode($bug) ?>">Add a Patch</a>
   <?php endif; //if ($canpatch) ?>
  </td>
 </tr>
<?php if (!count($patches)): ?>
 <tr>
  <th class="form-label_left">
   No patches
  </th>
 </tr>
<?php else: //if (!count($patches))
    foreach ($patches as $patch => $revisions):
?>
 <tr>
  <th class="form-label_left">
   Patch <a href="?bug=<?php echo urlencode($bug) ?>&patch=<?php echo urlencode($patch)
      ?>&revision=latest&display=1"><?php echo clean($patch); ?></a>
  </th>
  <td>
   Patch Versions: <a href="?bug=<?php echo urlencode($bug) ?>&patch=<?php echo urlencode($patch)
      ?>&revision=latest&display=1">latest</a>
   <?php foreach ($revisions as $revision): ?>
   <a href="?bug=<?php echo urlencode($bug) ?>&patch=<?php echo urlencode($patch)
      ?>&revision=<?php echo $revision ?>&display=1"><?php echo date('Y-m-d H:i:s', $revision) ?></a>
   <?php endforeach; //foreach ($revisions as $revision) ?>
  </td>
 </tr>
<?php
    endforeach; //foreach ($patches as $name => $revisions)
endif; //if (!count($patches)) ?>
</table>