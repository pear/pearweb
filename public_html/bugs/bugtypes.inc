<?php
$list = package::listAll();
$items = array();
$items['Web Site']      = 'Web Site';
$items['PEPr']          = '&nbsp;&nbsp;&nbsp;PEPr';
$items['Documentation'] = 'Documentation';
$items['Bug System']    = 'Bug System';
foreach ($list as $name => $data) {
    $items[$name] = $name;
}
// Extra items should be added to
// include/functions.inc:get_bugtype_mail()
//

?>
