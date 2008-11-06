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
  mkdir dist_release
fi
cd dist_release

echo "Release name: "
read NAME
echo

DIRNAME="docu-$NAME"

if [ -d "$DIRNAME" ]; then
  echo "Removing existing release directory"
  rm -rf "$DIRNAME"
  echo
fi

echo "Exporting repository..."
svn export https://docu.svn.sourceforge.net/svnroot/docu/trunk/src "$DIRNAME"
echo

echo "Preforming automatic alterations..."
cd "$DIRNAME"
rm test -rf
echo

echo "Automatic alterations complete."
echo "You can now make manual alterations."
echo "When you are done, run compress_release.sh"
