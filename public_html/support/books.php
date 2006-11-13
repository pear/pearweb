<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2006 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Author: Martin Jansen <mj@php.net>                                   |
   +----------------------------------------------------------------------+
   $Id$
*/

response_header("Support - Books");

echo "<h1>Support</h1>";

include 'tabs_list.php';

$books = array(
               array("title" => "The PEAR Installer Manifesto",
                     "authors" => array("Gregory Beaver"),
                     "pearuser" => array("cellog"),
                     "isbn" => "1904811191",
                     "url" => "http://www.packtpub.com/PEAR-Installer/book",
                     "publisher" => "Packt Publishing"
                     ),
               array("title" => "PHP Programming with PEAR",
                     "authors" => array("Stoyan Stefanov", "Stephan Schmidt", "Aaron Wormus", "Carsten Lucke"),
                     "pearuser" => array("stoyan", "schst", "wormus", "luckec"),
                     "isbn" => "1904811795",
                     "url" => "http://www.packtpub.com/pear/book",
                     "publisher" => "Packt Publishing"
                     ),
               array("title" => "PHP PEAR &mdash; Anwendung und Entwicklung - Erweiterungen für PHP schreiben",
                     "authors" => array("Carsten M&ouml;hrke"),
                     "pearuser" => array(),
                     "isbn" => "3898425800",
                     "url" => "http://www.galileocomputing.de/katalog/buecher/titel/gp/titelID-891",
                     "publisher" => "Galileo Computing"
                     ),
               array("title" => "Foundations of PEAR: Rapid PHP Development",
                     "authors" => array("Nathan A. Good", "Allan Kent"),
                     "pearuser" => array(),
                     "isbn" => "1590597397",
                     "url" => "http://apress.com/book/bookDisplay.html?bID=10181",
                     "publisher" => "Apress"
                     )
               );
               
?>

<h2>&raquo; <a name="books" id="books">Books</a></h2>

<p>The following is a list of books that have been written about PEAR.
There are a lot more PHP books that describe some PEAR packages.
You can search for them at <a href="http://www.amazon.com/exec/obidos/external-search?mode=books&keyword=PHP">
Amazon.com</a>.</p>

<table class="form-holder" cellpadding="5" cellspacing="1">
<?php
foreach ($books as $book) {
    echo "<tr>\n";
    echo "  <td rowspan=\"4\" class=\"form-input\">\n";
    if (isset($book['image'])) {
        echo "    <img src=\"/gifs/books/" . $book['image'] . ".gif\" width=\"50\" height=\"100\" />\n";
    } else {
        echo "    <img src=\"/gifs/blank.gif\" width=\"50\" height=\"100\" />\n";
    }
    echo "</td>\n";
    echo "  <td colspan=\"2\" valign=\"top\" class=\"form-input\">\n";
    echo "    <strong><a href=\"" . $book['url'] . "\">\n";
    echo "      " . $book['title'];
    echo "    </a></strong>\n";
    echo "  </td>\n";
    echo "</tr>\n";
    echo "<tr>\n";
    echo "  <td class=\"form-input\">Written by:</td>\n";

    $authors = array();
    for ($i = 0; $i < count($book['authors']); $i++) {
        if (!empty($book['pearuser'][$i])) {
            $authors[] = "    <a href=\"/user/" . $book['pearuser'][$i] . "\">" . $book['authors'][$i] . "</a>";
        } else {
            $authors[] = $book['authors'][$i];
        }
    }

    $size = count($authors);
    if ($size >= 2) {
        echo "  <td class=\"form-input\">" . join(array_splice($authors, 0, $size - 1), ", ") . " and " . $authors[0] . "</td>\n";;
    } else {
        echo "  <td class=\"form-input\">" . $authors[0] . "</td>\n";
    }

    echo "</tr>\n";
    echo "<tr>\n";
    echo "  <td class=\"form-input\">Publisher:</td>\n";
    echo "  <td class=\"form-input\">" . $book['publisher'] . "</td>\n";
    echo "</tr>\n";
    echo "<tr>\n";
    echo "  <td class=\"form-input\">ISBN:</td>\n";
    echo "  <td class=\"form-input\">" . $book['isbn'] . "</td>\n";
    echo "</tr>\n";
    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
}
?>
</table>

<p>Are you an author or publisher of one of the above books?  No image?
Let <a href="mailto:<?php echo PEAR_WEBMASTER_EMAIL; ?>">us know</a>
if we are allowed to use an image of your book&#39;s cover page
here.  Is your book missing?  Then tell us about it, too.</p>

<p><a href="/support/">&laquo; Back to the Support overview</a></p>

<?php
response_footer();
?>
