<?php
// Start the session
header("Access-Control-Allow-Origin: *");


ini_set('display_errors', 0);
error_reporting(0);


$threshold= $_POST['threshold'];
$coverage= $_POST['coverage'];
$evalue= $_POST['evalue'];
$identity= $_POST['identity'];

$coveragePatho= $_POST['coveragePatho'];
$evaluePatho= $_POST['evaluePatho'];
$identityPatho= $_POST['identityPatho'];
$pool= $_POST['pool'];


//$fileSeqHost= $_POST['fileSeqHost'];
$areaSeqHost= $_POST['areaSeqHost'];
//$fileSeqPathogen= $_POST['fileSeqPathogen'];
$areaSeqPathogen= $_POST['areaSeqPathogen'];


$emailAddress= $_POST['emailAddress'];


$namer = $_POST['namer'];

$inFilenameHost= '/var/www/html/PredHPI/tmp/' . $namer . '_HostInput.txt';
$inFilenamePathogen= '/var/www/html/PredHPI/tmp/' . $namer . '_PathogenInput.txt';

$outFilenameTabularHost= '/var/www/html/PredHPI/tmp/' . $namer . '_HostBlast_';
$outFilenameTabularPathogen= '/var/www/html/PredHPI/tmp/' . $namer . '_PathogenBlast_';



$outInfoFilename= '/var/www/html/PredHPI/tmp/' . $namer . '_PhyloPredTaskInfo.txt';
$outTabularFilename= '/var/www/html/PredHPI/tmp/' . $namer . '_PhyloPredTabularInfo.txt';
$outNetFilename= '/var/www/html/PredHPI/tmp/' . $namer . '.json';

$numberHost= 0;
$numberPathogen= 0;

$idsHost=array();
$idsPathogen=array();


//echo("\n");

function testFasta($fastaText,$type) {
    $checkPass  = true;
    $protSeq = false;
    $fastasequences = $fastaText;
    $checkStatus = 0;

    global $idsHost;
    global $idsPathogen;
    global $numberHost;
    global $numberPathogen;
    //Extract TextArea Element Value
    
    $sequences = explode("\n",$fastasequences);


    if(sizeof($sequences)>1){

        $isFastaHeader = false;
        $protSeq = false;
        //
        
        $aminoacidRegex= "/^[ILVFMCAGPTSYWQNHEDKRXUBZ]+[\*]*$/";

        $firstLine =trim($sequences[0]);

        if($firstLine[0]=='>'){
        	
            //echo ("ID in First line". "\n");
            if($type==1){
            	$numberHost= $numberHost + 1;
            	$tmp=explode(" ",$firstLine);
            	$id_tmp=explode(">",$tmp[0]);
            	$idsHost[] =$id_tmp[1];


            } else if($type==2){
            	$numberPathogen= $numberPathogen + 1;
            	$tmp=explode(" ",$firstLine);
            	$id_tmp=explode(">",$tmp[0]);
            	$idsPathogen[] =$id_tmp[1];

            }

            if(preg_match($aminoacidRegex,trim($sequences[1]))){
                $protSeq = true;
                //echo ("Is a protein seq". "\n");
            } else{
                //echo ("Is nothing". "\n");
                $checkPass = false;
            }

            if($checkPass ){
            	
                $sequencestatus = 1;//0 if last line was space, 1 if last line was id, 2 if last line were $sequences
                
                for ($i = 1; $i < sizeof($sequences); $i++) { 

                    $fastaLine = trim($sequences[$i]);
                    
                    if(strlen($fastaLine) == 0 && $sequencestatus == 0){
                        
                        //echo ("space reading". "\n");
                        $sequencestatus = 0;
                        
                    } else if($fastaLine[0]=='>' && $sequencestatus != 1){
                    	
                        //echo ("Id reading". "\n");
                        $sequencestatus = 1;
                        if($type==1){
                        	$numberHost= $numberHost + 1;
                        	$tmp=explode(" ",$fastaLine);
                        	$id_tmp=explode(">",$tmp[0]);
                        	//echo $id_tmp;
                        	array_push($idsHost,$id_tmp[1]);


                        } else if($type==2){
                        	$numberPathogen= $numberPathogen + 1;
                        	$tmp=explode(" ",$fastaLine);
                        	$id_tmp=explode(">",$tmp[0]);
                        	//echo $id_tmp;
                        	array_push($idsPathogen,$id_tmp[1]);

                        }
                        
                    } else if($protSeq && preg_match($aminoacidRegex,$fastaLine)  && $sequencestatus > 0){
						
                        $isFastaHeader = true;
                        $sequencestatus = 2;
                        //echo ("protein reading". "\n");
                        
                    } else if(strlen($fastaLine) == 0 && $sequencestatus > 1){
                    	
                        //echo ("space reading". "\n");
                        $sequencestatus = 0;
                        
                    } else {
                    	
                        $checkPass  = false;
                        //echo ("false sequence". "\n");
                        break;
                    }          	
                }
            }
     
            if(!$checkPass ){
                //echo ('Wrong file');
            }
            
        } else {
            $checkPass  = false;
            //echo ('First line does not include an ID');
        }   
  
    }

    if($protSeq){
        $checkStatus = 1;
    }

    if($checkPass){
        $checkStatus += 5;
    }               
	
    return $checkStatus;
}


