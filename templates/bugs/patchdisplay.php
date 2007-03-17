<?php response_header('Patch :: ' . clean($package) . ' :: Bug #' . clean($bug)); ?>
<h1>Patch version <?php echo date('Y-m-d H:i:s', $revision) ?> for <?php echo clean($package) ?> Bug #<?php
    echo clean($bug) ?></h1>
<a href="bug.php?id=<?php echo urlencode($bug) ?>">Return to Bug #<?php echo clean($bug) ?></a> 
| <a href="patch-display.php?bug=<?php echo urlencode($bug) ?>&patch=<?php echo urlencode($patch)
    ?>&revision=<?php echo urlencode($revision) ?>&download=1">Download this patch</a><br />
Patch Revisions:
<?php foreach ($revisions as $i => $revision): ?>
<a href="patch-display.php?bug=<?php echo urlencode($bug) ?>&patch=<?php
    echo urlencode($patch) ?>&revision=<?php echo urlencode($revision[0]) ?>"><?php
    echo date('Y-m-d H:i:s', $revision[0]) ?></a><?php if ($i < count($revisions) - 1) echo ' | '; ?>
<?php endforeach; //foreach ($revisions as $i => $revision) ?>
<h3>Developer: <a href="/user/<?php echo $handle ?>"><?php echo $handle ?></a></h3>
<pre>
<?php echo htmlentities($patchcontents, ENT_QUOTES, 'UTF-8'); ?>
</pre>
