<pre>
<?php if ($d->isEmpty()) echo 'Diffs are identical!'; else {
    echo $diff->render($d);
}
?>
</pre>
