<?php
/**
 * Generate DOAP data for a package
 * (Description of a Project)
 *
 * @link http://trac.usefulinc.com/doap
 */
if (!isset($pkg) || !is_array($pkg)) {
    //called directly?
    //since we are supposed to be included from package-info.php,
    //there should be a $pkg
    header('HTTP/1.0 400 Bad Request');
    exit();
}

$x = simplexml_load_string(<<<XML
<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF xml:lang="en"
 xmlns="http://usefulinc.com/ns/doap#" 
 xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
 xmlns:foaf="http://xmlns.com/foaf/0.1/"
>
 <Project/>
</rdf:RDF>
XML
);
$url = 'http://pear.php.net/package/' . $pkg['name'];
$p = $x->Project;
$p['rdf:about'] = $url;
$p->name = $pkg['name'];
$p->homepage['rdf:resource'] = $url;
//$p->shortdesc = $pkg['shortdesc'];

header('Content-Type: application/rdf+xml');
echo $x->asXML();
//var_dump($pkg);
?>