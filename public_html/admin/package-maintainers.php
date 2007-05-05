<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Pierre-Alain Joye <pajoye@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

auth_require();


$pid = isset($_GET['pid']) ? (int)$_GET['pid'] : false;

if ($pid && $pid < 1) {
   report_error('Invalid package');
}

include_once 'pear-database-package.php';
$package_name = package::info($pid, 'name');

response_header('Administration - ' . htmlspecialchars($package_name) . ' - Package Maintainers');

include_once 'pear-database-maintainer.php';
$maintainers = maintainer::get($pid);

// Maintainer being lead can go further, if not QA and up
if (!(isset($maintainers[$auth_user->handle]) && $maintainers[$auth_user->handle]['role'] == 'lead')) {
   auth_require('pear.qa');
}

if (isset($_POST) && isset($_POST['role'])) {

   // Got a new maintainer?
   if (isset($_POST['handle']['new']) && !empty($_POST['handle']['new'])) {

      $new = strip_tags($_POST['handle']['new']);
      include_once 'pear-database-user.php';
      if (!ereg('^[0-9a-z_]{2,20}$', $new)) {
         report_error('Invalid handle: ' . $new);
      } elseif (!user::exists($new)) {
         report_error($new . ' does not exist.');
      } else {
         $role = $_POST['role']['new'];

         if (!maintainer::isValidRole($role)) {
            report_error('Invalid role.');
         } else {
            if (maintainer::add($pid, $new, $role)) {
               $message = 'Maintainer ' .  $new . 'sucessfully added.';
               $maintainers[$new] = array('role'=>$role, 'active' => 1);
            }
         }
      }
   } else {
       $new     = '';
   }

   // Role, active, and marked for removal
   $roles   = $_POST['role'];
   $active  = $_POST['active'];

   if (isset($_POST['delete'])) {
      $delete  = $_POST['delete'];
   } else {
      $delete = array();
   }

   $updates = array();
   $update  = 0;

   foreach ($maintainers as $handle => $info) {
      if (isset($delete[$handle]) && $delete[$handle]) {
         maintainer::remove($pid, $handle);
         unset($maintainers[$handle]);
         continue;
      }

      if (isset($roles[$handle]) && $info['role'] != $roles[$handle]) {
         $update = 1;
         $update_role = $roles[$handle];
      } else {
         $update_role = $info['role'];
      }

      if (isset($active[$handle])) {
         $update_active = 1;
         $update = 1;
      } elseif ($info['active'] == 1 && $handle != $new) {
         $update_active = 0;
         $update = 1;
      }

      // Do not add again the newly added maintainer to the list
      if ($update == 1 && $handle != $new) {
         maintainer::update($pid, $handle, $update_role, $update_active);
         $maintainers[$handle]['role'] = $update_role;
         $maintainers[$handle]['active'] = $update_active;
      }

      $update = 0;
   }
}

include_once 'PEAR/Common.php';
$roles = PEAR_Common::getUserRoles();

?>
<h1>Package Information: <?php echo $package_name; ?></h1>
<?php
print_package_navigation($pid, $package_name, '/admin/package-maintainers.php?pid=' . $pid);
?>
<form name="maintainers_edit" method="post" action="?pid=<?php echo $pid; ?>">
<table class="form-holder" style="margin-bottom: 2em;" cellspacing="1" border="0">
<caption class="form-caption">Edit Maintainers list</caption>
<thead class="form-label_left">
   <th class="form-label_left">Handle</th><th class="form-label_left">Role</th><th class="form-label_left">Active</th><th class="form-label_left">Delete</th>
</thead>
<tbody>
<?php

foreach ($maintainers as $handle => $infos) {
   $select = '<select name="role[' . $handle . ']">';
   foreach($roles as $role) {
      $select .= '<option value="' . $role. '"' . ($role == $infos['role'] ? 'selected' : '') . '>' . $role . '</option>';
   }
   $select .= '</select>';
   $active_checkbox = '<input type="checkbox" value="1" name="active[' . $handle . ']" '. ($infos['active'] == 1 ? 'checked' : '' ) . '>';
?>
   <tr>
      <td><?php echo $handle; ?></td>
      <td><?php echo $select; ?></td><td><?php echo $active_checkbox; ?></td>
      <td><input type="checkbox" name="delete[<?php echo $handle; ?>]" value="1"></td>
   </tr>
<?php
}
?>
   <tr><td colspan="3"><b>Add a maintainer</b></tr>
   <tr>
      <td><input type="text" name="handle[new]" value="" /></td>
      <td><select name="role[new]">
      <?php foreach ($roles as $role) {
         echo '<option value=' . $role . '>' . $role . '</role>';
      }
      ?>
      </select>
      </td>
      <td>X</td>
   </tr>
</tbody>
</table>
<input type="submit" name="Save" value="Save">
</form>

<?php
response_footer();
