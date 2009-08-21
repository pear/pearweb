<pre>
<?php
if ($d->isEmpty()) {
    echo 'Patches are identical!';
} else {
    echo $diff->render($d);
}
?>
</pre>
