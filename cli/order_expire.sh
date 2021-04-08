#!/bin/bash
wdir=$(cd $(dirname $0);pwd)
cmd="php56 "$wdir"/cli.php auto orderExpire >> "$wdir"/../log/auto_orderExpire.txt"
eval $cmd
