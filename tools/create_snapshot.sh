#!/bin/bash

#
# This tool creates a snapshot of the docu repository
#

echo "Checking out repository..."
svn co https://docu.svn.sourceforge.net/svnroot/docu/trunk docu-snapshot

REV=`svnversion docu-snapshot`
DATE=`date +"%Y-%m-%d"`
DESTFILENAME="docu-snapshot-r$REV-$DATE.tar.bz2"

echo "Compressing snapshot, saving as $DESTFILENAME..."
tar -cj docu-snapshot > "$DESTFILENAME"
