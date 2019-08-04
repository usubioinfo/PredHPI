#!/bin/bash

outFilenameTabularHostLink=$1
outFilenameTabularPathogenLink=$2
threshold=$3
orgDB=$4
outTabularFilename=$5
combineMethod=$6

sed "s/outFilenameTabularHostLink/$outFilenameTabularHostLink/g;s/outFilenameTabularPathogenLink/$outFilenameTabularPathogenLink/g;s/threshold/$threshold/g;s/orgDB/$orgDB/g;s/outTabularFilename/$outTabularFilename/g;s/combineMethod/$combineMethod/g" /var/www/html/PredHPI/SLURM/gosemsim.sl
