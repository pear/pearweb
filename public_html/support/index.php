<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2004 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

response_header("Support");
?>

<h1>Support</h1>

<?php
// Todo: Move me to a shared place!
$items = array(
               'Overview' => array('url'   => 'index.php',
                                   'title' => 'Support Overview'
                                   ),
               'Mailing Lists' => array('url'   => 'lists.php',
                                        'title' => 'PEAR Mailing Lists'
                                        ),
               'Tutorials' => array('url'   => 'tutorials.php',
                                    'title' => 'Tutorials about PEAR (packages)'
                                    ),
               'Presentation Slides' => array('url'   => 'slides.php',
                                              'title' => 'Slides of presentations about PEAR'
                                              ),
               'Icons' => array('url'   => 'icons.php',
                                'title' => 'PEAR icons'
                                )
               );

print_tabbed_navigation($items);
?>

<h2>&raquo; Overview</h2>

<p>Besides the <a href="/manual/">manual</a> the PEAR website provides
a number of other support resources. You can select them using the
navigation tabs above.</p>

<p>Additionaly one can ask for help on the <i>#pear</i> 
<acronym title="Internet Relay Chat">IRC</acronym> channel at the 
<a href="http://www.efnet.org"> Eris Free Net</a>.  For german-speaking 
PEAR users  <a href="http://www.pear-forum.de/">PEAR.forum</a> provides 
web discussion boards, where questions concerning PEAR can be discussed.
</p>

<p>Some project members also write about PEAR in their weblogs or
journals. Some of them are aggregated through 
<a href="http://planet-php.net/">Planet PHP</a>.</p>

<p>Zend Technologies, PHP Magazine and the weblog <q>of Pears and Pickles</q>
are publishing roundups of the events on the PEAR and PECL mailing lists:</p>

<ul>
  <li><a href="http://zend.com/zend/pear/">Zend PEAR/PECL Weekly Summaries</a></li>
  <li><a href="http://php-mag.net/itr/psecom,id,207,nodeid,207.html">PHPBarnstormer</a> by PHP Magazine</li>
  <li>Weblog <q><a href="http://php.eckspee.com/">of Pears and Pickles</a></q></li>
</ul>

<p>If you have questions concering this website, you can contact
<a href="mailto:pear-webmaster@lists.php.net">pear-webmaster@lists.php.net</a>.
</p>

<?php
response_footer();
?>
