#!/bin/bash
#
# Copyright 2008 Josh Heidenreich
#
# This file is part of docu.
# 
# Docu is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# Docu is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with docu.  If not, see <http://www.gnu.org/licenses/>.
# 


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
