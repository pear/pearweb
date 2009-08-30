<?php
/**
 *  A quick and dirty RSS -> Twitter adapter for PEAR. Takes the 
 *  http://pear.php.net/feeds/latest.rss feed and pushed it out to
 *  to http://twitter.com/pear
 *  
 *  PHP Version 5.2.0+
 *  
 *  <code>
 *  php rss-to-twitter.php twitter_username twitter_password;
 *  </code>
 *  
 *  @category  pearweb
 *  @package   pearweb
 *  @author    Bill Shupp <hostmaster@shupp.org> 
 *  @copyright 2009 Bill Shupp
 *  @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 *  @link      http://pear.php.net
 */
require_once 'HTTP/Request2.php';
require_once 'XML/Feed/Parser.php';
require_once 'Cache/Lite.php';
require_once 'Services/Twitter.php';

if (!isset($argv[2])) {
    echo "usage: php " . __FILE__ . " <twitter_username> <twitter_password>\n";
    exit(1);
}

$cacheDir = '/tmp/twitterrss/';
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}
$cache = new Cache_Lite(array('cacheDir'             => $cacheDir,
                              'lifeTime'             => null,
                              'hashedDirectoryLevel' => 2));

$httpRequest = new HTTP_Request2('http://pear.php.net/feeds/latest.rss');
$response    = $httpRequest->send();
$rss         = new XML_Feed_Parser($response->getBody());
$twitter     = new Services_Twitter($argv[1], $argv[2]);

$all = array();
foreach ($rss as $feed) {
    $all[$feed->title] = $feed->link;
}

// Reverse so that we tweet the oldest first
$reversed = array_reverse($all, true);

$exclamations = array('Cool!',
                      'Awesome!',
                      'Great scott!',
                      'Sweet!',
                      'Great horny toads!');

foreach ($reversed as $title => $link) {
    $key = md5($link);
    if ($cache->get($key) === false) {
        $exclamation = $exclamations[rand(0, (count($exclamations) - 1))];
        $status      = "$exclamation $title was just released! $link";
        $twitter->statuses->update($status);
        $cache->save($title, $key);
    }
}

?>
