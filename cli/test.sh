#!/bin/bash
wdir=$(cd $(dirname $0);pwd)
cmd='/usr/local/php5.6/bin/php '$wdir'/cli.php auto ad >> '$wdir'/../log/auto_ad.txt'
eval $cmd
