<?php if ($pending) { ?>
<div class="pending_note">Pending note <a href="/notes/admin/?url=<?php echo $id ?>">Approve/Delete</a>
<?php } else { ?>
<div class="note">
<?php } ?>
 <div class="note_handle">Note by: <?php echo $userHandle ?></div>
 <div class="note_time"><?php echo $linkUrl ?></div>
 <div class="note_text">
  <?php echo $comment ?>
 </div>
</div>
