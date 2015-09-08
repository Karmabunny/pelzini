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

if [ ! -d public_docs ]; then
  mkdir public_docs
fi
cd public_docs

echo "Checking out repository..."
svn co https://docu.svn.sourceforge.net/svnroot/docu/trunk/src docs

echo
echo "Generating docs.."
php docs/processor/main.php

echo
echo "Uploading to website..."
