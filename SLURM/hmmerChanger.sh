#!/bin/bash

inFilename=$1
options=$2
evalue=$3
outFilenameTabularLink=$4

sed "s/inFilename/$inFilename/g;s/options/$options/g;s/defevalue/$evalue/g;s/outfile/$outFilenameTabularLink/g;" /var/www/html/PredHPI/SLURM/hmmer-dbased.sl
