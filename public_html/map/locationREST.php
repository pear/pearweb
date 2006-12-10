<?php
/**
 * The Developers location's map system XML Built
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
 * @package   Maps
 * @author    David Coallier <davidc@php.net>
 * @copyright Copyright (c) 1997-2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

$sql = "SELECT name, latitude, longitude, homepage, handle
         FROM users WHERE latitude <> '' AND longitude <> ''";
$query = $dbh->getAll($sql, DB_FETCHMODE_ASSOC);
/**
 * Why use any other dependencies, we are just
 * not on php5 yet, so we can't use DOM or
 * xmlwriter. Let's just build custom XML
 */
$xml  = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
$xml .= '
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
         xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"
         xmlns:dc="http://purl.org/dc/elements/1.1/" 
         xmlns="http://xmlns.com/foaf/0.1/">
';


foreach ($query as $parts => $key) {
    $xml .= "\t<Person>\n";
    $xml .= "\t\t<name>{$key['name']}</name>\n";
    $xml .= "\t\t<homepage dc:title=\"{$key['handle']}\"\n";
    $xml .= "\t\t          rdf:resource=\"{$key['homepage']}\"/>\n";
    $xml .= "\t\t<based_near geo:lat=\"{$key['latitude']}\"\n";
    $xml .= "\t\t            geo:long=\"{$key['longitude']}\" />\n";
    $xml .= "\t</Person>\n";
}
$xml .= "</rdf:RDF>\n";

header ("Content-Type: application/xml");
print $xml;
?>
