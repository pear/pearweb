<?php

/**
 * PEAR PWN URI
 */
define('PEAR_SITE','http://' . PEAR_CHANNELNAME);

/**
 * PEAR PWN URI
 */
define('PEAR_SITE_PWN','http://' . PEAR_CHANNELNAME . '/weeklynews.php#');

/**
 * PEAR Weekly news HTML files
 */
define('PEAR_PWN_PATH',dirname(__FILE__) . '/../weeklynews');
/**
 * RSS directory (http available)
 */
define('PEAR_RSS_PATH',dirname(__FILE__) . '/../public_html/rss');

/**
* DSN for pear packages database
*/
define('DSN','mysql://pear:pear@localhost/pear');

/**
 * RSS head&channel
 */
$head = '<?xml version="1.0" encoding="{encoding}"?>
<rdf:RDF
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns="http://purl.org/rss/1.0/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
>
<image>
    <title>PEAR</title>
    <url>http://' . PEAR_CHANNELNAME . '/gifs/pear-icon.gif</url>
    <link>http://' . PEAR_CHANNELNAME . '</link>
    <width>32</width>
    <height>32</height>
</image>
<channel rdf:about="http://' . PEAR_CHANNELNAME . '/">
    <title>' . PEAR_CHANNEL_SUMMARY . '</title>
    <link>http://' . PEAR_CHANNELNAME . '/</link>
    <description>' . PEAR_CHANNEL_SUMMARY . '</description>
    <pubDate>{pub_date}</pubDate>
    <items>
        <rdf:Seq>
{rdfsequences}
        </rdf:Seq>
    </items>
</channel>
<!-- RSS-Items -->
';

/**
 * rdf sequence template
 */
$rdfseq = '             <rdf:li rdf:resource="{link}" />
';

/**
 * rdf item template
 */
$item = '<item rdf:about="{link_about}">
    <title>{title}</title>
    <link>{link}</link>
    <description>{description}</description>
    <dc:date>{date}</dc:date>
</item>
';

/**
 * rdf footer
 */
$footer='<!-- / RSS-Items PEAR/RSS -->
</rdf:RDF>';

function show_latest($lang) {
    $dow =  date ("w");
    $start =  mktime (0,0,0,date("m")  ,date("d")-$dow,date("Y"));

    for ($i=0;$i <8;$i++ ) {
        $week = mktime (0,0,0,date("m",$start)  ,date("d",$start)-($i*7),date("Y",$start));
		$date = date("Ymd",$week);
		$file = PEAR_PWN_PATH.'/'.$date. ".{$lang}.html";
        if (@file_exists($file) ){
            return array($date,$file);
        }
    }
	return false;
}

$lang_maps = array(
    "en"    => "en_US",
    "de"    => "de",
    "fr"    => "fr",
    "pt_BR" => "pt_BR",
    "pl"    => "pl",
    "es"    => "es"
);

$iso_maps = array(
    "en"        => "iso-8859-1",
    "de"        => "iso-8859-1",
    "fr"        => "iso-8859-1",
    "pt_BR"     => "iso-8859-1",
    "pl"        => "iso-8859-2",
    "es"        => "iso-8859-1"
);


?>