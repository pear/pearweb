<?php response_header('UserNote'); ?>
<h1>Note addition</h1>
    <div>
    Note added, it will be reviewed soon and you will be contacted if there's any problem.
    Go back to the previous url <a href="http://pear.php.net<?php echo htmlspecialchars($redirect) ?>" 
                                   title="Previous Note">http://pear.php.net<?php echo htmlspecialchars($redirect) ?></a>
    </div>
<?php 

response_footer(); 

?>
