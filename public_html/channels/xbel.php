<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2005 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.0 of the PHP license,       |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/3_0.txt.                                  |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id: index.php 310104 2011-04-09 15:38:46Z clockwerx $
*/
require_once 'pear-database-channel.php';

$channels = channel::listActive();

header('Content-type: application/xbel+xml');
echo '<?xml version="1.0"?>' . "\n";
?>
<xbel version="1.0">

<?php foreach ($channels as $channel) { ?>
  <bookmark href="<?php print $channel['project_link']; ?>">
    <title><?php print htmlentities($channel['name']); ?></title>
    <desc><?php print htmlentities($channel['project_label']); ?></desc>
  </bookmark>
<?php } ?>
</xbel>
