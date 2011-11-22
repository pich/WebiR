#!/bin/bash

chmod a+rw ../application/tasks
chmod a+rw ../application/datasets
chmod a+rw ../application/logs
chmod a+rw ../sessions
touch ../application/logs/error.log
touch ../application/logs/debug.log
touch ../application/logs/common.log
if [ -a "../application/logs/common.log" ]; then
	chmod a+rw ../application/logs/*.log
fi

chmod +x ./*.sh