$hostSequence = "";
$pathogenSequence = "";

$proceed = false;
$proceedHost = false;
$proceedPathogen = false;
$errorFasta = 0;

$typeSeqHost ="";

$typeSeqPathogen ="";

$correctFastaFileHost = 0;
$correctFastaFilePathogen = 0;



if (!copy($_FILES['fileSeqHost']['tmp_name'], $inFilenameHost)) {
    //echo "Unable to open file!...\n";
    $hostSequence = $areaSeqHost;
    $typeSeqHost = "text";
} else {
	$hostSequence = file_get_contents($inFilenameHost);
	$typeSeqHost = "file";
}

$correctFastaFileHost = testFasta($hostSequence,1);



if (!copy($_FILES['fileSeqPathogen']['tmp_name'], $inFilenamePathogen)) {
    //echo "Unable to open file!...\n";
    $pathogenSequence = $areaSeqPathogen;
    $typeSeqPathogen = "text";
} else {
	$pathogenSequence = file_get_contents($inFilenamePathogen);
	$typeSeqPathogen = "file";
}
$correctFastaFilePathogen = testFasta($pathogenSequence,2);

if($typeSeqHost == "text"){
	$fastafileHost = fopen($inFilenameHost, "w") or die("Unable to open file!");
	$fastaTxtHost = $hostSequence;
	fwrite($fastafileHost, $fastaTxtHost);
	fclose($fastafileHost);
}

if($typeSeqPathogen == "text"){
	$fastafilePathogen = fopen($inFilenamePathogen, "w") or die("Unable to open file!");
	$fastaTxtPathogen = $pathogenSequence;
	fwrite($fastafilePathogen, $fastaTxtPathogen);
	fclose($fastafilePathogen);
}

if($correctFastaFileHost > 5){
	$proceedHost = true;
	//echo "Proceed pathogen ". $proceedHost ."\n";
}

if($correctFastaFilePathogen > 5 ){
	$proceedPathogen = true;
	//echo "Proceed pathogen ". $proceedPathogen ."\n";

}

$errorFasta = 0;
$proceed = $proceedHost && $proceedPathogen;
if(!$proceedHost && !$proceedPathogen){
	$errorFasta = 2;
}else if(!$proceedHost){
	$errorFasta = 1;
}else if(!$proceedPathogen){
	$errorFasta = 3;
}

//echo "Proceed ";
//echo $proceed ? 'true' : 'false';

