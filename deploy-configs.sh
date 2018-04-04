#!/usr/bin/env bash

set -x # expands variables and prints a little + sign before the line

appname="sygal"

# preprod
scp ./config/autoload/local.sygal-pp.php root@sygal-pp:/root/.ssh/${appname}/
