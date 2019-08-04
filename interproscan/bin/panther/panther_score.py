#!/usr/bin/env python

__author__ = "Gift Nuka"
__copyright__ = "Copyright 2017, EMBL-EBI"
__license__ = "Apache"
__version__ = "2.0"
__maintainer__ = "Gift Nuka"
__email__ = ""
__status__ = "production"

"""
this program takes hmmer3 domtablout and selects the best hit for Panther based on  e-value and bit scores.
 the hit with the smalles evalue and the highest score is selected.
 
 if two or more hits have the same evalue and score they all all selected.
 
 if subfamily has a hit the parent family is promoted.
 usage: python panther_score.py -d domtbl.out -m hmmscan|hmmsearch -n names.tab -e 0.001
 
 this program is ported from PantherScore.pl from pantherdb.org
"""

import sys
import getopt


def get_query_name(hmma):
    """
      get the panther family name from the query target
    """
    hmma_list = hmma.split ('.')
    if len(hmma_list) > 2:
        hmm_fam  = hmma_list[0]
        hmm_sf = hmma_list[1]
        something_else = hmma_list[2]
    elif len(hmma_list) == 2:
        hmm_fam  = hmma_list[0]
        something_else = hmma_list[1]
    hmm_id = hmm_fam
    if hmm_sf and hmm_sf.startswith("SF"):
        hmm_id = hmm_fam + ':' + hmm_sf
    return hmm_id

def get_panther_families(names_tab_file):
    panther_families = {}
    with open(names_tab_file, 'r') as infile:
        for line in infile:
            hmm_fam, family_name = line.split('\t')
            hmm_id = get_query_name(hmm_fam)
            panther_families[hmm_id] = family_name.strip()
    return panther_families

def append_to_match_list(all_scores, seq_id, item):
    updated_raw_matches = []
    if seq_id in all_scores:
        raw_matches = all_scores[seq_id]
        if  not raw_matches:
            #wwe expect hits for each seqid
            print_error ('some problem with the hmmer output, ...')
            sys.exit(4)
        raw_matches.append(item)
        updated_raw_matches = raw_matches
    else:
        updated_raw_matches = [item]
    return updated_raw_matches

#return true if two int ranges overlap
def location_overlaps(location1, location2):
    start_1,end_1 = list(map(int, location1.split("-")))
    start_2,end_2 = list(map(int,location2.split("-")))
    return max(start_1,start_2) <= min(end_1,end_2)

def get_match_groups(best_hits):
    group_hits = {}
    for el in best_hits:
        hmm_id = el[0]
        if hmm_id in group_hits:
            el_hits = group_hits[hmm_id]
            if el not in el_hits:
                el_hits.append(el)
        else:
            group_hits[hmm_id] = [el]
    return group_hits

def get_filtered_best_hits(best_hits):
    # hmm_hit = [hmm_id, description, float(eVal), float(score), location]
    filtered_best_hits = []
    all_best_hits = best_hits
    hits_per_hmmid = get_match_groups(best_hits)
    if len(list(hits_per_hmmid.keys())) == 1:
        return best_hits
    for hmmid in hits_per_hmmid:
        hmmid_hits =  hits_per_hmmid[hmmid]
        if ":SF" in hmmid:
            filtered_best_hits.extend(hmmid_hits)
            continue
        for el in hmmid_hits:
            base_el_location = el[4]
            overlaps = False
            for other_el in all_best_hits:
                if el == other_el:
                    continue
                if el[0] == other_el[0]:
                    continue
                if location_overlaps(base_el_location, other_el[4]):
                    overlaps = True
                    break
            if overlaps:
                all_best_hits.remove(el)
            else:
                filtered_best_hits.append(el)
    return filtered_best_hits


def get_best_hits(matches, evalue_cutoff):
    """
      get the best hit: one with smallest evalue and highest score.
      if at least two hits have same evalue and score, report them all
    """
    best_hits = []
    evalue_sorted = sorted(matches, key=lambda x: x[2])
    best_evalue = evalue_sorted[0][2]
    if best_evalue <= evalue_cutoff:
        scores = []
        for el in evalue_sorted:
            if best_evalue == el[2]:
                scores.append(el)
        score_sorted = sorted(scores, key=lambda x: x[3], reverse=True)
        best_score = score_sorted[0][3]
        for el in score_sorted:
            if best_score == el[3]:
                best_hits.append(el)
    return best_hits

