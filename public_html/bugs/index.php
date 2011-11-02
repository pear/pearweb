<?php
require_once 'pear-database-package.php';

/**
 * The bug system home page
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
 * @package   Bugs
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

// Obtain common includes
require_once './include/prepend.inc';

response_header('Bugs');

$packages = package::listAllNames();

if (DEVBOX) {
    $host = '';
} else {
    $host = PEARWEB_PROTOCOL . PEAR_CHANNELNAME;
}

?>

<h1>PEAR Bug Tracking System</h1>
<div class="bug-box bug-box-first">
    <h2>Report New Bug</h2>
    <p>
    Got a test case or reproducible steps?
    </p>
    <?php if (!empty($packages)) { ?>
        <form method="get" action="<?php echo $host ?>/bugs/report.php">
            <select name="package">
                <option selected="selected" value="">Select package ...</option>
                <?php foreach ($packages as $id => $package) { ?>
                    <option value="<?php print $package; ?>"><?php print $package; ?></option>
                <?php } ?>
            </select><!--br/-->
            <input type="submit" name="action" value="Report Bug" class="bugs-go" />
        </form>
    <?php } else { ?>
        <p>0 packages found.</p>
    <?php } ?>

    <p class="bug-note">Read how to <?php echo make_link('http://bugs.php.net/how-to-report.php',
                  'get bugs fixed quickly', 'top'); ?>!</p>
</div>


<div class="bug-box">
    <h2>Enhancements</h2>
    <p>Got a patch or a great use-case?</p>
    <?php if (!empty($packages)) { ?>
        <form method="get" action="<?php echo $host ?>/bugs/report.php">
            <input type="hidden" name="bug_type" value="Feature/Change Request"/>
            <select name="package">
                <option selected="selected" value="">Select package ...</option>
                <?php foreach ($packages as $id => $package) { ?>
                    <option value="<?php print $package; ?>"><?php print $package; ?></option>
                <?php } ?>
            </select><!--br/-->
            <input type="submit" name="action" value="Add Enhancement" class="bugs-go" />
        </form>
    <?php } else { ?>
        <p>0 packages found.</p>
    <?php } ?>
</div>

<div class="bug-box bug-box-help">
    <h2>Get Help</h2>
    <p>Not sure if its a bug?</p>

    <p>Try some of our support channels,<br/>
     and don't forget to <a href="http://pastebin.com">use pastebin</a>.
    </p>
    <ul>
     <li>The <a href="/support/lists.php">pear-general mailing list</a></li>
     <li>The <a href="irc://efnet/#pear">#pear IRC channel</a> on EFnet</li>
     <li>The <?php echo make_link('/manual/', 'PEAR manual'); ?></li>
    </ul>

</div>
<div style="clear:both;"></div>
<h2 style="margin-top: 1.0em;">More Options</h2>
<!-- Shh -->
<ul style="-moz-column-count:2">
    <li><?php echo make_link('search.php', 'Search'); ?> existing bugs.</li>
    <li>See <?php echo make_link('stats.php', 'Bug Statistics'); ?></li>
    <li>Check the <?php echo make_link('http://www.nabble.com/Pear---General-f166.html', 'pear-general', 'top'); ?> and <?php echo make_link('http://www.nabble.com/Pear---Dev-f167.html', 'pear-dev', 'top'); ?> mailing list archives.</li>
    <li>Report a <?php print make_bug_link('pearweb', 'report', 'website'); ?> problem.</li>
    <li>Report a <?php print make_bug_link('Documentation', 'report', 'documentation'); ?> problem.</li>
</ul>

<?php
response_footer();
?>
