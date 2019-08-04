#!/bin/bash
#SBATCH --time=3-00:00:00 # Walltime
#SBATCH --nodes=1
#SBATCH --ntasks=64
#SBATCH --cpus-per-task=1
#SBATCH -o slurm-%j.out-%N
#SBATCH --job-name=blastPar-%j
#SBATCH --account=webservice
export WORK_DIR=/home/cloaiza/PredHPI_SLURM


module purge
module load blast/2.7.1+


# Create scratch & copy everything over to scratch
cd $WORK_DIR

# Run SCRIPT
echo "Evaluating $NAME at `date`"
blastalgorithm -db /home/cloaiza/PredHPI_SLURM/database -evalue defevalue -max_target_seqs options -outfmt '6 qseqid sseqid pident length mismatch gapopen qstart qend sstart send evalue bitscore qcovhsp qcovhsp' -query inputfile -out /home/cloaiza/PredHPI_SLURM/outfile -num_threads 64

echo "End of program at `date`"