def parse_domtblout(domtblout, panther_families, run_mode):
    all_scores = {}
    with open(domtblout, 'r') as infile:
        for line in infile:
            if not line.strip():
                #possible empty lines
                continue
            if not line.startswith('#'):
                if run_mode == 'hmmscan':
                    hmma, acc2, qlen, seqid, acc1, tlen, eVal, score, bias, num, num_t, cEval, iEval, dscore, dbias, hmm_f, hmm_t, ali_f, ali_t, env_f, env_t, accuracy, desc = line.split()
                elif run_mode == 'hmmsearch':
                    seqid, acc1, tlen, hmma, acc2, qlen, eVal, score, bias, num, num_t, cEval, iEval, dscore, dbias, hmm_f, hmm_t, ali_f, ali_t, env_f, env_t, accuracy, desc = line.split()
                else:
                    print_error ("error: run_mode is invalid: " + run_mode)
                hmm_id = get_query_name(hmma)
                description = panther_families[hmm_id]
                hmm_location = str(hmm_f) + '-' + str(hmm_t)
                ali_location = str(ali_f) + '-' + str(ali_t)
                env_location = str(env_f) + '-' + str(env_t)
                hmm_hit = [hmm_id, description, float(eVal), float(score), hmm_location, ali_location, env_location, qlen]
                matches = append_to_match_list(all_scores, seqid, hmm_hit)
                all_scores[seqid] = matches
    return all_scores

def print_list(seq_key, best_hits):
    out_str_list = []
    for el in best_hits:
        out_str_list.append (seq_key + "\t" + "\t".join(str(x) for x in el) )
        #promote parent of subfamily
        if ':SF' in el[0]:
            el[0] = el[0].split (':')[0]
            out_str_list.append ( seq_key + "\t" + "\t".join(str(x) for x in el) )
    return out_str_list

def print_error(message):
    if sys.version_info >= (3, 0):
        print((str(sys.version_info)+'\n'))
        #print(message, file=sys.stderr)
        sys.stderr.write(message)
    else:
        sys.stderr.write(message)
    sys.stderr.write('\n')

def usage():
    sys.stderr.write("usage: python panther_score.py -d domtbl.out -m hmmscan|hmmsearch -n names.tab -e 0.001\n" )

if __name__ == "__main__":
    try:
        opts, args = getopt.getopt(sys.argv[1:], "hd:n:e:m:o:", ["help", "domtbl", "runmode", "evalue-cutoff","output"])
    except getopt.GetoptError as err:
        print(err)  # should print something like "option -a not recognized"
        usage()
        sys.exit(2)

    for option, arg_value in opts:
        if option in ("-d", "--domtbl"):
            domtblout = arg_value
        elif option in ("-h", "--help"):
            usage()
            sys.exit()
        elif option in ("-m", "--runmode"):
            run_mode = arg_value
        elif option in ("-n", "--nametab"):
            names_tab_file = arg_value
        elif option in ("-e", "--evalue-cutoff"):
            evalue_cutoff = float(arg_value)
        elif option in ("-o", "--output"):
            output_file = arg_value
        else:
            assert False, "unhandled option"

    try:
        if not (domtblout and run_mode and names_tab_file and output_file):
            print_error("provide expected options")
            usage()
            sys.exit(3)
        if not evalue_cutoff:
            evalue_cutoff = float(1e-11)
    except NameError:
        print ("provide the required parameters")
        usage()
        sys.exit(3)

    panther_families = get_panther_families(names_tab_file)
    all_scores = parse_domtblout(domtblout, panther_families, run_mode)
    with open(output_file, 'a') as outf:
        for seq_key in all_scores:
            matches = all_scores[seq_key]
            best_hits = get_best_hits(matches,  evalue_cutoff)
            filtered_best_hits = get_filtered_best_hits(best_hits)
            out_str_list = print_list(seq_key, filtered_best_hits)
            for out_str in out_str_list:
                outf.write(out_str + '\n')
