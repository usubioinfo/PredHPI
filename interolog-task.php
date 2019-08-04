<?php
// Start the session
header("Access-Control-Allow-Origin: *");


ini_set('display_errors', 1);
error_reporting(1);

//include('assets/Net/SSH2.php');
//include('assets/Crypt/Random.php');

$coverage= $_POST['coverage'];
$evalue= $_POST['evalue'];
$identity= $_POST['identity'];

$coveragePatho= $_POST['coveragePatho'];
$evaluePatho= $_POST['evaluePatho'];
$identityPatho= $_POST['identityPatho'];
$matchStrategyInterolog= $_POST['matchStrategyInterolog'];

$database= $_POST['database'];

//$fileSeqHost= $_POST['fileSeqHost'];
$areaSeqHost= $_POST['areaSeqHost'];
//$fileSeqPathogen= $_POST['fileSeqPathogen'];
$areaSeqPathogen= $_POST['areaSeqPathogen'];


$emailAddress= $_POST['emailAddress'];
$interologType= $_POST['interologType'];

$sequencesNumber = 0;

$namer = $_POST['namer'];

$inFilenameHost= '/var/www/html/PredHPI/tmp/' . $namer . '_HostBlastInput.txt';
$inFilenamePathogen= '/var/www/html/PredHPI/tmp/' . $namer . '_PathogenBlastInput.txt';

$inFilenameHostLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_HostBlastInput.txt';
$inFilenamePathogenLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_PathogenBlastInput.txt';

$inFilenameHostLinkBase= $namer . '_HostBlastInput.txt';
$inFilenamePathogenLinkBase= $namer . '_PathogenBlastInput.txt';

$slurmHost= '/var/www/html/PredHPI/SLURM/' . $namer . '_HostDiamond-interolog.sl';
$slurmPathogen= '/var/www/html/PredHPI/SLURM/' . $namer . '_PathogenDiamond-interolog.sl';

# It is possible that the number of blast executions is not just one so I will leave this name "incomplete" (without the .txt) and will edit it programatically as I make a new blast execution
$outFilenameTabularHost= '/var/www/html/PredHPI/tmp/' . $namer . '_HostBlastOutputInterolog_';
$outFilenameTabularPathogen= '/var/www/html/PredHPI/tmp/' . $namer . '_PathogenBlastOutputInterolog_';

$outFilenameTabularHostBase= $namer . '_HostBlastOutputInterolog_';
$outFilenameTabularPathogenBase= $namer . '_PathogenBlastOutputInterolog_';

$outFilenameTabularHostLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_HostBlastOutputInterolog_';
$outFilenameTabularPathogenLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_PathogenBlastOutputInterolog_';

$outInfoFilename= '/var/www/html/PredHPI/tmp/' . $namer . '_InterologTaskInfo.txt';
$outTabularFilename= '/var/www/html/PredHPI/tmp/' . $namer . '_InterologTabularInfo.txt';
$outNetFilename= '/var/www/html/PredHPI/tmp/' . $namer . '.json';

$databaseList = explode("\n",$database);
array_pop($databaseList);



