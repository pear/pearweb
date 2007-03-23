<?php
if (!$ajax) {
    response_header('View Full Comment');
    echo '<h1>View Comment</h1>';
?>
<div style="width: 80%; wrap: virtual; background: #C0C0C0; border: 1px dashed black;">
<?php } ?>

<p>
 <?php
  if (PEAR::isError($noteContent)) {
      echo 'Problem retrieving the note';
  } else {
     echo htmlspecialchars($noteContent['note_text']); 
  }   
  ?>
</p>
<?php
if (!$ajax) {
?>
</div>
<?php
    response_footer();
    exit;
}
?>
