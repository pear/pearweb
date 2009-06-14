<?php /* vim: set noet ts=4 sw=4: : */

/* Generates an RSS/RDF feed for a particular bug specified as the "id"
 * parameter.  optionally, if "format" is "xml", generates data in a
 * non-standard xml format.
 *
 * Contributed by Sara Golemon <pollita@php.net>
 * ported from php-bugs-web by Gregory Beaver <cellog@php.net>
 */

require_once dirname(dirname(__FILE__)) . '/include/functions.inc';

$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'rss';

$query  = "SELECT id,package_name,bug_type,email,sdesc,ldesc,php_version,
                  php_os,status,ts1,ts2,assign,package_version,handle,
                  UNIX_TIMESTAMP(ts1) as ts1a, UNIX_TIMESTAMP(ts2) as ts2a
                  FROM bugdb
                  WHERE id = ?
                  AND registered = 1";

$res = $dbh->getAll($query, array($id), DB_FETCHMODE_ASSOC);

if (count($res)) {
    $bug = $res[0];
}
if (!$res || !$bug) {
	header('HTTP/1.0 404 Not Found');
	die('Nothing found');
}

$query = 'SELECT 
			c.ts,
			comment,
			IF(c.handle <> "",u.registered,1) as registered,
			u.showemail,
			u.handle,
			c.handle as bughandle,
			UNIX_TIMESTAMP(ts) as added
    FROM bugdb_comments c
    LEFT JOIN users u ON u.handle = c.handle
    WHERE c.bug = ?
    ORDER BY c.ts DESC';
$comments = $GLOBALS['dbh']->getAll($query, array($bug['id']), DB_FETCHMODE_ASSOC);

if ($format == 'xml') {
    header('Content-type: text/xml; charset=utf-8');

	include dirname(__FILE__) . '/xml.php';
	exit;
} else {
    header('Content-type: application/rdf+xml; charset=utf-8');

	$uri = "http://" . PEAR_CHANNELNAME . "/bugs/{$bug['id']}";
	include dirname(__FILE__) . '/rdf.php';
	exit;
}


