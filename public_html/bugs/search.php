<?php /* vim: set noet ts=4 sw=4: : */
require_once './include/prepend.inc';
error_reporting(E_ALL ^ E_NOTICE);

if (isset($MAGIC_COOKIE) && !isset($user) && !isset($pw)) {
  list($user,$pw) = explode(":", base64_decode($MAGIC_COOKIE));
}

if ($search_for && !preg_match("/\\D/",trim($search_for))) {
    $x = $pw ? ($user ? "&edit=1" : "&edit=2") : "";
    header("Location: bug.php?id=$search_for$x");
    exit;
}

response_header("Search");

# the lol
echo "<style>"; include('./style.css'); echo "</style>";

$errors = array();
$warnings = array();

define('BOOLEAN_SEARCH', @intval($boolean));

if (isset($cmd) && $cmd == "display") {
    @mysql_connect("localhost","pear","pear")
        or die("Unable to connect to SQL server.");
    @mysql_select_db("pear");

    $mysql4 = version_compare(mysql_get_server_info(), "4.0.0", "ge");

    if (!$_GET['packge_name'] || !is_array($_GET['package_name'])) $_GET['package_name']  = array();
    if (!$_GET['package_nname']) $_GET['package_nname'] = array();

    if ($mysql4)
        $query = "SELECT SQL_CALC_FOUND_ROWS ";
    else
        $query = "SELECT ";

    $query .= "*, TO_DAYS(NOW())-TO_DAYS(ts2) AS unchanged FROM bugdb ";

    if (count($_GET['package_name']) == 0) {
        $where_clause = "WHERE package_name != 'Feature/Change Request'";
    } else {
        $where_clause = "WHERE package_name IN ('" . join("','", $_GET['package_name']) . "')";
    }

    if (count($_GET['package_nname']) > 0) {
        $where_clause.= " AND package_name NOT IN ('" . join("','", $_GET['package_nname']) . "')";
    }

    /* Treat assigned, analyzed, critical and verified bugs as open */
    if ($_GET['status'] == "Open") {
        $where_clause .= " AND (status='Open' OR status='Assigned' OR status='Analyzed' OR status='Critical' OR status='Verified')";
    } elseif ($_GET['status'] == "Old Feedback") {
        $where_clause .= " AND status='Feedback' AND TO_DAYS(NOW())-TO_DAYS(ts2)>60";
    } elseif ($_GET['status'] == "Fresh") {
        $where_clause .= " AND status != 'Closed' AND status != 'Duplicate' AND status != 'Bogus' AND TO_DAYS(NOW())-TO_DAYS(ts2) < 30";
    } elseif ($_GET['status'] == "Stale") {
        $where_clause .= " AND status != 'Closed' AND status != 'Duplicate' AND status != 'Bogus' AND TO_DAYS(NOW())-TO_DAYS(ts2) > 30";
    } elseif ($_GET['status'] && $_GET['status'] != "All") {
        $where_clause .= " AND status='$status'";
    }

    if (strlen($search_for)) {
        list($sql_search, $ignored) = format_search_string($search_for);
        $where_clause .= $sql_search;
        if (count($ignored) > 0 ) {
            array_push($warnings, "The following words were ignored: " . htmlentities(implode(', ', array_unique($ignored))));
        }
    }

    $bug_age = (int)$bug_age;
    if ($bug_age) {
        $where_clause .= " AND ts1 >= DATE_SUB(NOW(), INTERVAL $bug_age DAY)";
    }

    if ($php_os) {
        $where_clause .= " AND php_os like '%$php_os%'";
    }

    if (empty($phpver)) {
        $where_clause .= " AND (SUBSTRING(php_version,1,1) = '4' OR SUBSTRING(php_version,1,1) = '5' OR php_version = 'Irrelevant')";
    } else {
        // there's an index on php_version(1) to speed this up.
        if (strlen($phpver) == 1) {
            $where_clause .= " AND SUBSTRING(php_version,1,1) = '$phpver'";
        }
        else {
            $where_clause .= " AND php_version LIKE '$phpver%'";
        }
    }

    if (!empty($assign)) {
        $where_clause .= " AND assign = '$assign'";
    }

    if (!empty($author_email)) {
        $where_clause .= " AND bugdb.email = '$author_email' ";
    }

    $query .= "$where_clause ";

    $allowed_order = array("id", "package_name", "status", "php_version", "php_os", "sdesc", "assign");

    /* we avoid adding an order by clause if using the full text search */
    if ($order_by || $reorder_by || !strlen($search_for)) {
        if (!in_array($order_by,$allowed_order)) $order_by = "id";
        if (isset($reorder_by) && !in_array($reorder_by,$allowed_order)) $reorder_by = "id";
        if ($direction != "DESC") $direction = "ASC";

        if ($reorder_by) {
            if ($order_by == $reorder_by) {
                $direction = $direction == "ASC" ? "DESC" : "ASC";
            }
            else {
                $direction = "ASC";
            }
            $order_by = $reorder_by;
        }
        $query .= " ORDER BY $order_by $direction";
    }

    $begin = (int)$begin;
    if ($limit != 'All' && !(int)$limit) $limit = 30;

    if($limit!='All') $query .= " LIMIT $begin,".(int)$limit;

    if(stristr($query, ";")) {
        $errors[] = "<b>BAD HACKER!!</b> No database cracking for you today!";
    } else {

    $res = @mysql_query($query);
    if (!$res) die(htmlspecialchars($query)."<br>".mysql_error());

    $rows = mysql_num_rows($res);

    if ($mysql4)
        $total_rows = mysql_get_one("SELECT FOUND_ROWS()");
    else /* lame mysql 3 compatible attempt to allow browsing the search */
        $total_rows = $rows < 10 ? $rows : $begin + $rows + 10;

    if (!$rows) {
        show_bugs_menu($_GET['package_name'][0]);
        $errors[] = "No bugs were found.";
        display_errors($errors);
    }
    else {

        $package_name_string = '';
        if (count($_GET['package_name']) > 0) {
            foreach ($_GET['package_name'] as $type_str) {
                $package_name_string.= '&amp;package_name[]=' . urlencode($type_str);
            }
        }

        $package_nname_string = '';
        if (count($_GET['package_nname']) > 0) {
            foreach ($_GET['package_nname'] as $type_str) {
                $package_nname_string.= '&amp;package_nname[]=' . urlencode($type_str);
            }
        }

        $link = "$PHP_SELF?cmd=display" .
                $package_name_string   .
                $package_nname_string  .
                "&amp;status="     . urlencode(stripslashes($status)) .
                "&amp;search_for=" . urlencode(stripslashes($search_for)) .
                "&amp;php_os="     . urlencode(stripslashes($php_os)) .
                "&amp;boolean="    . BOOLEAN_SEARCH .
                "&amp;author_email=". urlencode(stripslashes($author_email)) .
                "&amp;bug_age=$bug_age&amp;by=$by&amp;order_by=$order_by&amp;direction=$direction&amp;phpver=$phpver&amp;limit=$limit&amp;assign=$assign";
        if (isset($_GET['package_name']) && count($_GET['package_name']) == 1) {
            show_bugs_menu($_GET['package_name'][0]);
        }
?>
<table align="center" border="0" cellspacing="2" width="95%">
<?php show_prev_next($begin,$rows,$total_rows,$link,$limit);?>
<tr bgcolor="#aaaaaa">
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=id">ID#</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=id">Date</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=package_name">Type</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=status">Status</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=php_version">Version</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=php_os">OS</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=sdesc">Summary</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=assign">Assigned</a></th>
</tr>
<?php
        if ($warnings) display_warnings($warnings);
        while ($row = mysql_fetch_array($res)) {
            echo '<tr valign="top" bgcolor="', get_row_color($row), '">';

            /* Bug ID */
            echo "<td align=\"center\"><a href=\"bug.php?id=$row[id]\">$row[id]</a>";
            echo "<br /><a href=\"bug.php?id=$row[id]&amp;edit=1\">(edit)</a></td>";

            /* Date */
            echo "<td align=\"center\">".date ("Y-m-d H:i:s", strtotime ($row[ts1]))."</td>";
            echo "<td>", htmlspecialchars($row[package_name]), "</td>";
            echo "<td>", htmlspecialchars($row[status]);
            if ($row[status] == "Feedback" && $row[unchanged] > 0) {
                printf ("<br>%d day%s", $row[unchanged], $row[unchanged] > 1 ? "s" : "");
            }
            echo "</td>";
            echo "<td>", htmlspecialchars($row[php_version]), "</td>";
            echo "<td>", $row[php_os] ? htmlspecialchars($row[php_os]) : "&nbsp;", "</td>";
            echo "<td>", $row[sdesc]  ? htmlspecialchars($row[sdesc]) : "&nbsp;",  "</td>";
            echo "<td>", $row[assign] ? htmlspecialchars($row[assign]) : "&nbsp;", "</td>";
            echo "</tr>\n";
        }

        show_prev_next($begin,$rows,$total_rows,$link,$limit);
?>
</table>
<?php
    }
        response_footer();
        exit;
}
}

