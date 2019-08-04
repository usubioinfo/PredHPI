#!/bin/bash

inFilename=$1
databaseList=$2
evalue=$3
outFilenameTabularLink=$4
options=$5
blastalgorithm=$6;

sed "s/inputfile/$inFilename/g;s/database/$databaseList/g;s/defevalue/$evalue/g;s/outfile/$outFilenameTabularLink/g;s/options/$options/g;s/blastalgorithm/$blastalgorithm/g" /var/www/html/PredHPI/SLURM/parBlast.sh
