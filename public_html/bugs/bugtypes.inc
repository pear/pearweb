<?php
$list = package::listAll(true);
$items = array();
$items['Web Site']      = 'Web Site';
$items['Bug System']    = '&nbsp;&nbsp;&nbsp;Bug System';
$items['PEPr']          = '&nbsp;&nbsp;&nbsp;PEPr';
$items['Documentation'] = 'Documentation';
foreach ($list as $name => $data) {
    $items[$name] = $name;
}
// Extra items should be added to
// include/functions.inc:get_bugtype_mail()
//

?>
