<?php

/**
 * Interface for deleting a proposal.
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
 * @author    Daniel Convissor <danielc@php.net>
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

/**
 * Obtain the common functions and classes.
 */
require_once 'pepr/pepr.php';

auth_require('pear.pepr');

if (!empty($_GET['isDeleted'])) {
    response_header('PEPr :: Delete');
    echo "<h1>Delete Proposal</h1>\n";
    report_success('Proposal deleted successfully.');
    echo '<p>';
    print_link('/pepr/pepr-overview.php', 'Back to PEPr Home Page');
    echo "</p>\n";
    response_footer();
    exit;
}

if (!$proposal =& proposal::get($dbh, @$_GET['id'])) {
    response_header('PEPr :: Delete :: Invalid Request');
    echo "<h1>Delete Proposal</h1>\n";
    report_error('The requested proposal does not exist.');
    response_footer();
    exit;
}

ob_start();

response_header('PEPr :: Delete :: ' . htmlspecialchars($proposal->pkg_name));
echo '<h1>Delete Proposal &quot;' . htmlspecialchars($proposal->pkg_name) . "&quot;</h1>\n";

if (!$proposal->mayEdit($_COOKIE['PEAR_USER'])) {
    report_error('You are not allowed to delete this proposal,'
                 . ' probably due to it having reached the "'
                 . $proposal->getStatus(true) . '" phase.'
                 . ' If this MUST be deleted, contact someone ELSE'
                 . ' who has pear.pepr.admin karma.');
    response_footer();
    exit;
}

if ($proposal->compareStatus('>', 'proposal')) {
    $karma =& new Damblan_Karma($dbh);
    if ($karma->has($_COOKIE['PEAR_USER'], 'pear.pepr.admin')) {
        report_error('This proposal has reached the "'
                     . $proposal->getStatus(true) . '" phase.'
                     . ' Are you SURE you want to delete it?',
                     'warnings', 'WARNING:');
    }
}

include_once 'HTML/QuickForm.php';
$form =& new HTML_QuickForm('delete-proposal', 'post',
                            'pepr-proposal-delete.php?id=' . $proposal->id);

$form->addElement('checkbox', 'delete', 'Really delete proposal for ',
                  htmlspecialchars($proposal->pkg_category) . '::'
                  . htmlspecialchars($proposal->pkg_name));
$form->addElement('submit', 'submit', 'Do it');

$form->addRule('delete', 'You have to check the box to delete!', 'required',
               '', 'client');

if (isset($_POST['submit'])) {
    if ($form->validate()) {
        $proposal->delete($dbh);
        $proposal->sendActionEmail('proposal_delete', 'mixed',
                                   $_COOKIE['PEAR_USER']);
        ob_end_clean();
        localRedirect('pepr-proposal-delete.php?id=' . $proposal->id . '&isDeleted=1');
    } else {
        $pepr_form = $form->toArray();
        report_error($pepr_form['errors']);
    }
}

ob_end_flush();
display_pepr_nav($proposal);

$form->display();

response_footer();

?>
