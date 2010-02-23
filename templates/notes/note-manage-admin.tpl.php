<?php
extra_styles('/css/thickbox.css');
response_header($title);
?>
<style type="text/css">
#actions_box {
    position: fixed; background-color: white; right: 0; bottom: 0; padding: 1em;
}

.user_note label {
    cursor: pointer;
}
.user_note {
    margin-top: 1.5em;
    padding-top: 1.5em;
    border-top: 1px solid rgb(200, 200, 200);
}

</style>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="/javascript/thickbox.js"></script>
<script type="text/javascript">
    var isChecked = false;
    
    $('#submitButton').live('click', function() {
        if (isChecked === false) {
            $('input[type=checkbox]').each(function() {
                $(this).attr('checked', 'check');
                isChecked = true;
            });
        } else {
            $('input[type=checkbox]').each(function() {
                $(this).attr('checked', false);
                isChecked = false;
            });
        }
    });
</script>
<h1>Notes Management Area</h1>
<h3><?php echo $title; ?></h3>
<?php include PEARWEB_TEMPLATEDIR . '/notes/note-manage-links.tpl.php'; ?>

<?php if (count($pendingComments) > 0) : ?>
<?php if (strlen(trim($error)) > 0): // {{{ error ?>
<div class="errors"><?php echo $error; ?></div>
<?php endif; // }}} ?>
<?php if (isset($message) && strlen(trim($message)) > 0): // {{{ message?>
<div class="message"><?php echo $message; ?></div>
<?php endif; // }}} ?>

<?php
if (isset($url) && !empty($url)) {
    echo '<a href="/manual/en/',
          urlencode(htmlspecialchars($url)),
         '">Return to manual</a>';
}
?>

<form action="/notes/admin/trans.php" method="post">
 <input type="hidden" name="action" value="<?php echo $action ?>" />
 <input type="hidden" name="url" value="<?php echo htmlspecialchars($url) ?>" />

    <div id="actions_box">
        <h3><?php echo $caption ?></h3>
        <input id="submitButton" type="button"  value="Select All" />&nbsp;
        <input type="submit" name="<?php echo $name ?>" value="<?php echo $button ?>" />
        <?php if ($name != 'undelete'): ?>
          <input type="submit" name="delete" value="Delete" />
        <?php endif; ?>
    </div>
 

  <?php foreach ($pendingComments as $pendingComment): ?>

    <div class="user_note">
        <h4><a href="/manual/en/<?php echo $pendingComment['page_url'] ?>"><?php echo $pendingComment['page_url'] ?></a> - <?php echo htmlspecialchars($pendingComment['user_name']); ?></h4>
        <p>
            <label>
               <input type="checkbox" name="noteIds[]" value="<?php echo $pendingComment['note_id']; ?>" style="display: block; float: right" />
               <?php
                 if (strlen($pendingComment['unfiltered_note']) > 200) {
                     echo substr(htmlspecialchars($pendingComment['unfiltered_note']), 0, 200) . '...';
                 } else {
                     echo $pendingComment['note_text'];
                 }
               ?>
            </label>
        </p>
        <p>
            <a class="thickbox" href="view-note.php?height=300&width=450&ajax=yes&noteId=<?php echo $pendingComment['note_id'] ?>"
               title="See full note">View full note</a> <a href="view-note.php?ajax=no&status=<?php echo $status ?>&noteId=<?php echo $pendingComment['note_id'] ?>"
               title="No JS View">(no js)</a>, or
            <a href="/notes/admin/trans.php?action=makeDocBug&noteId=<?php echo $pendingComment['note_id']; ?>&url=<?php echo $pendingComment['page_url']; ?>">make a Doc Bug</a>.
        </p>
    </div> 
 <?php endforeach; ?>


</form>
<?php elseif (count($pendingComments) == 0) : ?>
<h3>There are no pending user notes to manage, sorry... :(</h3>
<?php endif; ?>
<?php response_footer(); ?>
