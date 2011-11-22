#!/bin/bash

#
# WebiR -- The Web Interface to R
#
# LICENSE
#
# This source file is subject to the new BSD license that is bundled
# with this package in the file LICENSE.txt.
# It is also available through the world-wide-web at this URL:
# http://escsa.eu/license/webir.txt
# If you did not receive a copy of the license and are unable to
# obtain it through the world-wide-web, please send an email
# to firma@escsa.pl so we can send you a copy immediately.
#
# @category   App
# @package    Core
# @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
# @author     Jakub Argasiński <jakub.argasinski@escsa.pl>
# @author     Daniel Bojdo <daniel.bojdo@escsa.pl>
# @license    http://escsa.eu/license/webir.txt     New BSD License
#

ABSPATH="$(dirname $(cd "${0%/*}" 2>/dev/null; echo "$PWD"/"${0##*/}"))"

while(true) do
	APPLICATION_ENV=cli-dev php $ABSPATH/r-task-manager.php &
	APPLICATION_ENV=cli-dev php $ABSPATH/r-dataset-processor.php &
	/usr/sbin/logrotate -s $ABSPATH/../application/logs/status $ABSPATH/logrotate.conf
	sleep 5
done;
