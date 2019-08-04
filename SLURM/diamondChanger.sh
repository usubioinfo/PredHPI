#!/bin/bash

inFilename=$1
databaseList=$2
evalue=$3
outFilenameTabularLink=$4
slurm=$5

sed "s/inputfile/$inFilename/g;s/database/$databaseList/g;s/defevalue/$evalue/g;s/outfile/$outFilenameTabularLink/g;" /var/www/html/PredHPI/SLURM/diamond-interolog.sl > $slurm