<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

if (!isset($_POST['search_in']) || !isset($_POST['search_string'])) {
    error_handler('Please use the search system via the search form above.',
                  'Search');
}

switch ($_POST['search_in']) {
	case 'packages':
		localRedirect('/package-search.php?pkg_name='
                      . urlencode($_POST['search_string'])
                                  . '&bool=AND&submit=Search#results');
		break;

    case 'developers':
        // XXX: Enable searching for names instead of handles
        localRedirect('/user/' . urlencode($_POST['search_string']));
        break;

    case 'pear-dev':
    case 'pear-cvs':
    case 'pear-general':
        header('Location: http://marc.theaimsgroup.com/?'
               . 'l=' . $_POST['search_in'] . '&w=2&r=1&q=b&s='
               . urlencode($_POST['search_string']));
        break;

    case 'site':
        header('Location: http://google.com/search?as_sitesearch=' . PEAR_CHANNELNAME
               . '&as_q=' . urlencode($_POST['search_string']));
        break;

case 'pepr':
        header('Location: /pepr/pepr-overview.php?search='
               . urlencode($_POST['search_string']));
        break;

    default:
        error_handler('Invalid search target.', 'Search');
}

?>
