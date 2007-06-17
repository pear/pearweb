<?php response_header('Upload New Release');
?><h1>Upload New Release</h1>
<?php
if ($success) {
    if (is_array($info)) {
        report_success('Version ' . $info['version'] . ' of '
                       . $info['package'] . ' has been successfully released, '
                       . 'and its promotion cycle has started.');
        echo '<p><a href="/package/', $info['package'], '">Visit package home</a>';
    } else {
        report_success('Version ' . $info->getVersion() . ' of '
                       . $info->getPackage() . ' has been successfully released, '
                       . 'and its promotion cycle has started.');
    }
    echo '</p></div>';
} else {
    report_error($errors);
}
?>
<p>
Upload a new package distribution file built using &quot;<code>pear
package</code>&quot; here.  The information from your package.xml file will
be displayed on the next screen for verification. The maximum file size
is 16 MB.
</p>

<p>
Uploading new releases is restricted to each package's lead developer(s).
</p><form action="release-upload.php" method="post" enctype="multipart/form-data" >
<table class="form-holder" cellspacing="1">
 <caption class="form-caption">
  Upload
 </caption>
 <tr>
  <th class="form-label_left"><label for="f" accesskey="i">D<span class="accesskey">i</span>stribution File</label></th>
  <td class="form-input">
   <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo HTML_FORM_MAX_FILE_SIZE ?>" />
   <input type="file" name="distfile" size="40" id="f"/>
  </td>
 </tr>
 <tr>
  <th class="form-label_left">&nbsp;</th>
  <td class="form-input">
   <input type="submit" name="upload" value="Upload!" />
  </td>
 </tr>
</table>
<input type="hidden" name="_fields" value="distfile:upload" />
</form>

