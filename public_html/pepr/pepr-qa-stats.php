<?php
/**
 * Displays a list of propably orphan proposals
 *
 * The <var>$proposalStatiMap</var> array is defined in
 * pearweb/include/pepr/pepr.php.
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
 * @package   PEPr
 * @author    Tobias Schlitt <toby@php.net>
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

/**
 * Obtain the common functions and classes.
 */
require_once 'pepr/pepr.php';

function getDays($date) {
    return ceil((time() - $date) / 60 / 60 / 24);
}

// {{{ SQL for orphan drafts

$sql['orphan_drafts'] = <<<EOS
SELECT 
    p.id AS id,
    p.pkg_name AS pkg_name,
    p.user_handle AS user_handle,
    UNIX_TIMESTAMP(p.draft_date) AS draft_date
FROM 
    package_proposals AS p
WHERE 
    p.status = "draft" 
    AND p.draft_date < DATE_ADD(NOW(), INTERVAL -30 DAY)
ORDER BY draft_date DESC
EOS;

// }}}
// {{{ SQL for orphan proposals

$sql['orphan_proposals'] = <<<EOS
SELECT 
    p.id AS id,
    p.pkg_name AS pkg_name,
    p.user_handle AS user_handle,
    UNIX_TIMESTAMP(p.draft_date) AS draft_date,
    UNIX_TIMESTAMP(p.proposal_date) as proposal_date,
    MAX(pcl.timestamp) as latest_change,
    MAX(pcm.timestamp) as latest_comment
FROM 
    package_proposals AS p,
    package_proposal_changelog AS pcl,
    package_proposal_comments AS pcm 
WHERE 
    p.id = pcl.pkg_prop_id 
    AND p.id = pcm.pkg_prop_id 
    AND p.status = "proposal" 
    AND p.proposal_date < DATE_ADD(NOW(), INTERVAL -30 DAY)
    AND pcl.timestamp < DATE_ADD(NOW(), INTERVAL -30 DAY)
    AND pcm.timestamp < DATE_ADD(NOW(), INTERVAL -30 DAY)
GROUP BY pcl.pkg_prop_id, pcm.pkg_prop_id
ORDER BY proposal_date DESC
EOS;

// }}}
// {{{ Fetch results

$res = array();
foreach ($sql as $name => $sqlSt) {
    $res[$name] = $dbh->getAll($sqlSt, DB_FETCHMODE_ASSOC);
}

// }}} 

response_header('PEPr :: Propably orphan proposals');

// {{{ HTML for orphan drafts
echo '<h1>Status &quot;draft&quot;</h1>';
echo '<table border="0" cellspacing="0">';
echo '<tr>';
echo '<th>Name</th>';
echo '<th>Draft-Date</th>';
echo '<th>Proposer</th>';
echo '</tr>';
$i = 0;
foreach ($res['orphan_proposals'] as $set) {
    echo '<tr style='.(($i++ % 2 == 0) ? '"background-color: #CCCCCC;"' : '').'>';
    echo '<td class="textcell"><a href="/pepr/pepr-proposal-show.php?id='.$set['id'].'">'.$set['pkg_name'].'</a></td>';
    echo '<td class="textcell">'.getDays($set['draft_date']).' days ago<br />('.make_utc_date($set['draft_date']).')</td>';
    echo '<td class="textcell">'.user_link($set['user_handle']).'</td>';
    echo '</tr>';
}
echo '</table>';
// }}}
// {{{ HTML for orphan proposals
echo '<h1>Status &quot;proposal&quot;</h1>';
echo '<table border="0" cellspacing="0">';
echo '<tr>';
echo '<th>Name</th>';
echo '<th>Draft-Date</th>';
echo '<th>Proposal-Date</th>';
echo '<th>Last change</th>';
echo '<th>Last comment</th>';
echo '<th>Proposer</th>';
echo '</tr>';
$i = 0;
foreach ($res['orphan_proposals'] as $set) {
    echo '<tr style='.(($i++ % 2 == 0) ? '"background-color: #CCCCCC;"' : '').'>';
    echo '<td class="textcell"><a href="/pepr/pepr-proposal-show.php?id='.$set['id'].'">'.$set['pkg_name'].'</a></td>';
    echo '<td class="textcell">'.getDays($set['draft_date']).' days ago<br />('.make_utc_date($set['draft_date']).')</td>';
    echo '<td class="textcell">'.getDays($set['proposal_date']).' days ago<br /> ('.make_utc_date($set['proposal_date']).')</td>';
    echo '<td class="textcell">'.getDays($set['latest_change']).' days ago<br /> ('.make_utc_date($set['latest_change']).')</td>';
    echo '<td class="textcell">'.getDays($set['latest_comment']).' days ago<br /> (<a href="/pepr-comment-show.php?id='.$set['id'].'">'.make_utc_date($set['latest_comment']).'</a>)</td>';
    echo '<td class="textcell">'.user_link($set['user_handle']).'</td>';
    
    echo '</tr>';
}
echo '</table>';
// }}}

