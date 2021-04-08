#!/bin/bash
wdir=$(cd $(dirname $0);pwd)
cmd="php56 "$wdir"/cli.php auto coupon >> "$wdir"/../log/auto_coupon.txt"
eval $cmd
