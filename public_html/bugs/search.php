<?php /* vim: set noet ts=4 sw=4: : */
require_once 'bugs/prepend.inc';
error_reporting(E_ALL ^ E_NOTICE);

if (isset($MAGIC_COOKIE) && !isset($user) && !isset($pw)) {
  list($user,$pw) = explode(":", base64_decode($MAGIC_COOKIE));
}

if ($search_for && !preg_match("/\\D/",trim($search_for))) {
	$x = $pw ? ($user ? '&edit=1' : '&edit=2') : "";
	header("Location: bug.php?id=$search_for$x");
	exit;
}

response_header('Search');

# the lol
echo '<style type="text/css">'; include('./style.css'); echo '</style>';

$errors = array();
$warnings = array();

define('BOOLEAN_SEARCH', @intval($boolean));

if (isset($cmd) && $cmd == 'display') {

	$mysql4 = version_compare(mysql_get_server_info(), '4.0.0', 'ge');

	if (!$package_name || !is_array($package_name)) $package_name = array();
	if (!$package_nname) $package_nname = array();

	if ($mysql4) {
        $query = 'SELECT SQL_CALC_FOUND_ROWS ';
	} else {
        $query = 'SELECT ';
    }

	$query .= '*, bugdb.id, TO_DAYS(NOW())-TO_DAYS(bugdb.ts2) AS unchanged FROM bugdb ';
    
    if (!empty($maintain)) {
        $query .= 'LEFT JOIN packages ON packages.name = bugdb.package_name
                   LEFT JOIN maintains ON packages.id = maintains.package ';
    }
    
    if (count($package_name) == 0) {
        $where_clause = "WHERE bugdb.package_name != ''";
    } else {
        $where_clause = "WHERE bugdb.package_name IN ('" . join("','", $package_name) . "')";
    }

	if (count($package_nname) > 0) {
		$where_clause.= " AND bugdb.package_name NOT IN ('" . join("','", $package_nname) . "')";
	}

	/* Treat assigned, analyzed, critical and verified bugs as open */
	if ($status == 'Open') {
		$where_clause .= " AND (bugdb.status='Open' OR bugdb.status='Assigned' OR bugdb.status='Analyzed' 
                            OR bugdb.status='Critical' OR bugdb.status='Verified')";
	} elseif ($status == 'Old Feedback') {
		$where_clause .= " AND bugdb.status='Feedback' AND TO_DAYS(NOW())-TO_DAYS(bugdb.ts2)>60";
	} elseif ($status == 'Fresh') {
		$where_clause .= " AND bugdb.status != 'Closed' AND bugdb.status != 'Duplicate' 
                            AND bugdb.status != 'Bogus' AND TO_DAYS(NOW())-TO_DAYS(bugdb.ts2) < 30";
	} elseif ($status == 'Stale') {
		$where_clause .= " AND bugdb.status != 'Closed' AND bugdb.status != 'Duplicate' 
                            AND bugdb.status != 'Bogus' AND TO_DAYS(NOW())-TO_DAYS(bugdb.ts2) > 30";
	} elseif ($status && $status != 'All') {
		$where_clause .= " AND bugdb.status='$status'";
	}

	if (strlen($search_for)) {
		list($sql_search, $ignored) = format_search_string($search_for);
		$where_clause .= $sql_search;
		if (count($ignored) > 0 ) {
			array_push($warnings, 'The following words were ignored: ' . htmlentities(implode(', ', array_unique($ignored))));
		}
	}

	$bug_age = (int)$bug_age;
	if ($bug_age) {
		$where_clause .= " AND bugdb.ts1 >= DATE_SUB(NOW(), INTERVAL $bug_age DAY)";
	}

	if ($php_os) {
		$where_clause .= " AND bugdb.php_os like '%$php_os%'";
	}

	if (empty($phpver)) {
		$where_clause .= " AND (SUBSTRING(bugdb.php_version,1,1) = '4' OR SUBSTRING(bugdb.php_version,1,1) = '5' OR bugdb.php_version = 'Irrelevant')";
	} else {
		// there's an index on php_version(1) to speed this up.
		if (strlen($phpver) == 1) {
			$where_clause .= " AND SUBSTRING(bugdb.php_version,1,1) = '$phpver'";
		}
		else {
			$where_clause .= " AND bugdb.php_version LIKE '$phpver%'";
		}
	}

	if (!empty($assign)) {
	    $where_clause .= " AND bugdb.assign = '$assign'";
	}

	if (!empty($maintain)) {
	    $where_clause .= " AND maintains.handle = '$maintain'";
	}

	if (!empty($author_email)) {
	    $where_clause .= " AND bugdb.email = '$author_email' ";
	}

    $query .= "$where_clause ";

	$allowed_order = array('bugdb.id', 'bugdb.package_name', 'bugdb.status', 
                            'bugdb.php_version', 'bugdb.php_os', 'bugdb.sdesc', 'bugdb.assign');

	/* we avoid adding an order by clause if using the full text search */
    if ($order_by || $reorder_by || !strlen($search_for)) {
		if (!in_array($order_by,$allowed_order)) $order_by = "bugdb.id";
		if (isset($reorder_by) && !in_array($reorder_by,$allowed_order)) $reorder_by = "bugdb.id";
		if ($direction != 'DESC') $direction = 'ASC';

		if ($reorder_by) {
			if ($order_by == $reorder_by) {
				$direction = $direction == 'ASC' ? 'DESC' : 'ASC';
			} else {
				$direction = 'ASC';
			}
			$order_by = $reorder_by;
		}
		$query .= " ORDER BY $order_by $direction";
    }

	$begin = (int)$begin;
	if ($limit != 'All' && !(int)$limit) $limit = 30;

	if ($limit!='All') $query .= " LIMIT $begin,".(int)$limit;

	if (stristr($query, ';')) {
        $errors[] = '<b>BAD HACKER!!</b> No database cracking for you today!';
	} else {

	$res = $dbh->query($query);
    if (DB::isError($res)) {
        die ($res->getDebugInfo());
    }
	$rows = $res->numRows();

	if ($mysql4) {
		$total_rows = mysql_get_one('SELECT FOUND_ROWS()');
    } else { /* lame mysql 3 compatible attempt to allow browsing the search */
		$total_rows = $rows < 10 ? $rows : $begin + $rows + 10;
    }

	if (!$rows) {
        show_bugs_menu($package_name[0]);
        $errors[] = 'No bugs were found.';
        display_errors($errors);
	} else {
		$package_name_string = '';
		if (count($package_name) > 0) {
			foreach ($package_name as $name_str) {
				$package_name_string .= '&amp;package_name[]=' . urlencode($name_str);
			}
		}

		$package_nname_string = '';
		if (count($package_nname) > 0) {
			foreach ($package_nname as $name_str) {
				$package_nname_string .= '&amp;package_nname[]=' . urlencode($name_str);
			}
		}

		$link = $_SERVER['PHP_SELF'] . "?cmd=display" .
				$package_name_string   .
				$package_nname_string  .
				"&amp;status="     . urlencode(stripslashes($status)) .
				"&amp;search_for=" . urlencode(stripslashes($search_for)) .
				"&amp;php_os="     . urlencode(stripslashes($php_os)) .
				"&amp;boolean="    . BOOLEAN_SEARCH .
				"&amp;author_email=". urlencode(stripslashes($author_email)) .
				"&amp;bug_age=$bug_age&amp;by=$by&amp;order_by=$order_by&amp;direction=$direction&amp;phpver=$phpver&amp;limit=$limit&amp;assign=$assign";
        if (isset($package_name) && count($package_name) == 1) {
            show_bugs_menu($package_name[0]);
        }
?>
<table align="center" cellspacing="2" style="width: 95%; border: 0px;">
 <?php show_prev_next($begin,$rows,$total_rows,$link,$limit);?>
 <tr style="background-color: #AAAAAA;">
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=id">ID#</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=id">Date</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=package">Package</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=status">Status</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=php_version">Version</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=php_os">OS</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=sdesc">Summary</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=assign">Assigned</a></th>
 </tr>
<?php
		if ($warnings) display_warnings($warnings);
		while ($row = $res->fetchRow()) {
			echo '<tr valign="top" bgcolor="', get_row_color($row), '">';

			/* Bug ID */
			echo '<td align="center"><a href="bug.php?id='.$row['id'].'">'.$row['id'].'</a>';
			echo '<br /><a href="bug.php?id='.$row['id'].'&amp;edit=1">(edit)</a></td>';

			/* Date */
			echo '<td align="center">'.date ('Y-m-d H:i:s', strtotime ($row['ts1'])).'</td>';
			echo '<td>', htmlspecialchars($row['package_name']), '</td>';
			echo '<td>', htmlspecialchars($row['status']);
			if ($row['status'] == 'Feedback' && $row['unchanged'] > 0) {
				printf ("<br />%d day%s", $row['unchanged'], $row['unchanged'] > 1 ? 's' : '');
			}
			echo '</td>';
			echo '<td>', htmlspecialchars($row['php_version']), '</td>';
			echo '<td>', $row['php_os'] ? htmlspecialchars($row['php_os']) : '&nbsp;', '</td>';
            $row['bug_type'] != 'Bug' ? $summary = '[FCR] ' : $summary = '';
			echo '<td>', $row['sdesc']  ? clean($summary.$row['sdesc'])  : '&nbsp;', '</td>';
			echo '<td>', $row['assign'] ? htmlspecialchars($row['assign']) : '&nbsp;', '</td>';
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
   <input type="radio" name="direction" value="ASC" <?php if($direction != 'DESC') { echo('checked="checked"'); }?> />Ascending
   &nbsp;
   <input type="radio" name="direction" value="DESC" <?php if($direction == 'DESC') { echo('checked="checked"'); }?> />Descending
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
  <th>Maintainer</th>
  <td nowrap="nowrap">Return only bugs in packages <b>maintained</b> by</td>
  <td><input type="text" name="maintain" value="<?php echo htmlspecialchars(stripslashes($maintain));?>" />
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
	echo '<tr style="background-color: #CCCCCC;"><td align="center" colspan="8">';
    echo '<table cellspacing="0" cellpadding="0" style="width: 100%; border: 0px;"><tr>';
	if ($begin > 0) {
		echo "<td align=\"left\" width=\"33%\">
                <a href=\"$link&amp;begin=",max(0,$begin-$limit),"\">&laquo; Show Previous $limit Entries</a>
              </td>";
	} else {
        echo '<td style="width: 33%;">&nbsp;</td>';
    }
    echo "<td align=\"center\" width=\"34%\">Showing ",$begin+1,"-", $begin+$rows, " of $total_rows</td>";
	if ($begin+$rows < $total_rows) {
		echo "<td align=\"right\" width=\"33%\">
                <a href=\"$link&amp;begin=",$begin+$limit,"\">Show Next $limit Entries &raquo;</a>
             </td>";
	}
    else {
        echo '<td style="width: 33%;">&nbsp;</td>';
    }
	echo '</tr></table></td></tr>';
}

function show_order_options ($current) {
	$opts = array(
		''             => 'relevance',
		'id'           => 'ID',
		'package_name' => 'package',
		'status'       => 'status',
		'php_version'  => 'version',
		'php_os'       => 'os',
		'sdesc'        => 'summary',
		'assign'       => 'assignment',
	);
	foreach ($opts as $k => $v) {
		echo '<option value="', $k, '"',
		     ($v == $current ? ' selected="selected"' : ''),
		     '>Sort by ', $v, "</option>\n";
	}
}
