<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2005 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Author: Martin Jansen <mj@php.net>                                   |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once 'Damblan/Search.php';

/**
 * Site-wide search class
 *
 * @author Martin Jansen <mj@php.net>
 * @package Damblan
 * @version $Revision$
 * @extends Damblan_Search
 */
class Damblan_Search_Site extends Damblan_Search
{
    function search($term)
    {
        header('Location: http://search.yahoo.com/search?vs=' . PEAR_CHANNELNAME
               . '&va=' . urlencode($term));
        exit();
    }
}
