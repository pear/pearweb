<?php

/**
 * The Developers location's map system
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  pearweb
 * @package   Maps
 * @author    David Coallier <davidc@php.net>
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */
$map = '
<script type="text/javascript" language="javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAjPqDvnoTwt1l2d9kE7aeSRSaX3uuPis-gsi6PocQln0mfq-TehSSt5OZ9q0OyzKSOAfNu8NuLlNgWA"></script>
';
response_header('PEAR Maps', false, $map);

?>

<h1>PEAR Developer's locations!</h1>
<?php
$maps = array(
    'world' =>
        array('name'  => 'World Map',
              'link'  => 'http://pear.cweiske.de/devmaps/peardev-world.jpg',
              'thumb' => 'http://pear.cweiske.de/devmaps/peardev-world.200.jpg'
        ),

    'northamerica' =>
        array('name'  => 'North America',
              'link'  => 'http://pear.cweiske.de/devmaps/peardev-northamerica.jpg',
              'thumb' => 'http://pear.cweiske.de/devmaps/peardev-northamerica.200.jpg',
        ),

    'southamerica' =>
        array('name'  => 'South America',
              'link'  => 'http://pear.cweiske.de/devmaps/peardev-southamerica.jpg',
              'thumb' => 'http://pear.cweiske.de/devmaps/peardev-southamerica.200.jpg',
        ),

    'europe' => 
        array('name'  => 'Europe',
              'link'  => 'http://pear.cweiske.de/devmaps/peardev-europe.jpg',
              'thumb' => 'http://pear.cweiske.de/devmaps/peardev-europe.200.jpg',
        ),

    'mideast' =>
        array('name'  => 'Middle East',
              'link'  => 'http://pear.cweiske.de/devmaps/peardev-mideast.jpg',
              'thumb' => 'http://pear.cweiske.de/devmaps/peardev-mideast.200.jpg',
        ),

    'asia' =>
        array('name'  => 'Asia',
              'link'  => 'http://pear.cweiske.de/devmaps/peardev-asia.jpg',
              'thumb' => 'http://pear.cweiske.de/devmaps/peardev-asia.200.jpg',
        ),

    'australia' =>
        array('name'  => 'Australia',
              'link'  => 'http://pear.cweiske.de/devmaps/peardev-australia.jpg',
              'thumb' => 'http://pear.cweiske.de/devmaps/peardev-australia.200.jpg',
        ),
);
?>
<p>
 This map contains the location of the PEAR developers that were kind enough
 to put their location or close to their location up there. If you need support
 just visit the <?php print_link('/support/', 'support page'); ?>.
 <br /></p>
 <p>Now that you are ready to proceed:</p>
 <br />
 <noscript>
  <?php
    print '<h1>Maps Links</h1>';
    foreach ($maps as $map) {
        echo '<a href="' . $map['link'] . '">'
            .'<img src="' . $map['thumb'] . '" alt="' . $map['name'] . '" width="200px"/>'
            .'</a>' . "\r\n";
    }
    print '<hr noshade="noshade"/>';
  ?>
 </noscript>

 <script language="javascript" type="text/javascript">

 points = new Array();
 
 <?php
    $sql = "
    SELECT latitude, longitude, handle, homepage, IF(showemail,email,'') as email
     FROM users
      WHERE latitude <> ''
      AND longitude  <> ''
    ";

    $infos = $dbh->getAll($sql);
    foreach ($infos as $info) {
        echo "points.push([
                          '{$info[0]}',
                          '{$info[1]}',
                          '{$info[2]}',
                          '{$info[3]}',
                          '{$info[4]}']);\n";

    }
 ?>
 
</script>
<script language="javascript" type="text/javascript" src="../javascript/peardev_map.js"></script>

<div style="width: 80%; height: 300px; border: none;" 
     id="peardev_map">
</div>
<?php
$showMap = '
<script language="javascript" type="text/javascript">
tmp = document.getElementById(\'peardev_map\');
tmp.style.border = \'1px solid black\';
showfullmap();

</script>
';

response_footer(false, $showMap);

?>
