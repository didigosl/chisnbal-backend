#!/bin/bash
wdir=$(cd $(dirname $0);pwd)
cmd="php56 "$wdir"/cli.php auto stat >> "$wdir"/../log/auto_stat.txt"
eval $cmd
