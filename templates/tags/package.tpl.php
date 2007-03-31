<?php response_header('PEAR :: Tag :: ' . $tag); ?>
<h1>Packages tagged with <?php echo $tag ?></h1>
<table>
 <tr>
  <th>Package</th><th>Category</th><th>Description</th>
 </tr>
<?php
foreach ($packages as $package) {
    echo '<tr><td><a href="/package/',$package['name'],'">',
        $package['name'],'</a></td><td><a href="/packages.php?catpid=',
        $package['catid'],'">',$package['category'],
        '</a></td><td>',htmlspecialchars($package['summary']),'</td></tr>';
}
echo '</table>';
response_footer();
