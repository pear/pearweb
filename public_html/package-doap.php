<?php
/**
 * Generate DOAP data for a package
 * (Description of a Project)
 *
 * PHP version 5
 *
 * @category PEAR Website
 * @package  pearweb
 * @author   Christian Weiske <cweiske@php.net>
 * @license  http://www.php.net/license PHP License
 * @link     http://trac.usefulinc.com/doap
 */
if (!isset($pkg) || !is_array($pkg)) {
    //called directly?
    //since we are supposed to be included from package-info.php,
    //there should be a $pkg
    header('HTTP/1.0 400 Bad Request');
    exit();
}

require_once 'pear-database-maintainer.php';
require_once 'pear-database-package.php';

$x = simplexml_load_string(<<<XML
<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF xml:lang="en"
 xmlns="http://usefulinc.com/ns/doap#" 
 xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
 xmlns:foaf="http://xmlns.com/foaf/0.1/"
 xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"
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
//we have no "created" data in our database. use first release
end($pkg['releases']);
$p->created = reset(
    explode(
        ' ',
        $pkg['releases'][key($pkg['releases'])]['releasedate']
    )
);
$p->shortdesc = $pkg['summary'];
$p->shortdesc['xml:lang'] = 'en';

$p->description = $pkg['description'];
$p->description['xml:lang'] = 'en';

$p->{'mailing-list'}[0]['rdf:resource'] = 'http://pear.php.net/support/lists.php';
$p->{'mailing-list'}[1]['rdf:resource'] = 'http://news.php.net/php.pear.general';
$p->{'mailing-list'}[2]['rdf:resource'] = 'http://news.php.net/php.pear.dev';

$p->{'download-page'}['rdf:resource'] = $url . '/download';

/*
 * DOAP: helper, tester, translator, documenter, developer, maintainer
 * PEAR: helper, contributor, developer, lead
 */
$maintainers = maintainer::getDetailled($pkg['packageid']);

$rolemap = array(
    'helper'      => 'helper',
    'contributor' => 'helper',
    'developer'   => 'developer',
    'lead'        => 'maintainer',
);
$n = 0;
foreach ($maintainers as $nick => $maint) {
    $role = $rolemap[$maint['role']];
    $p->{$role}[$n]->{'foaf:Person'}->{'foaf:nick'} = $nick;
    $p->{$role}[$n]->{'foaf:Person'}->{'foaf:name'} = $maint['name'];
    $p->{$role}[$n]->{'foaf:Person'}->{'foaf:homepage'}['rdf:resource']
        = $maint['homepage'];
    $p->{$role}[$n]->{'foaf:Person'}->{'foaf:mbox_sha1sum'}
        = sha1('mailto:' . $maint['email']);

    if ($maint['longitude'] != '') {
        $p->{$role}[$n]->{'foaf:Person'}->{'foaf:based_near'}
            ->{'geo:Point'}['geo:lat'] = $maint['latitude'];
        $p->{$role}[$n]->{'foaf:Person'}->{'foaf:based_near'}
            ->{'geo:Point'}['geo:long'] = $maint['longitude'];
    }

    ++$n;
}

//category
$p->category['rdf:resource'] = 'http://pear.php.net/packages.php'
    . '?catpid=' . $pkg['categoryid'] . '&catname=' . $pkg['category'];

//latest release
$latest  = reset($pkg['releases']);
$version = key($pkg['releases']);
$p->release->Version->name     = $latest['state'];
$p->release->Version->created  = reset(explode(' ', $latest['releasedate']));
$p->release->Version->revision = $version;

$p->license['rdf:resource'] = package::get_license_link($pkg['license'], true);

$p->{'bug-database'}['rdf:resource'] = $url . '/bugs';

header('Content-Type: application/rdf+xml');
echo $x->asXML();

?>