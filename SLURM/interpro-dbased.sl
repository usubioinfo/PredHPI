#!/bin/bash
#SBATCH --time=3-00:00:00 # Walltime
#SBATCH --nodes=1          # Use 1 Node     (Unless code is multi-node parallelized)
#SBATCH --ntasks=64         # We only run one R instance = 1 task
#SBATCH --cpus-per-task=1 # number of threads we want to run on
#SBATCH -o slurm-%j.out-%N
#SBATCH --job-name=inteproWebServer-%j
#SBATCH --account=webservice
export WORK_DIR=/home/cloaiza/PredHPI_SLURM


module purge

module load python3/deepLearning/2019.04.07
# Create scratch & copy everything over to scratch
cd $WORK_DIR

# Run SCRIPT
echo "Evaluating $NAME at `date`"
/bin/sh /opt/software/interproscan-5.33-72.0/interproscan.sh -i /home/cloaiza/PredHPI_SLURM/inFilename -f tsv -appl Pfam,PROSITEPATTERNS,PROSITEPROFILES,PANTHER -iprlookup -goterms -o /home/cloaiza/PredHPI_SLURM/outFileName

echo "End of program at `date`"