if($proceed){

	$infoTaskFile = fopen($outInfoFilename, "w") or die("Unable to open file!");
	
	#$infoText = "Genome pool: ".$pool."\n";
	#fwrite($infoTaskFile, $infoText);
	$infoText = "Host\n";
	fwrite($infoTaskFile, $infoText);
	$infoText = "Minimum Identity (%): $identity\n";
	fwrite($infoTaskFile, $infoText);
	$infoText = "Minimum Coverage (%): $coverage\n";
	fwrite($infoTaskFile, $infoText);
	$infoText = "Expected value: $evalue\n";
	fwrite($infoTaskFile, $infoText);

	$infoText = "Pathogen\n";
	fwrite($infoTaskFile, $infoText);
	$infoText = "Minimum Identity (%): $identityPatho\n";
	fwrite($infoTaskFile, $infoText);
	$infoText = "Minimum Coverage (%): $coveragePatho\n";
	fwrite($infoTaskFile, $infoText);
	$infoText = "Expected value: $evaluePatho\n";
	fwrite($infoTaskFile, $infoText);

	$infoText = "Threshold: ".$threshold."\n";
	fwrite($infoTaskFile, $infoText);
	
	$infoText = "Email address provided: $emailAddress\n\n";
	fwrite($infoTaskFile, $infoText);
	
	fclose($infoTaskFile);

	##get sequences ids

	$patternHost= new SplFixedArray($numberHost);
	$patternPathogen= new SplFixedArray($numberPathogen);

	for($x = 0; $x < $numberHost; $x++){
		$patternHost[$x] = "";
	}

	for($y = 0; $y < $numberPathogen; $y++){
		$patternPathogen[$y] = "";
	}

	##pool genome readin
	$poolFolder = "phyloBioconductor";
	$poolList = file("bioconductorPool.txt");
	$nullPool = "000000000000000000";
	if($pool=="bioconductorPool"){
		$poolFolder = "phyloBioconductor";
		$poolList = file("bioconductorPool.txt");
		$nullPool = "000000000000000000";
	} else if($pool=="modelPool"){
		$poolFolder = "phyloModelSC";
		$poolList = file("modelPool.txt");
		$nullPool = "0000000000000000000000000000000000000000000000000000000000000000000000000000000000";
	}
	#$poolList = file("randome.txt");
	$genomeNumber = sizeof($poolList);

	
	for ($i = 0; $i < $genomeNumber; $i++) {
		
		#$blastInstructionHost = 'blastp -db /home/phyloDB/'.trim($poolList[$i]);

		$blastInstructionHost = 'diamond blastp --db /home/'.$poolFolder.'/'.trim($poolList[$i]);
		$blastInstructionHost .= ' --query ' . $inFilenameHost;
		$blastInstructionHost .= ' --quiet --evalue ' . $evalue . ' --out  ' . $outFilenameTabularHost.$i.".txt" ;
		$blastInstructionHost .= ' --outfmt 6 qseqid sseqid pident length mismatch gapopen qstart qend sstart send evalue bitscore qcovhsp qcovhsp -k 1';
		$blastInstructionHost .= " --threads 4 ";
		//echo ($blastInstructionHost);

		#$blastInstructionPathogen = 'blastp -db /home/phyloDB/'.trim($poolList[$i]);
		$blastInstructionPathogen = 'diamond blastp --db /home/'.$poolFolder.'/'.trim($poolList[$i]);
		$blastInstructionPathogen .= ' --query ' . $inFilenamePathogen;
		$blastInstructionPathogen .= ' --quiet --evalue ' . $evaluePatho . ' --out  '. $outFilenameTabularPathogen.$i.".txt" ;
		$blastInstructionPathogen .= ' --outfmt 6 qseqid sseqid pident length mismatch gapopen qstart qend sstart send evalue bitscore qcovhsp qcovhsp -k 1';
		$blastInstructionPathogen .= " --threads 4 ";
		//echo ($blastInstructionPathogen);

		exec($blastInstructionHost, $outputHost, $return_varHost);
		exec($blastInstructionPathogen, $outputPathogen, $return_varPathogen);


		$blastOutputHostIds=array();
		$blastOutputPathogenIds=array();

		$fileTabularHost = fopen($outFilenameTabularHost.$i.".txt","r");
		
		while(! feof($fileTabularHost)){
		    $line = fgets($fileTabularHost);
		    if($line != false && $line != '\n' && trim($line) != ''){
		        $separatedLine = explode("\t",$line);
		        if(intval($separatedLine[2]) >= $identity && intval($separatedLine[12]) >= $coverage){
		        	array_push($blastOutputHostIds,$separatedLine[0]);
		        } 
		    }
	    }
	    fclose($fileTabularHost);

	    $fileTabularPathogen = fopen($outFilenameTabularPathogen.$i.".txt","r");

		while(! feof($fileTabularPathogen)){
		    $line = fgets($fileTabularPathogen);
		    if($line != false && $line != '\n' && trim($line) != ''){
		        $separatedLine = explode("\t",$line);
		        if(intval($separatedLine[2]) >= $identityPatho && intval($separatedLine[12]) >= $coveragePatho){
		        	array_push($blastOutputPathogenIds,$separatedLine[0]);
		        } 
		    }
	    }

	    fclose($fileTabularPathogen);

	    unlink($outFilenameTabularHost.$i.".txt");
	    unlink($outFilenameTabularPathogen.$i.".txt");

		for ($l = 0; $l < $numberHost; $l++) {
			if(in_array($idsHost[$l],$blastOutputHostIds)){
				//exists
				$patternHost[$l].="1";
			} else{
				$patternHost[$l].="0";
			}
		}

		for ($m = 0; $m < $numberPathogen; $m++) {
			if(in_array($idsPathogen[$m],$blastOutputPathogenIds)){
				//exists
				$patternPathogen[$m].="1";
			} else{
				$patternPathogen[$m].="0";
			}
		}

		
	}
	
	$infoTabularFile = fopen($outTabularFilename, "w") or die("Unable to open file!");

	for ($l = 0; $l < $numberHost; $l++) {
		if($nullPool!=$patternHost[$l]){

			for ($m = 0; $m < $numberPathogen; $m++) {
				$hostID = $idsHost[$l];
	    		$pathogenID = $idsPathogen[$m];
	    		$simValue = ($genomeNumber-levenshtein($patternHost[$l], $patternPathogen[$m]))/$genomeNumber;
	    		$hostPattern = $patternHost[$l];
	    		$pathogenPattern = $patternPathogen[$m];
	    		$interacting = "NO";
	    		if($simValue >= $threshold){
	    			$interacting = "YES";
		    		$infoText = $interacting."\t".$hostID."\t".$pathogenID."\t".$simValue."\t".$hostPattern."\t".$pathogenPattern."\n";
	    			fwrite($infoTabularFile, $infoText);
	    		}


			}
		}

	}
	fclose($infoTabularFile);

	

	### Arrays to create json object
	
	$netNodes = array();
	$netEdges = array();
	$nodesAdded = array();
	$netElements = array();

	
	$infoTabularFile = fopen($outTabularFilename, "r") or die("Unable to open file!");
	
	
	while(! feof($infoTabularFile)){
	    $line = fgets($infoTabularFile);
	    if($line != false && $line != '\n' && trim($line) != ''){
	    	$separatedLine = explode("\t",$line);
	    	if($separatedLine[0]=="YES"){


	    		$hostID = $separatedLine[1];
	    		$pathogenID = $separatedLine[2];
	    		$simValue = $separatedLine[3];
	    		$hostPattern = $separatedLine[4];
	    		$pathogenPattern = $separatedLine[5];

	    		$nodeHostArray = array('id' => $hostID, 'pattern' => $hostPattern, 'typeColor' => '#004085');
				$nodePathogenArray = array('id' => $pathogenID, 'pattern' => $pathogenPattern, 'typeColor' => '#ff5d48');

				$edgeArray = array('id' => $hostID."_".$pathogenID, 'name' => $hostID." pp ".$pathogenID, 'similatirity' => $simValue,'source' => $hostID, 'target' => $pathogenID, 'databaseColor'=> '#7fdb6a');

				if(!in_array($hostID,$nodesAdded)){

					$netNodes[] = array('data' => $nodeHostArray);
					array_push($nodesAdded,$hostID);
				}

				if(!in_array($pathogenID,$nodesAdded)){
					$netNodes[] = array('data' => $nodePathogenArray);
					array_push($nodesAdded,$pathogenID);
				}

				$netEdges[] = array('data' => $edgeArray);

	    	}
	    }
	}	
	
	fclose($infoTabularFile);
	

	
	$netElements= array('nodes' => $netNodes,'edges' => $netEdges);

	$netFile = fopen($outNetFilename, "w") or die("Unable to open file!");
	
	fwrite($netFile, json_encode($netElements));
	fclose($netFile);
	
		

	if($emailAddress != "noemail"){
		$msgEmail = "Your Phylo prediction job at PredHPI is done! \nPlease go to http://bioinfo.usu.edu/PredHPI/phylo-results.php?result=$namer to see it.";
		$msgEmail = wordwrap($msgEmail,70);
		$from = "noreply@bioinfo.biotec.usu.edu";
		$headers = "From: $from"; 
		$mail= mail($emailAddress,"GoPred PredHPI results",$msgEmail,$headers,'-f '.$from);
		if($mail){
		  //echo "Email sent";
		}else{
		  //echo "Something went wrong with Mail."; 
		}
	}
	print_r($namer);
} else {
	echo("fastaerror-".$errorFasta);
}


unlink($inFilenameHost);
unlink($inFilenamePathogen);









//print_r($_FILES);

//echo($_POST['evalue']);


?>