if ($errors) display_errors($errors);
if ($warnings) display_warnings($warnings);
?>
<form id="asearch" method="get" action="<?php echo $PHP_SELF?>">
<table id="primary" width="95%">
<tr valign="top">
  <th>Find bugs</th>
  <td nowrap="nowrap">with all or any of the words</td>
  <td><input type="text" name="search_for" value="<?php echo htmlspecialchars(stripslashes($search_for));?>" size="20" maxlength="255" />
      <br><?php show_boolean_options(BOOLEAN_SEARCH) ?> (<?php print_link('http://bugs.php.net/search-howto.php', '?', true);?>)</td>
  <td rowspan="2">
   <select name="limit"><?php show_limit_options($limit);?></select>
   <br />
   <select name="order_by"><?php show_order_options($limit);?></select>
   <br />
   <input type="radio" name="direction" value="ASC" <?php if($direction != "DESC") { echo('checked="checked"'); }?>/>Ascending
   &nbsp;
   <input type="radio" name="direction" value="DESC" <?php if($direction == "DESC") { echo('checked="checked"'); }?>/>Descending
   <br />
   <input type="hidden" name="cmd" value="display" />
   <input type="submit" value="Search" />
  </td>
</tr>
<tr valign="top">
  <th>Status</th>
  <td nowrap="nowrap">Return only bugs with <b>status</b></td>
  <td><select name="status"><?php show_state_options($status);?></select></td>
