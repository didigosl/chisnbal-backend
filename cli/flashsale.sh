#!/bin/bash
wdir=$(cd $(dirname $0);pwd)
cmd="php56 "$wdir"/cli.php auto flashSale >> "$wdir"/../log/auto_flashSale.txt"
eval $cmd