function testFasta($fastaText) {
    $checkPass  = true;
    $protSeq = false;
    $nuclSeq = false;
    $fastasequences = $fastaText;
    $checkStatus = 0;
    //Extract TextArea Element Value
    
    $sequences = explode("\n",$fastasequences);

    if(sizeof($sequences)>1){

        $isFastaHeader = false;
        $protSeq = false;
        $nuclSeq = false;
        //
        
        $nucleotideRegex= "/^[ATGCN]*$/";
        $aminoacidRegex= "/^[ILVFMCAGPTSYWQNHEDKRXUBZ]+[\*]*$/";

        $firstLine =trim($sequences[0]);

        if($firstLine[0]=='>'){
        	
            //echo ("ID in First line". "\n");
            
            if(preg_match($nucleotideRegex,trim($sequences[1]))){
                $nuclSeq = true;
                //echo ("Is a nucleotide seq". "\n");
            } else if(preg_match($aminoacidRegex,trim($sequences[1]))){
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
                        $sequencesNumber = $sequencesNumber + 1 ;
                        
                    } else if($nuclSeq && preg_match($nucleotideRegex,$fastaLine)  && $sequencestatus > 0){
						
                        $isFastaHeader = true;
                        $sequencestatus = 2;
                        //echo ("nucleotide reading". "\n");
                        
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

    if($nuclSeq){
        $checkStatus = 2;
    } else if($protSeq){
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
$blastTypeHost ="";

$typeSeqPathogen ="";
$blastTypePathogen ="";

$correctFastaFileHost = 0;
$correctFastaFilePathogen = 0;

if($interologType <= 2){
	if (!copy($_FILES['fileSeqHost']['tmp_name'], $inFilenameHost)) {
	    //echo "Unable to open file!...\n";
	    $hostSequence = $areaSeqHost;
	    $typeSeqHost = "text";
	} else {
		$hostSequence = file_get_contents($inFilenameHost);
		$typeSeqHost = "file";
	}
	$correctFastaFileHost = testFasta($hostSequence);
}

if($interologType >= 2){
	if (!copy($_FILES['fileSeqPathogen']['tmp_name'], $inFilenamePathogen)) {
	    //echo "Unable to open file!...\n";
	    $pathogenSequence = $areaSeqPathogen;
	    $typeSeqPathogen = "text";
	} else {
		$pathogenSequence = file_get_contents($inFilenamePathogen);
		$typeSeqPathogen = "file";
	}
	$correctFastaFilePathogen = testFasta($pathogenSequence);
}


if($correctFastaFileHost > 5 && $interologType <= 2){
	$proceedHost = true;
	//echo "Proceed pathogen ". $proceedHost ."\n";
	if($correctFastaFileHost < 7){
        $blastTypeHost ="diamond blastp";
    } else {
        $blastTypeHost ="diamond blastx";
    }
}

if($correctFastaFilePathogen > 5 && $interologType >= 2 ){
	$proceedPathogen = true;
	//echo "Proceed pathogen ". $proceedPathogen ."\n";
	if($correctFastaFilePathogen < 7){
        $blastTypePathogen ="diamond blastp";
    } else {
        $blastTypePathogen ="diamond blastx";
    }
}

if($interologType == 1){
	$proceed = $proceedHost;
	if(!$proceedHost){
		$errorFasta = 1;
	}
} else if($interologType == 2){
	$proceed = $proceedHost && $proceedPathogen;
	if(!$proceedHost && !$proceedPathogen){
		$errorFasta = 2;
	}else if(!$proceedHost){
		$errorFasta = 1;
	}else if(!$proceedPathogen){
		$errorFasta = 3;
	}
} else if($interologType == 3){
	$proceed = $proceedPathogen;
	if(!$proceedPathogen){
		$errorFasta = 3;
	}
}
#echo "Interolog ". $interologType . "\n";
#echo "Proceed ";
#echo $proceed ? 'true' : 'false';

if($proceed){

	$infoTaskFile = fopen($outInfoFilename, "w") or die("Unable to open file!");
	
	$infoText = "Databases:";
	fwrite($infoTaskFile, $infoText);
	for ($i = 0; $i < sizeof($databaseList); $i++) {
		$databaseName = trim($databaseList[$i]);
		$infoText = "  $databaseName  ";
		fwrite($infoTaskFile, $infoText);
	}
	$infoText = "\n";
	fwrite($infoTaskFile, $infoText);
	if($interologType == 1){
		$infoText = "Interolog Type: Only Host\n";
	} else if($interologType == 2){
		$infoText = "Interolog Type: Host and Pathogen\n";
	} else if($interologType == 3){
		$infoText = "Interolog Type: Only Pathogen\n";
	}
	
	fwrite($infoTaskFile, $infoText);

	$infoText = "Host:\n";
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
	
	$infoText = "Email address provided: $emailAddress\n\n";
	fwrite($infoTaskFile, $infoText);
	
	fclose($infoTaskFile);


	$infoTabularFile = fopen($outTabularFilename, "w") or die("Unable to open file!");


	$link = mysqli_connect('127.0.0.01:3306', 'user', 'password') or die('Could not connect to mysql');
	$link->set_charset("utf8");
	#echo ('Connected successfully');
	mysqli_select_db($link,'database') or die('Could not select database');
	$hostDBresults = array();
	$pathogenDBresults = array();

	### Arrays to create json object
	$netNodes = array();
	$netEdges = array();
	$nodesAdded = array();
	$netElements = array();
	$resultsUnique = array();
	/*
	$ssh = new Net_SSH2('129.123.62.5','22');
	if (!$ssh->login('user', 'password')) {
		$ssh->getLog();
		$ssh->getErrors();
	    exit('Login Failed');
	}
	*/
	for ($i = 0; $i < sizeof($databaseList); $i++) {

		$hostNumberSequences = 0;
		$pathogenNumberSequences = 0;
		$hostMapBlast = array();
		$pathogenMapBlast = array();


		$databaseColor = '#FFFFF';

		echo($databaseList[$i);

		if(trim($databaseList[$i])=="hpidb"){
			$databaseColor = '#d32f2f';
		} else if(trim($databaseList[$i])=="mintdb"){
			$databaseColor = '#512da8';
		} else if(trim($databaseList[$i])=="dipdb"){
			$databaseColor = '#455a64';
		} else if(trim($databaseList[$i])=="biogriddb"){
			$databaseColor = '#303f9f';
		} else if(trim($databaseList[$i])=="intactdb"){
			$databaseColor = '#388e3c';
		}  else if(trim($databaseList[$i])=="virhostnet"){
			$databaseColor = '#ffa000';
		}  else if(trim($databaseList[$i])=="stringdb"){
			$databaseColor = '#f57c00';
		}  else if(trim($databaseList[$i])=="arabhpi"){
			$databaseColor = '#fbc02d';
		}  else if(trim($databaseList[$i])=="phisto"){
			$databaseColor = '#0288d1';
		}


		$blastInstructionHost = $blastTypeHost . ' --db Diamond_DB/'.trim($databaseList[$i]);
		$blastInstructionPathogen = $blastTypePathogen . ' --db Diamond_DB/'.trim($databaseList[$i]);

		if($interologType <= 2){
			if($typeSeqHost == "text"){
				$fastafileHost = fopen($inFilenameHost, "w") or die("Unable to open file!");
				$fastaTxtHost = $hostSequence;
				fwrite($fastafileHost, $fastaTxtHost);
				fclose($fastafileHost);
			} 

			//if($sequencesNumber > 2){

			$blastInstructionHost .= ' --query ' . $inFilenameHost;
			if($matchStrategyInterolog=="bm"){
				$blastInstructionHost .= ' --max-target-seqs 1 ';
			}
			$blastInstructionHost .= ' --evalue ' . $evalue . ' --out  ' . $outFilenameTabularHost.trim($databaseList[$i]).".txt" ;
			$blastInstructionHost .= ' --quiet --outfmt 6 qseqid sseqid pident length mismatch gapopen qstart qend sstart send evalue bitscore qcovhsp qcovhsp';
			$blastInstructionHost .= " --threads 4 ";

			//echo ($blastInstructionHost);
			exec($blastInstructionHost, $outputHost, $return_varHost);
			/*
			} else {

				//$sedCommand = " sed 's/inputfile/".$inFilenameHost."/g;s/database/".$databaseList[$i]."/g;s/defevalue/".$evalue."/g;s/outfile/".$outFilenameTabularHostLink.trim($databaseList[$i]).".txt/g;' /var/www/html/PredHPI/SLURM/diamond-interolog.sl > ".$slurmHost;
				$diamondChangerHost = "/var/www/html/PredHPI/SLURM/diamondChanger.sh ".$inFilenameHostLinkBase." ".trim($databaseList[$i])." ".$evalue." ".$outFilenameTabularHostBase.trim($databaseList[$i]).".txt ".$slurmHost;
				//$sshComand = "ssh user@biocluster.usu.edu 'sbatch --wait ".$slurmHost." ' ";

				echo $ssh->exec('pwd');
				echo $ssh->exec('ls -la');

				copy($inFilenameHost, $inFilenameHostLink);
				exec($diamondChangerHost);
				//exec($sshComandHost);
				copy($outFilenameTabularHostLink.trim($databaseList[$i]).".txt ", $outFilenameTabularHost.trim($databaseList[$i]).".txt ");

			}
			*/

		}


		if($interologType >= 2){
			if($typeSeqPathogen == "text"){
				$fastafilePathogen = fopen($inFilenamePathogen, "w") or die("Unable to open file!");
				$fastaTxtPathogen = $pathogenSequence;
				fwrite($fastafilePathogen, $fastaTxtPathogen);
				fclose($fastafilePathogen);
			} 


			//if($sequencesNumber > 2){
				
			$blastInstructionPathogen .= ' --query ' . $inFilenamePathogen;
			if($matchStrategyInterolog=="bm"){
				$blastInstructionPathogen .= ' --max-target-seqs 1 ';
			}
			$blastInstructionPathogen .= ' --evalue ' . $evaluePatho . ' --out  '. $outFilenameTabularPathogen.trim($databaseList[$i]).".txt" ;
			$blastInstructionPathogen .= ' --quiet --outfmt 6 qseqid sseqid pident length mismatch gapopen qstart qend sstart send evalue bitscore qcovhsp qcovhsp';
			$blastInstructionPathogen .= " --threads 4 ";

			//echo($blastInstructionPathogen);
			exec($blastInstructionPathogen, $outputPathogen, $return_varPathogen);
			/*
			} else {

				//$sedCommand = " sed 's/inputfile/".$inFilenamePathogen."/g;s/database/".$databaseList[$i]."/g;s/defevalue/".$evaluePatho."/g;s/outfile/".$outFilenameTabularPathogenLink.trim($databaseList[$i]).".txt/g;' /var/www/html/PredHPI/SLURM/diamond-interolog.sl > ".$slurmPathogen;
				$diamondChangerPathogen = "/var/www/html/PredHPI/SLURM/diamondChanger.sh ".$inFilenamePathogenLinkBase." ".trim($databaseList[$i])." ".$evaluePatho." ".$outFilenameTabularPathogenBase.trim($databaseList[$i]).".txt ".$slurmPathogen;
				//$sshComand = " ssh user@biocluster.usu.edu 'sbatch --wait ".$slurmPathogen." ' ";
				echo $ssh->exec('pwd');
				echo $ssh->exec('ls -la');

				copy($inFilenamePathogen, $inFilenamePathogenLink);
				exec($diamondChangerPathogen);
				//exec($sshComandPathogen);
				copy($outFilenameTabularPathogenLink.trim($databaseList[$i]).".txt ", $outFilenameTabularPathogen.trim($databaseList[$i]).".txt ");
			}
			*/
			
		}

		
		$query = 'SELECT * FROM '.$databaseList[$i];
		$whereClause=' WHERE';

		$interactorAquery= "";
		$interactorBquery= "";

		if($interologType <= 2){

			$whereClause.=' interactor_A IN ("';
			



			$fileTabularHost = fopen($outFilenameTabularHost.trim($databaseList[$i]).".txt","r");

			while(! feof($fileTabularHost)){
			    $line = fgets($fileTabularHost);
			    if($line != false && $line != '\n' && trim($line) != ''){
			        $separatedLine = explode("\t",$line);
			        if($separatedLine[2] >= $identity && $separatedLine[12] >= $coverage){
			        	$interactorAquery.= $separatedLine[1].'","' ;

			        	if($hostMapBlast[$separatedLine[1]]==""){
			        		$hostMapBlast[$separatedLine[1]]= $separatedLine[0];
			        	} else {
			        		$hostMapBlast[$separatedLine[1]]= $hostMapBlast[$separatedLine[1]].",".$separatedLine[0];
			        	}

			        	$hostNumberSequences +=1;
			        } 
			    }
		    }

		    $whereClause.= $interactorAquery;

		    if($whereClause[strlen($whereClause)-2] == '('){
		    	$whereClause .=' "';
		    } else if($whereClause[strlen($whereClause)-1] == '"'){
		    	$whereClause = substr($whereClause,0,strlen($whereClause)-2);
		    }

		    $whereClause .= ')';

			fclose($fileTabularHost);
			if($interologType == 1){
				$query.= $whereClause;
				#echo($query);
				if($hostNumberSequences > 0){
					$result = mysqli_query($link,$query) or die('Query failed: ' . mysqli_error($link));
				    
					while ($lineQueryResult = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
						
						$full_query_A = $hostMapBlast[$lineQueryResult['interactor_A']];

					    $queries_A = explode(",",$full_query_A);

					    for ($w = 0; $w < sizeof($queries_A); $w++) {

					    	if($queries_A[$w] != ""){
					    		$preformatted_query_A = $queries_A[$w];
								$preformatted_hit_A = $lineQueryResult['interactor_A'];
								$preformatted_query_B = $lineQueryResult['interactor_B'];

								$query_A = "";
								$hit_A = "";
								$query_B = "";



								if(strpos($preformatted_query_A,"uniprotkb:") !== false){
									$auxFormatter = explode("uniprotkb:",$preformatted_query_A);
									$query_A = $auxFormatter[1];
								} else {
									$query_A = $preformatted_query_A;
								}

								if(strpos($preformatted_hit_A,"uniprotkb:") !== false){
									$auxFormatter = explode("uniprotkb:",$preformatted_hit_A);
									$hit_A = $auxFormatter[1];
								} else {
									$hit_A = $preformatted_hit_A;
								}

								if(strpos($preformatted_query_A,"entrez_gene/locuslink:") !== false){
									$auxFormatter = explode("entrez_gene/locuslink:",$preformatted_query_A);
									$query_A = $auxFormatter[1];
								}
								if(strpos($preformatted_hit_A,"entrez_gene/locuslink:") !== false){
									$auxFormatter = explode("entrez_gene/locuslink:",$preformatted_hit_A);
									$hit_A = $auxFormatter[1];
								}





								if(strpos($preformatted_query_B,"uniprotkb:") !== false){
									$auxFormatter = explode("uniprotkb:",$preformatted_query_B);
									$query_B = $auxFormatter[1];
								} else {
									$query_B = $preformatted_query_B;
								}


								if(strpos($preformatted_query_B,"entrez_gene/locuslink:") !== false){
									$auxFormatter = explode("entrez_gene/locuslink:",$preformatted_query_B);
									$query_B = $auxFormatter[1];
								}




														
								$lineInterologResult = $query_A ."\t". $query_B ."\t". $hit_A." with ".$query_B ."\t". implode("\t",$lineQueryResult) ."\t". trim($databaseList[$i]);
								fwrite($infoTabularFile, $lineInterologResult);
								fwrite($infoTabularFile, PHP_EOL);


								$nodeHostArray = array('id' => $query_A, 'hitName' => $hit_A, 'hitTaxon' => $lineQueryResult['protein_taxid_A'], 'typeColor' => '#004085');
								$nodePathogenArray = array('id' => $query_B, 'hitName' => $query_B, 'hitTaxon' => $lineQueryResult['protein_taxid_B'], 'typeColor' => '#ff5d48');
								$edgeArray = array('id' => $hit_A."_".$query_A." with ".$hit_B."_".$query_B."_".trim($databaseList[$i]), 'name' => $hit_A." pp ".$query_B, 'source' => $query_A, 'target' => $query_B, 'interactionType' => str_replace('"','',$lineQueryResult['interaction_type']), 'detectionMethod' => str_replace('"','',$lineQueryResult['detection_method']), 'authorName' => str_replace('"','',$lineQueryResult['author_name']), 'database' => trim($databaseList[$i]), 'databaseColor'=> $databaseColor);

								if(!in_array($query_A,$nodesAdded)){

									$netNodes[] = array('data' => $nodeHostArray);
									array_push($nodesAdded,$query_A);
								}

								if(!in_array($query_B,$nodesAdded)){
									$netNodes[] = array('data' => $nodePathogenArray);
									array_push($nodesAdded,$query_B);
								}
								$netEdges[] = array('data' => $edgeArray);
					    	}

					    	
					    }
	


					}
					
					// Free resultset
					mysqli_free_result($result);
				}
			    
				
			}
			
		}

		if($interologType >= 2){ 


			$interactorBquery.="";

			if($interologType == 2){
				$whereClause.=' AND interactor_B IN ("';
			} else if($interologType == 3){
				$whereClause.=' interactor_B IN ("';
			}

			$fileTabularPathogen = fopen($outFilenameTabularPathogen.trim($databaseList[$i]).".txt","r");

			while(! feof($fileTabularPathogen)){
			    $line = fgets($fileTabularPathogen);
			    if($line != false && $line != '\n' && trim($line) != ''){
			        $separatedLine = explode("\t",$line);
			        if($separatedLine[2] >= $identityPatho && $separatedLine[12] >= $coveragePatho){
			        	$interactorBquery.= $separatedLine[1].'","' ;

			        	if($pathogenMapBlast[$separatedLine[1]]==""){
			        		$pathogenMapBlast[$separatedLine[1]]= $separatedLine[0];
			        	} else {
			        		$pathogenMapBlast[$separatedLine[1]]= $pathogenMapBlast[$separatedLine[1]].",".$separatedLine[0];
			        	}

			        	$pathogenNumberSequences +=1;
			        } 
			    }
		    }

		    $whereClause .= $interactorBquery;

		    if($whereClause[strlen($whereClause)-2] == '('){
		    	$whereClause .=' "';
		    } else if($whereClause[strlen($whereClause)-1] == '"'){
		    	$whereClause = substr($whereClause,0,strlen($whereClause)-2);
		    }

		    $whereClause .= ')';

		    if($interologType == 2){
			    $whereClause .=' OR ( interactor_A IN ("'.$interactorBquery.'")';
	   			$whereClause .=' AND interactor_B IN ("'.$interactorAquery.'") )';
			}


			fclose($fileTabularPathogen);

			$query.= $whereClause;
			//echo($query);
			if($pathogenNumberSequences > 0){
				$result = mysqli_query($link,$query) or die('Query failed: ' . mysqli_error($link));
		    
				while ($lineQueryResult = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

					$full_query_A = $hostMapBlast[$lineQueryResult['interactor_A']];
					$full_query_B = $pathogenMapBlast[$lineQueryResult['interactor_B']];


					if($interologType == 2){
						$full_query_A1 = $hostMapBlast[$lineQueryResult['interactor_A']];
						$full_query_B1 = $pathogenMapBlast[$lineQueryResult['interactor_B']];

						$full_query_A2 = $hostMapBlast[$lineQueryResult['interactor_A']];
						$full_query_B2 = $pathogenMapBlast[$lineQueryResult['interactor_B']];

						$full_query_A = $full_query_A1.",".$full_query_A2;
						$full_query_B = $full_query_B1.",".$full_query_B2;
					}



					if($interologType == 3){
						$full_query_A = $lineQueryResult['interactor_A'];
						$full_query_B = $pathogenMapBlast[$lineQueryResult['interactor_B']];
					}

					#echo($full_query_A)
					#echo($full_query_B)

				    $queries_A = explode(",",$full_query_A);
				    $queries_B = explode(",",$full_query_B);





				    if(sizeof($queries_A)==0){
				    	$queries_A=array();
				    	array_push($queries_A,$full_query_A);
				    }

				    if(sizeof($queries_B)==0){
				    	$queries_B=array();
				    	array_push($queries_B,$full_query_B);
				    }


				    for ($w = 0; $w < sizeof($queries_A); $w++) {

				    	for ($y = 0; $y < sizeof($queries_B); $y++) {

				    		if($queries_A[$w] && $queries_B[$y] != ""){
					    		$preformatted_query_A = $queries_A[$w];
								$preformatted_hit_A = $lineQueryResult['interactor_A'];
								$preformatted_query_B = $queries_B[$y];
								$preformatted_hit_B = $lineQueryResult['interactor_B'];

								if($interologType == 3){
									$preformatted_query_A = $lineQueryResult['interactor_A'];
								}

								$query_A = "";
								$hit_A = "";
								$query_B = "";
								$hit_B = "";

								if(strpos($preformatted_query_A,"uniprotkb:") !== false){
									$auxFormatter = explode("uniprotkb:",$preformatted_query_A);
									$query_A = $auxFormatter[1];
								} else {
									$query_A = $preformatted_query_A;
								}

								if(strpos($preformatted_hit_A,"uniprotkb:") !== false){
									$auxFormatter = explode("uniprotkb:",$preformatted_hit_A);
									$hit_A = $auxFormatter[1];
								} else {
									$hit_A = $preformatted_hit_A;
								}

								if(strpos($preformatted_query_B,"uniprotkb:") !== false){
									$auxFormatter = explode("uniprotkb:",$preformatted_query_B);
									$query_B = $auxFormatter[1];
								} else {
									$query_B = $preformatted_query_B;
								}


								if(strpos($preformatted_hit_B,"uniprotkb:") !== false){
									$auxFormatter = explode("uniprotkb:",$preformatted_hit_B);
									$hit_B = $auxFormatter[1];
								} else {
									$hit_B = $preformatted_hit_B;
								}


								if(strpos($preformatted_query_A,"entrez_gene/locuslink:") !== false){
									$auxFormatter = explode("entrez_gene/locuslink:",$preformatted_query_A);
									$query_A = $auxFormatter[1];
								}
								if(strpos($preformatted_hit_A,"entrez_gene/locuslink:") !== false){
									$auxFormatter = explode("entrez_gene/locuslink:",$preformatted_hit_A);
									$hit_A = $auxFormatter[1];
								}

								if(strpos($preformatted_query_B,"entrez_gene/locuslink:") !== false){
									$auxFormatter = explode("entrez_gene/locuslink:",$preformatted_query_B);
									$query_B = $auxFormatter[1];
								}


								if(strpos($preformatted_hit_B,"entrez_gene/locuslink:") !== false){
									$auxFormatter = explode("entrez_gene/locuslink:",$preformatted_hit_B);
									$hit_B = $auxFormatter[1];
								}

						
								$lineInterologResult = $query_A ."\t". $query_B ."\t". $hit_A." with ".$hit_B ."\t".implode("\t",$lineQueryResult) ."\t". trim($databaseList[$i]);

								fwrite($infoTabularFile, $lineInterologResult);
								fwrite($infoTabularFile, PHP_EOL);

								$simpleInterologResult = $query_A ."\t". $query_B ."\t". $hit_A." with ".$hit_B ."\t". trim($databaseList[$i]);

								if(!in_array($simpleInterologResult,$resultsUnique)){



									$nodeHostArray = array('id' => $query_A, 'hitName' => $hit_A, 'hitTaxon' => $lineQueryResult['protein_taxid_A'], 'typeColor' => '#004085');
									$nodePathogenArray = array('id' => $query_B, 'hitName' => $hit_B, 'hitTaxon' => $lineQueryResult['protein_taxid_B'], 'typeColor' => '#ff5d48');
									$edgeArray = array('id' => $hit_A."_".$query_A." with ".$hit_B."_".$query_B."_".trim($databaseList[$i]), 'name' => $hit_A." pp ".$hit_B, 'source' => $query_A, 'target' => $query_B, 'interactionType' => $lineQueryResult['interaction_type'], 'detectionMethod' => $lineQueryResult['detection_method'], 'authorName' => $lineQueryResult['author_name'], 'database' => trim($databaseList[$i]), 'databaseColor'=> $databaseColor);

									if(!in_array($query_A,$nodesAdded)){
										$netNodes[] = array('data' => $nodeHostArray);
										array_push($nodesAdded,$query_A);
									}

									if(!in_array($query_B,$nodesAdded)){
										$netNodes[] = array('data' => $nodePathogenArray);
										array_push($nodesAdded,$query_B);
									}
									$netEdges[] = array('data' => $edgeArray);
									array_push($resultsUnique,$simpleInterologResult);
								}
				    		}

				    	}

						
					}

				}
				
				// Free resultset
				mysqli_free_result($result);
			}
		    
		
		}
		unlink($outFilenameTabularHost);
		unlink($outFilenameTabularPathogen);
		
		
	}
	
	mysqli_close($link);

		// Code to make thw writing of the results, I should map the blast query ids to the mysql queries
	//print_r($query);
	fclose($infoTabularFile);

	$netElements= array('nodes' => $netNodes,'edges' => $netEdges);

	$netFile = fopen($outNetFilename, "w") or die("Unable to open file!");
	
	fwrite($netFile, json_encode($netElements));
	fclose($netFile);


	if($emailAddress != "noemail"){
		$msgEmail = "Your Interolog prediction job at PredHPI is done! \nPlease go to http://bioinfo.usu.edu/PredHPI/interolog-results.php?result=$namer to see it.";
		$msgEmail = wordwrap($msgEmail,70);
		$from = "noreply@bioinfo.biotec.usu.edu";
		$headers = "From: $from"; 
		$mail= mail($emailAddress,"Interolog PredHPI results",$msgEmail,$headers,'-f '.$from);
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