</tr>
</table>
<table>
<tr valign="top">
  <th>Category</th>
  <td nowrap="nowrap">Return only bugs in <b>categories</b></td>
  <td><select name="package_name[]" multiple size=6><?php show_types($package_name,2);?></select></td>
</tr>
<tr valign="top">
  <th>&nbsp;</th>
  <td nowrap="nowrap">Return only bugs <b>NOT</b> in <b>categories</b></td>
  <td><select name="package_nname[]" multiple size=6><?php show_types($package_nname,2);?></select></td>
</tr>
<tr valign="top">
  <th>OS</th>
  <td nowrap="nowrap">Return bugs with <b>operating system</b></td>
  <td><input type="text" name="php_os" value="<?php echo htmlspecialchars(stripslashes($php_os));?>" /></td>
</tr>
<tr valign="top">
  <th>Version</th>
  <td nowrap="nowrap">Return bugs reported with <b>PHP version</b></td>
  <td><input type="text" name="phpver" value="<?php echo htmlspecialchars(stripslashes($phpver));?>" /></td>
</tr>
<tr valign="top">
  <th>Assigned</th>
  <td nowrap="nowrap">Return only bugs <b>assigned</b> to</td>
  <td><input type="text" name="assign" value="<?php echo htmlspecialchars(stripslashes($assign));?>" />
<?php
    if (!empty($user)) {
    $u = stripslashes($user);
        print "<input type=\"button\" value=\"set to $u\" onclick=\"form.assign.value='$u'\" />";
    }
?>
  </td>
</tr>
<tr valign="top">
  <th>Author email</th>
  <td nowrap="nowrap">Return only bugs with author email</td>
  <td><input type="text" name="author_email" value="<?php echo htmlspecialchars(stripslashes($author_email)); ?>" /></td>
</tr>
<tr valign="top">
  <th>Date</th>
  <td nowrap="nowrap">Return bugs submitted</td>
  <td><select name="bug_age"><?php show_byage_options($bug_age);?></select></td>
</tr>
</table>
</form>

<?php
response_footer();

function show_prev_next($begin,$rows,$total_rows,$link,$limit) {
    if($limit=='All') return;
    echo "<tr bgcolor=\"#cccccc\"><td align=\"center\" colspan=\"8\">";
    echo '<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr>';
    if ($begin > 0) {
        echo "<td align=\"left\" width=\"33%\"><a href=\"$link&amp;begin=",max(0,$begin-$limit),"\">&laquo; Show Previous $limit Entries</a></td>";
    }
    else {
        echo "<td width=\"33%\">&nbsp;</td>";
    }
    echo "<td align=\"center\" width=\"34%\">Showing ",$begin+1,"-", $begin+$rows, " of $total_rows</td>";
    if ($begin+$rows < $total_rows) {
        echo "<td align=\"right\" width=\"33%\"><a href=\"$link&amp;begin=",$begin+$limit,"\">Show Next $limit Entries &raquo;</a></td>";
    }
    else {
        echo "<td width=\"33%\">&nbsp;</td>";
    }
    echo "</tr></table></td></tr>";
}

function show_order_options ($current) {
    $opts = array(
        '' => "relevance",
        'id' => "ID",
        'package_name' => "package",
        'status' => "status",
        'php_version' => "version",
        'php_os' => "os",
        'sdesc' => "summary",
        'assign' => "assignment",
    );
    foreach ($opts as $k => $v) {
        echo '<option value="', $k, '"',
             ($v == $current ? ' selected="selected"' : ''),
             '>Sort by ', $v, "</option>\n";
    }
}