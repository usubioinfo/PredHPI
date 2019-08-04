#!/bin/bash
#SBATCH --time=3-00:00:00 # Walltime
#SBATCH --nodes=1          # Use 1 Node     (Unless code is multi-node parallelized)
#SBATCH --ntasks=64         # We only run one R instance = 1 task
#SBATCH --cpus-per-task=1 # number of threads we want to run on
#SBATCH -o slurm-%j.out-%N
#SBATCH --job-name=hmmer-%j
#SBATCH --account=webservice
export WORK_DIR=/home/cloaiza/PredHPI_SLURM


module purge
module load hmmer/3.2.1


# Create scratch & copy everything over to scratch
cd $WORK_DIR

# Run SCRIPT
echo "Evaluating $NAME at `date`"
hmmscan --cpu 40 options -E defevalue --tblout /home/cloaiza/PredHPI_SLURM/outfile /home/cloaiza/PredHPI_SLURM/Pfam-A.hmm /home/cloaiza/PredHPI_SLURM/inFilename

echo "End of program at `date`"
