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


if [ ! -d dist_release ]; then
  echo "There are no releases!"
  exit 1
fi
cd dist_release

echo "Release name: "
read NAME
echo

DIRNAME="docu-$NAME"

# bzball
DESTFILENAME="$DIRNAME.tar.bz2"
[ -f "$DESTFILENAME" ] && rm "$DESTFILENAME"
echo "Compressing release, saving as $DESTFILENAME..."
tar -cj "$DIRNAME" > "$DESTFILENAME"

# tarball
DESTFILENAME="$DIRNAME.tar.gz"
[ -f "$DESTFILENAME" ] && rm "$DESTFILENAME"
echo "Compressing release, saving as $DESTFILENAME..."
tar -cz "$DIRNAME" > "$DESTFILENAME"

# zip file
DESTFILENAME="$DIRNAME.zip"
[ -f "$DESTFILENAME" ] && rm "$DESTFILENAME"
echo "Compressing release, saving as $DESTFILENAME..."
zip -rq "$DESTFILENAME" "$DIRNAME"

# md5
DESTFILENAME="$DIRNAME.md5"
[ -f "$DESTFILENAME" ] && rm "$DESTFILENAME"
echo "MD5ing release, saving as $DESTFILENAME..."
md5sum $DIRNAME.* > "$DESTFILENAME"
