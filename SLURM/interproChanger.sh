#!/bin/bash

inFilename=$1
outFilenameTabularLink=$2

sed "s/inFilename/$inFilename/g;s/outFileName/$outFilenameTabularLink/g;" /var/www/html/PredHPI/SLURM/interpro-dbased.sl
