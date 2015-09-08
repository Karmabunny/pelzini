#!/bin/bash
#
# Copyright 2008 Josh Heidenreich
#
# This file is part of Pelzini.
# 
# Pelzini is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# Pelzini is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with Pelzini.  If not, see <http://www.gnu.org/licenses/>.
# 


#
# This tool creates a snapshot of the Pelzini repository
#

if [ ! -d dist_snapshot ]; then
  mkdir dist_snapshot
fi
cd dist_snapshot

echo "Checking out repository..."
svn co https://docu.svn.sourceforge.net/svnroot/docu/trunk pelzini_snapshot

REV=`svnversion pelzini_snapshot`
DATE=`date +"%Y-%m-%d"`
DESTFILENAME="pelzini_snapshot_r$REV-$DATE.zip"

echo "Compressing snapshot, saving as $DESTFILENAME..."
zip -rq "$DESTFILENAME" pelzini_snapshot
