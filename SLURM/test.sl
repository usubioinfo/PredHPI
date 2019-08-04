#!/bin/bash
#SBATCH --time=3-00:00:00 # Walltime
#SBATCH --nodes=1          # Use 1 Node     (Unless code is multi-node parallelized)
#SBATCH --ntasks=32         # We only run one R instance = 1 task
#SBATCH --cpus-per-task=1 # number of threads we want to run on
#SBATCH -o slurm-%j.out-%N
#SBATCH --job-name=test

export WORK_DIR=/home/cloaiza/PredHPI_SLURM


module purge
module load diamond/0.9.24


# Create scratch & copy everything over to scratch
cd $WORK_DIR

# Run SCRIPT
echo "Evaluating $NAME at `date`"
diamond blastp --query PredHPI_SLURM/test.fasta --db /home/shared_dbs_MODS/bioinfo_dbs/hpidb --evalue 1e-10  --outfmt 6 qseqid sseqid pident length mismatch gapopen qstart qend sstart send evalue bitscore qcovhsp qcovhsp   --out PredHPI_SLURM/outtest.txt --threads $SLURM_NTASKS

echo "End of program at `date`"
