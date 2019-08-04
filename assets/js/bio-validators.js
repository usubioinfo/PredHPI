

/*

 REFORMAT.JS
 Authors: Seung-Zin Nam, David Rau

 */

function validateFasta(fasta) {

    if (!fasta) {
        return false;
    }


    // checks double occurrences of ">" in the header
    // Ignore #A3M# to allow A3M format input
    fasta = fasta.replace(/^#A3M#/, '');
    fasta = fasta.replace(/^\n+/, '');
    var newlines = fasta.split('\n');
    if (!newlines[0].startsWith("#") && newlines[0].startsWith(">")) {

        if (!fasta.startsWith('>')) {
            return false;
        }
        if (fasta.indexOf('>') == -1) {
            return false;
        }

        var splittedStrings = fasta.split('\n>'),
            i = 0;

        for (; i < splittedStrings.length; i++) {

            // immediately remove trailing spaces
            splittedStrings[i] = splittedStrings[i].trim();

            //check if header contains at least one char
            if (splittedStrings[i].length < 1) {
                return false;
            }

            //reinsert separator
            var seq = ">" + splittedStrings[i];

            // split on newlines...
            var lines = seq.split('\n');

            // check for header
            if (seq[0] == '>') {
                // remove one line, starting at the first position
                lines.splice(0, 1);

            }

            // join the array back into a single string without newlines and

            seq = lines.join('').trim();

            if (0 === seq.length || !seq) {
                console.warn("no sequence found for header");
                return false;
            }

            if (/[^-.*A-Z\s]/i.test(seq.toUpperCase())) {
                return false;
            }

        }

        return true;
    }

    //check if there are any headers or illegal characters at all (if not it might be intended as a single-line sequence)
    return !(/[^-.*A-Z\s]/i.test(fasta));
}


function validateDNA(json) {
    if(!json) {
        return;
    }
    for(var elem=0;elem< json.length; elem++){
        if(/[^\-\\AGTC\s]/i.test(json[elem].seq.toUpperCase())) {
            return false;
        }
    }
    return true;
}


function validateProtein(json) {
    if(!json) {
        return;
    }
    for(var elem=0;elem< json.length; elem++){

        if(!/[^\-\.\\AGTCU\s]/i.test(json[elem].seq.toUpperCase())) {

                return false;
        }
    }
    return true;
}