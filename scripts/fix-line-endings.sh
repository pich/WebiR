#!/bin/bash

find ../application/ -name "*.php" -exec dos2unix {} \;
find ../library/doctrine/ -name "*.php" -exec dos2unix {} \;
find ../library/webir/ -name "*.php" -exec dos2unix {} \;
find ../library/zend/ -name "*.php" -exec dos2unix {} \;
find ../tests/ -name "*.php" -exec dos2unix {} \;
