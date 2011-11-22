#!/bin/bash

find ../application/ -name "*.php" -exec svn propset svn:keywords Id {} \;
find ../library/webir/ -name "*.php" -exec svn propset svn:keywords Id {} \;
find ../tests/ -name "*.php" -exec svn propset svn:keywords Id {} \;
