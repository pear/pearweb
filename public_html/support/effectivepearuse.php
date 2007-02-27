<?php response_header('PEAR Support :: Developing Effectively with PEAR packages'); ?>
<h1>Developing Effectively with PEAR Packages</h1>
<h5>by Justin Patrin, edited by Gregory Beaver</h5>
<h2>Introduction</h2>
<p>
 This document is the result of years of collected helpful hints that everyone
 using PEAR packages should know about.  Users who come on IRC often need to know
 this information, and so now this knowledge is available for all to learn from.
</p>

<h2>Error Handling</h2>

<p>
Most PEAR packages will return errors from a function call. These errors take the form of <a href="http://pear.php.net/manual/en/core.pear.pear-error.php">PEAR_Error</a> objects. The correct way to handle these is:
</p>

<div class="explain">
<?php
highlight_string('
<?php
require_once \'PEAR/DB.php\';
$db = DB::connect($dsn);
if (PEAR::isError($db)) {
    //This is an example of what you can do when an error happens. You could also log the error or try to recover from it.
    die($db->getMessage() . \' \' . print_r($db->getUserInfo(), true));
}
?>')
?>
</div>

<p>
This should be done for all calls which are documented to return an error. If you don't check for an error return you will get error messages such as <code>Fatal error: Call to undefined function: PEAR_Error::fetchRow(). in /usr/share/php5/MDB2.php on line 1921.</code>
</p>

<p>
Another way to handle errors in PEAR is to use a global error handler. A simple example is below. This will die on all PEAR_Errors and show the reason for the error:
</p>

<div class="explain">
<?php
highlight_string('
<?php
function handle_pear_error($e) {
    die($e->getMessage() . \' \' . print_r($e->getUserInfo(), true));
}
require_once \'PEAR.php\';
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, \'handle_pear_error\');
?>') ?>
</div>

<p>
You can also get a backtrace from the error object so that you can see where the error came from. This is especially helpful when you're using a global error handler and you can't tell where the error is coming from.
</p>

<?php
highlight_string('
<?php
function handle_pear_error($e) {
    echo \'Backtrace:
\';
    foreach ($e->getBacktrace() as $l) {
        echo \'File: \' . $l[\'file\'] . \' Line: \' . $l[\'line\'] .
             \' Class: \' . $l[\'class\'] . \' Function: \' . $l[\'function\'] . \'
\';
    }
    die($e->getMessage() . \' \' . print_r($e->getUserInfo(), true));
}
require_once \'PEAR.php\';
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, \'handle_pear_error\');
?>') ?>

<p>
The first line output will be from PEAR_Error and can usually be ignored. It is the next line that tells you where the error was raised.
</p>

<div class="explain">
<pre>
Backtrace:
File: /usr/lib/php/PEAR.php Line: 572 Class: PEAR_Error Function: PEAR_Error
File: /home/papercrane/public_html/test.php Line: 13 Class: PEAR Function: raiseError
Some Error
</pre>
</div>

<p>
Newer PEAR packages which are written for PHP5 use <a href="http://pear.php.net/manual/en/core.pear.pear-exception.php">PEAR_Exception</a> instead of PEAR_Error. See the <a href="http://pear.php.net/pepr/pepr-proposal-show.php?id=132">Error Handling Guidelines for PHP5 packages RFC</a>, <a href="http://wiki.ciaweb.net/yawiki/index.php?area=PEAR_Dev&page=RfcExceptionUse">the wiki page it is based on</a>, and the <a href="http://www.php.net/exceptions">PHP documentation</a> for how to handle these. Be careful with your input and output
</p>

<h2>Security Concerns</h2>

<p>
Handling the superglobals such as <code>$_POST</code>, <code>$_GET</code>, and <code>$_REQUEST</code> can be tricky and leave you open to Injection and XSS attacks as well as cause annoying problems such as multiplying backslashes.
</p>

<p>
It is always best to let a well tested package, such as <a href="http://pear.php.net/HTML_QuickForm">HTML_QuickForm</a>, <a href="http://pear.php.net/MDB2">MDB2</a>, or <a href="http://pear.php.net/DB">DB</a> handle these values for you.
</p>

<p>
For input and output of form values, use HTML_QuickForm. It will automatically quote your values so as to stop XSS and will also make sure that magic_quotes_gpc isn't corrupting your values.
</p>

<?php
highlight_string('
<?php
$value = \'inject">XX<input name="password" type="hidden" value="h4cked\';
require_once \'HTML/QuickForm.php\';
$form = new HTML_QuickForm();
$form->addElement(\'password\', \'password\', \'Enter your password\');
$form->setDefaults($value);
if ($form->validate()) {
    echo \'Password entered: \' . htmlentities($form->exportValue(\'password\'));
}
$form->display();') ?>

<p>
If you had simply output <code>$value</code> without passing it through HTML_QuickForm you would have had injected HTML in your form. If you happened to have <pre>magic_quotes_gpc</pre> turned on (you should never have this on) then the value output would have had extra backslashes before any quotes passed in. If htmlentities() hadn't been run before outputting the value then any HTML entered would have been injected into your page.
</p>

<p>
Then when you want to insert into your database: With MDB2:
</p>

<?php
highlight_string('
<?php
$mdb2 = MDB2::connect($dsn);
$sth = $mdb2->query(\'SELECT * FROM table WHERE col = \' .
    $mdb2->quote($value, \'string\'));

// With DB:

$db = DB::connect($dsn);
$sth = $db->query(\'SELECT * FROM table WHERE col = \' .
    $db->quoteSmart($value));
?>') ?>