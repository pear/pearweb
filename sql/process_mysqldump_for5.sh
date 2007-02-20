#!/bin/sh
sed -e 's/release/\`release\`/' pearall.sql > pearall2.sql
sed -e 's/ release)/ \`release\`\)/' pearall2.sql > pearall3.sql
sed -e 's/release\([)]\)/ \`release\`\1/' pearall3.sql > pearall4.sql
sed -e 's/release\([,]\)/ \`release\`\1/' pearall4.sql > pearall5.sql
mysql -u root pear < pearall5.sql