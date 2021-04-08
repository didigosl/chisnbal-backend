#!/bin/bash
wdir=$(cd $(dirname $0);pwd)
echo wdir
cm="ls "$wdir"/tasks"
eval $cm
