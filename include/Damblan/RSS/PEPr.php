<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003 The PEAR Group                                    |
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

require_once "pear-database.php";
require_once "Damblan/RSS/Common.php";
require_once "pepr/pepr.php";

/**
 * Generates a RSS feed for the latest releases in a given category
 *
 * @author Martin Jansen <mj@php.net>
 * @package Damblan
 * @category RSS
 * @version $Revision$
 */
class Damblan_RSS_PEPr extends Damblan_RSS_Common {

    function Damblan_RSS_PEPr($value) {
        parent::Damblan_RSS_Common();
       
        global $proposalStatiMap, $dbh;

        $value = trim($value);

        if (isset($proposalStatiMap[$value])) {
            $this->setTitle("PEPr: Latest proposals with status " . $proposalStatiMap[$value]);
            $this->setDescription("The latest PEPr proposals with status " . $proposalStatiMap[$value]);
            $items = proposal::getAll($dbh, @$value, 10);
        } else if(substr($value, 0, 6) == 'search') {
            $searchString = substr($value, 7);
            $this->setTitle("PEPr: Latest proposals containing " . $searchString);
            $this->setDescription("The latest PEPr proposals containing " . $searchString);
            $items = proposal::search($searchString);
        } else {
            $this->setTitle("PEPr: Latest proposals.");
            $this->setDescription("The latest PEPr proposals.");
            $value = null;
            $items = proposal::getAll($dbh, @$value, 10);
        }

        
        
        foreach ($items as $id => $item) {
            $item = $item->toRSSArray();
            $this->addItem($this->newItem($item['title'], $item['link'], $item['desc'], $item['date']));
        }
    }
}

?>
