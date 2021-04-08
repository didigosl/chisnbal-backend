#!/bin/bash
wdir=$(cd $(dirname $0);pwd)
cm="php56 "$wdir"/cli.php auto ad>> "$wdir"/../log/auto_ad.txt"
eval $cm
