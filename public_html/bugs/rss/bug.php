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
                  php_os,status,ts1,ts2,assign,package_version,handle FROM bugdb WHERE id=?
                  AND registered=1";

$res = $dbh->getAll($query, array($id), DB_FETCHMODE_ASSOC);

if (count($res)) {
    $bug = $res[0];
}
if (!$res || !$bug) {
    die('Nothing found');
	outputHeader(array(), $format);
	outputFooter($format);
	exit;
}

outputHeader($bug, $format);

$query  = "SELECT handle,email,comment,UNIX_TIMESTAMP(ts) as added"
		. " FROM bugdb_comments WHERE bug=? ORDER BY ts DESC";
$res = $dbh->getAll($query, array($id), DB_FETCHMODE_ASSOC);
if ($res) {
    outputbug($bug, $res, $format);
}

outputFooter($format);

function outputHeader($bug,$format) {
	header('Content-type: text/xml; charset=utf-8');
	switch ($format) {
		case 'xml':
			echo "<pearbug>\n";  
			foreach($bug as $key => $value)
				echo "  <$key>" . htmlspecialchars($value) . "</$key>\n";
			break;
		case 'rss':
		default:
            $query = 'SELECT c.ts, IF(c.handle <> "",u.registered,1) as registered,
                u.showemail, u.handle,c.handle as bughandle
                FROM bugdb_comments c
                LEFT JOIN users u ON u.handle = c.handle
                WHERE c.bug = ?
                ORDER BY c.ts';
            $res = $GLOBALS['dbh']->getAll($query, array($bug['id']));
			echo '<?xml version="1.0" encoding="UTF-8"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/" xmlns:dc="http://purl.org/dc/elements/1.1/">';
			echo "\n    <channel rdf:about=\"http://pear.php.net/bugs/{$bug['id']}\">\n";
			echo '    <link>http://pear.php.net/bugs/' . intval($bug['id']) . "</link>\n";
            echo "    <dc:creator>pear-webmaster@lists.php.net</dc:creator>\n";
            echo "    <dc:publisher>pear-webmaster@lists.php.net</dc:publisher>\n";
			echo "    <dc:language>en-us</dc:language>\n";
			echo "    <items>\n";
			echo "     <rdf:Seq>\n";
			foreach ($res as $comment) {
			    $comment = urlencode($comment[0]);
    			echo "      <rdf:li rdf:resource=\"http://pear.php.net/bugs/" .
    			     intval($bug['id']) . "#$comment\"/>\n";
			}
			echo "     </rdf:Seq>\n";
			echo "    </items>\n";
			echo '    <title>' . utf8_encode(htmlspecialchars("[{$bug['status']}] {$bug['sdesc']}")) . "</title>\n";
			echo '    <description>';
			echo utf8_encode(htmlspecialchars("{$bug['package_name']} "));
			echo utf8_encode(htmlspecialchars("{$bug['bug_type']}\nReported by "));
			if ($bug['handle']) {
    			echo utf8_encode(htmlspecialchars("{$bug['handle']}\n"));
			} else {
			    echo utf8_encode(htmlspecialchars(substr($bug['email'], 0, strpos($bug['email'], '@')))) . "@...\n";
			}
			echo date('Y-m-d\TH:i:s-05:00', strtotime($bug['ts1'])) . "\n";
			echo utf8_encode(htmlspecialchars("PHP: {$bug['php_version']} OS: {$bug['php_os']} Package Version: {$bug['package_version']}\n\n"));
			echo utf8_encode(htmlspecialchars($bug['ldesc']));
			echo "    </description>\n";
			echo "  </channel>\n";
	}
}

function outputbug($bug, $res, $format) {
	foreach ($res as $row) {
		switch ($format) {
			case 'xml':
				echo "  <comment>\n";
				foreach ($row as $key => $value)
					echo "    <$key>" . htmlspecialchars($value) . "</$key>\n";
				echo "  </comment>\n";
				break;
			case 'rss':
			default:
			    $ts = urlencode($bug['ts2']);
				echo "    <item rdf:about=\"http://pear.php.net/bugs/" .
				     $bug['id'] . "#$ts\">\n";
				echo '      <title>';
				if ($row['handle']) {
				    echo utf8_encode(htmlspecialchars($row['handle'])) . "</title>\n";
				} else {
				    echo utf8_encode(htmlspecialchars(substr($row['email'], 0, strpos($row['email'], '@')))) . "@... [$bug[ts2]]</title>\n";
				}
				echo "      <link>http://pear.php.net/bugs/{$bug['id']}#$ts</link>\n";
				echo '      <description>' . utf8_encode(htmlspecialchars($row['comment'])) . "</description>\n";
				echo '      <dc:date>' . date('Y-m-d\TH:i:s-05:00', $row['added']) . "</dc:date>\n";
				echo "    </item>\n";
		}
	}
}


function outputFooter($format) {
	switch ($format) {
		case 'xml':
			echo "</pearbug>\n";
			break;
		case 'rss':
		default:
			echo "</rdf:RDF>";
	}
}
?>
