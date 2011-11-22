#!/bin/bash

psql -U webiru -d webirdb < /home/httpd/scripts/restart-running-tasks.sql
