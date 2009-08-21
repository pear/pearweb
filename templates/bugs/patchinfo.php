<?php
/**
 * Display information about a patch (specific revision)
 *  and lists all revisions
 */
?>
<h1>Patch version <?php echo format_date($revision) ?> for <?php echo clean($package) ?> Bug #<?php
    echo clean($bug) ?></h1>

<a href="bug.php?id=<?php echo urlencode($bug) ?>">
 Return to Bug #<?php echo clean($bug) ?>
</a>
| <a href="patch-display.php?bug_id=<?php echo urlencode($bug) ?>&patch=<?php echo urlencode($patch)
    ?>&revision=<?php echo urlencode($revision) ?>&download=1">
 Download this patch
</a><br />


<?php
if (count($obsoletedby)) {
    echo '<div class="warnings">This patch is obsolete</div><p>Obsoleted by patches:<ul>';
    foreach ($obsoletedby as $betterpatch) {
        echo '<li><a href="/bugs/patch-display.php?patch=',
             urlencode($betterpatch['patch']),
             '&bug_id=', $bug, '&revision=', $betterpatch['revision'],
             '">', htmlspecialchars($betterpatch['patch']), ', revision ',
             format_date($betterpatch['revision']), '</a></li>';
    }
    echo '</ul></p>';
}


if (count($obsoletes)) {
    echo '<div class="warnings">This patch renders other patches obsolete</div>',
         '<p>Obsolete patches:<ul>';
    foreach ($obsoletes as $betterpatch) {
        echo '<li><a href="/bugs/patch-display.php?patch=',
             urlencode($betterpatch['obsolete_patch']),
             '&bug_id=', $bug,
             '&revision=', $betterpatch['obsolete_revision'],
             '">', htmlspecialchars($betterpatch['obsolete_patch']), ', revision ',
             format_date($betterpatch['obsolete_revision']), '</a></li>';
    }
    echo '</ul></p>';
}
?>

Patch Revisions:
<?php
echo '<ul>';
foreach ($revisions as $i => $rev) {
    $url = 'patch-display.php'
        . '?bug_id=' . urlencode($bug)
        . '&patch=' . urlencode($patch)
        . '&revision=' . urlencode($rev[0]);
    $diffurl = '/bugs/patch-display.php?patch='
        . urlencode($patch)
        . '&bug_id=' . $bug
        . '&diff=1&old=' . $rev[0]
        . '&revision=' . $revision;
    $same = $rev[0] == $revision;
    $diffold = isset($diffoldrev) && $rev[0] == $diffoldrev;
    echo '<li>';
    echo '<a href="' . htmlspecialchars($url) . '">'
        . ($same ? '<strong>' : '')
        . ($diffold ? '<em>' : '')
        . format_date($rev[0])
        . ($diffold ? '</em>' : '')
        . ($same ? '</strong>' : '')
        . '</a>';
    if (!$same && !$diffold) {
        echo ' <a href="' . htmlspecialchars($diffurl) . '">'
            . '[diff to current]'
            . '</a>';
    }
    echo '</li>';
}
echo '</ul></li>';
?>
<h3>Developer: <a href="/user/<?php echo $handle ?>"><?php echo $handle ?></a></h3>

