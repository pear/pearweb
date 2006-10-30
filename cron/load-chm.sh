#!/bin/sh

cd /var/lib/pear/chm/

/usr/bin/env curl -s "http://pear.markwiesemann.eu/manual/pear_manual_{de,en,fr}.chm" -o "pear_manual_#1.chm.NEW"

for LANG in de en fr;
do
    mv "pear_manual_${LANG}.chm.NEW" "pear_manual_${LANG}.chm"
    chmod 0644 "pear_manual_${LANG}.chm"
done
