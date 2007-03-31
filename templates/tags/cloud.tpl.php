<?php
response_header('PEAR :: Tag Cloud');
echo '<h1>PEAR Package Tag Cloud</h1>',
     '<style type="text/css">', $cloud['css'], '</style>',
     '<div class="tags">',$cloud['html'],'</div>';
response_footer();