<?php
/**
 * Display information about a patch (specific revision)
 *  and lists all revisions
 */

$downurl = 'patch-download.php'
    . '?id=' . $bug_id
    . '&amp;patch=' . urlencode($patch)
    . '&amp;revision=' . urlencode($revision);
?>
<div class="bugheader">
<table class="details">
 <tbody>
  <tr>
   <th>Patch</th>
   <td><strong><?php echo $patch; ?></strong></td>
   <th rowspan="5">Revisions</th>
   <td rowspan="5">
    <ul class="revlist">
<?php
foreach ($revisions as $i => $rev) {
    $url = 'bug.php'
        . '?id=' . urlencode($bug)
        . '&edit=12'
        . '&patch=' . urlencode($patch)
        . '&revision=' . urlencode($rev[0]);
    $diffurl = '/bugs/bug.php'
        . '?patch=' . urlencode($patch)
        . '&id=' . $bug
        . '&edit=12'
        . '&diff=1&old=' . $rev[0]
        . '&revision=' . $revision;
    $same    = $rev[0] == $revision;
    $diffold = isset($diffoldrev) && $rev[0] == $diffoldrev;
    echo '<li'
        . ($same ? ' class="active"' : '')
        . ($diffold ? ' class="diffold"' : '')
        . '>';
    echo '<a href="' . htmlspecialchars($url) . '">'
        . format_date($rev[0])

        . '</a>';
    if (!$same && !$diffold) {
        echo ' <a href="' . htmlspecialchars($diffurl) . '">'
            . '[diff to current]'
            . '</a>';
    }
    echo '</li>';
}
?>
    </ul>
   </td>
  </tr>
  <tr>
   <th>Revision</th>
   <td><?php echo format_date($revision); ?></td>
  </tr>
  <tr>
   <th>Developer</th>
   <td>
    <a href="/user/<?php echo $handle; ?>"><?php echo $handle; ?></a>
   </td>
  </tr>
  <tr>
   <td>&nbsp;</td><td></td>
  </tr>
  <tr>
   <td></td>
   <td>
    <a href="<?php echo $downurl; ?>">Download patch</a>
   </td>
  </tr>
 </tbody>
</table>
</div>
<br/>

<?php
if (count($obsoletedby)) {
    echo '<div class="warnings">This patch is obsolete</div><p>Obsoleted by patches:<ul>';
    foreach ($obsoletedby as $betterpatch) {
        echo '<li><a href="/bugs/bug.php?edit=12&amp;patch=',
             urlencode($betterpatch['patch']),
             '&amp;id=', $bug, '&amp;revision=', $betterpatch['revision'],
             '">', htmlspecialchars($betterpatch['patch']), ', revision ',
             format_date($betterpatch['revision']), '</a></li>';
    }
    echo '</ul></p>';
}


if (count($obsoletes)) {
    echo '<div class="warnings">This patch renders other patches obsolete</div>',
         '<p>Obsolete patches:<ul>';
    foreach ($obsoletes as $betterpatch) {
        echo '<li><a href="/bugs/bug.php?edit=12&amp;patch=',
             urlencode($betterpatch['obsolete_patch']),
             '&amp;id=', $bug,
             '&amp;revision=', $betterpatch['obsolete_revision'],
             '">', htmlspecialchars($betterpatch['obsolete_patch']), ', revision ',
             format_date($betterpatch['obsolete_revision']), '</a></li>';
    }
    echo '</ul></p>';
}
?>

