#!/bin/bash
#SBATCH --time=3-00:00:00 # Walltime
#SBATCH --nodes=1          # Use 1 Node     (Unless code is multi-node parallelized)
#SBATCH --ntasks=18         # We only run one R instance = 1 task
#SBATCH --cpus-per-task=1 # number of threads we want to run on
#SBATCH -o slurm-%j.out-%N
#SBATCH --mem=4G
#SBATCH --job-name=GOsemsim-%j
#SBATCH --account=webservice
export WORK_DIR=/home/cloaiza/PredHPI_SLURM


module purge
module load R

# Create scratch & copy everything over to scratch
cd $WORK_DIR

# Run SCRIPT
echo "Evaluating $NAME at `date`"

Rscript /home/cloaiza/PredHPI_SLURM/GOsimCalculation.R /home/cloaiza/PredHPI_SLURM/outFilenameTabularHostLink /home/cloaiza/PredHPI_SLURM/outFilenameTabularPathogenLink threshold orgDB /home/cloaiza/PredHPI_SLURM/outTabularFilename combineMethod


echo "End of program at `date`"

