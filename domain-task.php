<?php
// Start the session
header("Access-Control-Allow-Origin: *");


ini_set('display_errors', 0);
error_reporting(0);

include('assets/Net/SSH2.php');


$noFilters= $_POST['noFilters'];
$noFiltersP= $_POST['noFiltersP'];

$forward= $_POST['forward'];
$viterbi= $_POST['viterbi'];
$iddiScore= $_POST['iddiScore'];
$MSV= $_POST['MSV'];

$evalue= $_POST['evalue'];
$evalueP= $_POST['evalueP'];

$domE= $_POST['coverageDomain'];
$domEP= $_POST['coverageDomainP'];

$database= $_POST['database'];


//$fileSeqHost= $_POST['fileSeqHost'];
$areaSeqHost= $_POST['areaSeqHost'];
//$fileSeqPathogen= $_POST['fileSeqPathogen'];
$areaSeqPathogen= $_POST['areaSeqPathogen'];


$emailAddress= $_POST['emailAddress'];
$domainPredType= $_POST['type'];


$namer = $_POST['namer'];

$inFilenameHost= '/var/www/html/PredHPI/tmp/' . $namer . '_HostHmmerInput.txt';
$inFilenamePathogen= '/var/www/html/PredHPI/tmp/' . $namer . '_PathogenHmmerInput.txt';

# Hmmer domain result, must parser to eliminate the lines that start with #
$outFilenameTabularHost= '/var/www/html/PredHPI/tmp/' . $namer . '_HostHmmerOutput.txt';
$outFilenameTabularPathogen= '/var/www/html/PredHPI/tmp/' . $namer . '_PathogenHmmerOutput.txt';


$inFilenameHostLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_HostHmmerInput.txt';
$inFilenamePathogenLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_PathogenHmmerInput.txt';

$inFilenameHostLinkBase= $namer . '_HostHmmerInput.txt';
$inFilenamePathogenLinkBase= $namer . '_PathogenHmmerInput.txt';

$slurmHost= '/var/www/html/PredHPI/SLURM/' . $namer . '_Host-dbased.sl';
$slurmPathogen= '/var/www/html/PredHPI/SLURM/' . $namer . '_Pathogen-dbased.sl';

$slurmHostLink= '/home/user/PredHPI_SLURM/' . $namer . '_Host-dbased.sl';
$slurmPathogenLink= '/home/user/PredHPI_SLURM/' . $namer . '_Pathogen-dbased.sl';

$outFilenameTabularHostBase= $namer . '_HostHmmerOutput.txt';
$outFilenameTabularPathogenBase= $namer . '_PathogenHmmerOutput.txt';

$outFilenameTabularHostLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_HostHmmerOutput.txt';
$outFilenameTabularPathogenLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_PathogenHmmerOutput.txt';


$outFilenameTabularHostFix= '/var/www/html/PredHPI/tmp/' . $namer . '_HostHmmerOutputFix.txt';
$outFilenameTabularPathogenFix= '/var/www/html/PredHPI/tmp/' . $namer . '_PathogenHmmerOutputFix.txt';

$outInfoFilename= '/var/www/html/PredHPI/tmp/' . $namer . '_DomainPredTaskInfo.txt';
$outTabularFilename= '/var/www/html/PredHPI/tmp/' . $namer . '_DomainPredTabularInfo.txt';
$outNetFilename= '/var/www/html/PredHPI/tmp/' . $namer . '.json';

$databaseList = explode("\n",$database);
array_pop($databaseList);
//echo("\n");

$ssh = new Net_SSH2('biocluster.usu.edu', 22);
if (!$ssh->login('user', 'password')) {
    exit('Login Failed');
}

$ssh->setTimeout(false);

function testFasta($fastaText) {
    $checkPass  = true;
    $protSeq = false;
    $fastasequences = $fastaText;
    $checkStatus = 0;
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

if($domainPredType <= 2){
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

if($domainPredType >= 2){
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


if($correctFastaFileHost > 5 && $domainPredType <= 2){
	$proceedHost = true;
	//echo "Proceed pathogen ". $proceedHost ."\n";
}

if($correctFastaFilePathogen > 5 && $domainPredType >= 2 ){
	$proceedPathogen = true;
	//echo "Proceed pathogen ". $proceedPathogen ."\n";

}

if($domainPredType == 1){
	$proceed = $proceedHost;
	if(!$proceedHost){
		$errorFasta = 1;
	}
} else if($domainPredType == 2){
	$proceed = $proceedHost && $proceedPathogen;
	if(!$proceedHost && !$proceedPathogen){
		$errorFasta = 2;
	}else if(!$proceedHost){
		$errorFasta = 1;
	}else if(!$proceedPathogen){
		$errorFasta = 3;
	}
} else if($domainPredType == 3){
	$proceed = $proceedPathogen;
	if(!$proceedPathogen){
		$errorFasta = 3;
	}
}
//echo "DomainPred ". $domainPredType . "\n";
//echo "Proceed ";
//echo $proceed ? 'true' : 'false';

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
	if($domainPredType == 1){
		$infoText = "DomainPred Type: Only Host\n";
	} else if($domainPredType == 2){
		$infoText = "DomainPred Type: Host and Pathogen\n";
	} else if($domainPredType == 3){
		$infoText = "DomainPred Type: Only Pathogen\n";
	}
	
	fwrite($infoTaskFile, $infoText);

	$infoText = "Expected value: $evalue\n";
	fwrite($infoTaskFile, $infoText);
	
	$infoText = "Email address provided: $emailAddress\n\n";
	fwrite($infoTaskFile, $infoText);
	
	fclose($infoTaskFile);



	$link = mysqli_connect('127.0.0.01:3306', 'user', 'password') or die('Could not connect: ' . mysqli_error($link));
	#echo ('Connected successfully');
	mysqli_select_db($link,'database') or die('Could not select database');
	$hostDBresults = array();
	$pathogenDBresults = array();

	### Arrays to create json object
	$netNodes = array();
	$netEdges = array();
	$nodesAdded = array();
	$netElements = array();

	$hmmerInstructionHost = 'hmmscan --cpu 4 ';
	$hmmerInstructionPathogen = 'hmmscan --cpu 4 ';
	$options=" --noali ";

	if($noFilters=="noFilters"){
		$hmmerInstructionHost .= ' --max ';
		$options=" --max ";
	} else {
		$hmmerInstructionHost .= ' --F1 '.$MSV;
		$hmmerInstructionHost .= ' --F2 '.$viterbi;
		$hmmerInstructionHost .= ' --F3 '.$forward;
	}

	$hmmerInstructionHost .= ' -E '.$evalue;
	$hmmerInstructionHost .= ' --domE '.$domE;


	if($noFiltersP=="noFilters"){
		$hmmerInstructionPathogen .= ' --max ';
		$options=" --max ";
	} else {
		$hmmerInstructionPathogen .= ' --F1 '.$MSV;
		$hmmerInstructionPathogen .= ' --F2 '.$viterbi;
		$hmmerInstructionPathogen .= ' --F3 '.$forward;
	}

	$hmmerInstructionPathogen .= ' -E '.$evalueP;
	$hmmerInstructionPathogen .= ' --domE '.$domEP;


	if($domainPredType <= 2){
		if($typeSeqHost == "text"){
			$fastafileHost = fopen($inFilenameHost, "w") or die("Unable to open file!");
			$fastaTxtHost = $hostSequence;
			fwrite($fastafileHost, $fastaTxtHost);
			fclose($fastafileHost);
		}



		$hmmerChangerHost = "/var/www/html/PredHPI/SLURM/hmmerChanger.sh ".$inFilenameHostLinkBase." ".$options." ".$evalue." ".$outFilenameTabularHostBase;

		copy($inFilenameHost, $inFilenameHostLink);
		exec($hmmerChangerHost,$outputSlurmHFile,$return_varHostS);

		file_put_contents($slurmHost, implode("\n",$outputSlurmHFile));

		$ssh->exec('sbatch --wait '.$slurmHostLink);

		$quickParserHost = 'grep -v "^#" '.$outFilenameTabularHostLink;
		exec($quickParserHost, $outputFixHostHmmer,$return_varHost);

		file_put_contents($outFilenameTabularHostFix, implode("\n",$outputFixHostHmmer));
	}


	if($domainPredType >= 2){
		if($typeSeqPathogen == "text"){
			$fastafilePathogen = fopen($inFilenamePathogen, "w") or die("Unable to open file!");
			$fastaTxtPathogen = $pathogenSequence;
			fwrite($fastafilePathogen, $fastaTxtPathogen);
			fclose($fastafilePathogen);
		}

		$hmmerChangerPathogen = "/var/www/html/PredHPI/SLURM/hmmerChanger.sh ".$inFilenamePathogenLinkBase." ".$options." ".$evalue." ".$outFilenameTabularPathogenBase;

		copy($inFilenamePathogen, $inFilenamePathogenLink);
		exec($hmmerChangerPathogen,$outputSlurmPFile,$return_varPathogenS);

		file_put_contents($slurmPathogen, implode("\n",$outputSlurmPFile));

		$ssh->exec('sbatch --wait '.$slurmPathogenLink);

		$quickParserPathogen = 'grep -v "^#" '.$outFilenameTabularPathogenLink;
		exec($quickParserPathogen, $outputFixPathogenHmmer,$return_varPathogen);

		file_put_contents($outFilenameTabularPathogenFix, implode("\n",$outputFixPathogenHmmer));

	}

	## TODO, differetiation between the two databases search
	
	for ($i = 0; $i < sizeof($databaseList); $i++) {

		$hostNumberSequences = 0;
		$pathogenNumberSequences = 0;
		$numberSequences = 0;

		$hostMapHmmer = array();
		$pathogenMapHmmer = array();
		$mapHmmer = array();

		$hostMapDesc = array();
		$pathogenMapDesc = array();
		$mapDesc = array();

		$databaseIdIndex = -1;
		$databaseColor = '#7fdb6a';
		//echo(trim($databaseList[$i]));
		//echo("\n");

		if(trim($databaseList[$i])=="DDI1"){
			$databaseColor = '#7fdb6a';
			$databaseIdIndex = 0;
		} else if(trim($databaseList[$i])=="iPfam"){
			$databaseColor = '#7fdb6a';
			$databaseIdIndex = 2;
		} else if(trim($databaseList[$i])=="iddi"){
			$databaseColor = '#7fdb6a';
			$databaseIdIndex = 1;
		} 

		$query = 'SELECT * FROM '.$databaseList[$i];
		$whereClause=' WHERE ';
		$regexpresionDA = '"';
		$regexpresionDB = '"';

		#if($domainPredType == 1 || $domainPredType == 3){

		$fileTabular = "";
		## For host 
		if($domainPredType <= 2){
			$fileTabular = fopen($outFilenameTabularHostFix,"r");
		} else if($domainPredType == 3){
			$fileTabular = fopen($outFilenameTabularPathogenFix,"r");
		}

		while(! feof($fileTabular)){
		    $line = fgets($fileTabular);
		    if($line != false && $line != '\n' && trim($line) != ''){
		        $separatedLine = preg_split('/\s+/', $line);
		        $pFamId= "";

		        if($databaseIdIndex == 1){
		        	$pfamIdTmpArray= explode(".",$separatedLine[$databaseIdIndex]);

		        	$pFamId=$pfamIdTmpArray[0];

		        } else {
		        	$pFamId= $separatedLine[$databaseIdIndex];

		        }

		        $description = "";
	        	for ($j = 18; $j < sizeof($separatedLine); $j++) {
				    $description.=$separatedLine[$j].' ';
				}

				$mapDesc[$pFamId]= $description;

	        	$regexpresionDA.= $pFamId.'","' ;

	        	if($mapHmmer[$pFamId]==""){
	        		$mapHmmer[$pFamId]= $separatedLine[2];
	        	} else {
	        		$mapHmmer[$pFamId]= $mapHmmer[$pFamId].",".$separatedLine[2];
	        	}
	        	
	        	$numberSequences +=1;

		    }
	    }

		if($regexpresionDA[strlen($regexpresionDA)-1] == '"'){
	    	$regexpresionDA = substr($regexpresionDA,0,strlen($regexpresionDA)-2);
	    }
	    //echo(json_encode($mapHmmer));
	    fclose($fileTabular);


	    // Building String Query for Domain Pred Host & Pathogen
	    if($domainPredType == 1){
	    	$whereClause.= ' domain1 IN ('.$regexpresionDA.')';

	    } else if($domainPredType == 3){
			$whereClause.= ' domain2 IN ('.$regexpresionDA.')';

	    } else {
	    	$fileTabular = fopen($outFilenameTabularPathogenFix,"r");
	    	$hostMapHmmer = $mapHmmer;
	    	$hostMapDesc = $mapDesc;
	    	$hostNumberSequences = $numberSequences;

	    	while(! feof($fileTabular)){
			    $line = fgets($fileTabular);
			    if($line != false && $line != '\n' && trim($line) != ''){
			        $separatedLine = preg_split('/\s+/', $line);
			        $pFamId= "";

			        if($databaseIdIndex == 1){
			        	$pfamIdTmpArray= explode(".",$separatedLine[$databaseIdIndex]);
			        	$pFamId=$pfamIdTmpArray[0];

			        } else {
			        	$pFamId= $separatedLine[$databaseIdIndex];

			        }

			        $description = "";
		        	for ($j = 18; $j < sizeof($separatedLine); $j++) {
					    $description.=$separatedLine[$j].' ';
					}

					$pathogenMapDesc[$pFamId]= $description;

		        	$regexpresionDB.= $pFamId.'","' ;

		        	if($pathogenMapHmmer[$pFamId]==""){
		        		$pathogenMapHmmer[$pFamId]= $separatedLine[2];
		        	} else {
		        		$pathogenMapHmmer[$pFamId]= $pathogenMapHmmer[$pFamId].",".$separatedLine[2];
		        	}
		        	$pathogenNumberSequences +=1;

			    }
		    }

		    fclose($fileTabular);


		    if($regexpresionDB[strlen($regexpresionDB)-1] == '"'){
		    	$regexpresionDB = substr($regexpresionDB,0,strlen($regexpresionDB)-2);
		    }

		    $whereClause.= ' (domain1 IN ('.$regexpresionDA;
		    $whereClause.=') AND domain2 IN ('.$regexpresionDB .') )';
		    $whereClause.=' OR (domain1 IN ('.$regexpresionDB .') AND domain2 IN ('.$regexpresionDA.") )";

	    }

	    $query.= $whereClause;
	    //echo($query);

	    ##Execute query if you have domains detected
	    

	    if( ( ($domainPredType == 1 || $domainPredType == 3) && $numberSequences > 0) || (($hostNumberSequences > 0 && $pathogenNumberSequences > 0) && $domainPredType == 2) ){

	    	$result = mysqli_query($link,$query) or die('Query failed: ' . mysqli_error($link));
	    	$infoTabularFile = fopen($outTabularFilename, "w") or die("Unable to open file!");

	    	while ($lineQueryResult = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

	    		$query_A = "";
	    		$query_B = "";
	    		$hit_A = "";
	    		$hit_B = "";
	    		$description_A = "";
	    		$description_B = "";
	    		$scoreReliabilityPass = "true";

	    		if(trim($databaseList[$i])=="iddi"){
	    			if($lineQueryResult['PRED_SCORE'] < $iddiScore){
	    				$scoreReliabilityPass = "false";
	    			}
	    		}

	    		if($scoreReliabilityPass == "true"){
		    		if( ($domainPredType == 1 || $domainPredType == 3) && $numberSequences > 0){


		    			if($domainPredType == 1){
		    				$query_A = $mapHmmer[$lineQueryResult['domain1']];
		    				$hit_A = $lineQueryResult['domain1'];
		    				$hit_B = $lineQueryResult['domain2'];
		    				$query_B = $lineQueryResult['domain2'];
		    				$description_A = $mapDesc[$lineQueryResult['domain1']];
		    				$description_B = "";
		    			} else if($domainPredType == 3){
		    				$query_A = $lineQueryResult['domain1'];
		    				$query_B = $mapHmmer[$lineQueryResult['domain2']];
		    				$hit_A = $lineQueryResult['domain1'];
		    				$hit_B = $lineQueryResult['domain2'];
		    				$description_A = "";
		    				$description_B = $mapDesc[$lineQueryResult['domain2']];
		    			}


		    		} else {
		    			$query_A = $hostMapHmmer[$lineQueryResult['domain1']];
			    		$query_B = $pathogenMapHmmer[$lineQueryResult['domain2']];
			    		$hit_A = $lineQueryResult['domain1'];
			    		$hit_B = $lineQueryResult['domain2'];
			    		if(!($query_A != "" && $query_B != "")){
			    			$query_A = $hostMapHmmer[$lineQueryResult['domain2']];
			    			$query_B = $pathogenMapHmmer[$lineQueryResult['domain1']];
			    			$hit_A = $lineQueryResult['domain2'];
			    			$hit_B = $lineQueryResult['domain1'];
			    			$description_A = $hostMapDesc[$lineQueryResult['domain2']];
			    			$description_B = $pathogenMapDesc[$lineQueryResult['domain1']];

			    		} else {
			    			$description_A = $hostMapDesc[$lineQueryResult['domain1']];
			    			$description_B = $pathogenMapDesc[$lineQueryResult['domain2']];
			    		}


		    		}


	    		//echo("\n");
	    		//echo($lineQueryResult['domain1']." interacting with ".$lineQueryResult['domain2']);
	    		//echo("\n");
	    		/*
	    		echo("\n");
	    		echo($query_A." interacting with ".$query_B);
	    		echo("\n");
	    		echo($hit_A." interacting with ".$hit_B);
	    		echo("\n");
	    		*/


		    		if($query_A != "" && $query_B != ""){
		    			$domainInteraction = $hit_A." with ".$hit_B;
		    			//echo($domainInteraction);

						if (strpos($query_A, ',') !== false) {
						    $queries_A = explode(",",$query_A);

						    if (strpos($query_B, ',') !== false) {
						    	$queries_B = explode(",",$query_B);
						    	for ($w = 0; $w < sizeof($queries_A); $w++) {
							    	for ($y = 0; $y < sizeof($queries_B); $y++) {
									    $lineDomainPredResult = $queries_A[$w] ."\t". $queries_B[$y] ."\t". $domainInteraction ."\t". json_encode($description_A) ."\t".json_encode($description_B). "\t". implode("\t",$lineQueryResult) ."\t". trim($databaseList[$i]);
										fwrite($infoTabularFile, $lineDomainPredResult);
										fwrite($infoTabularFile, PHP_EOL);
									}
								}

						    } else {
						    	for ($w = 0; $w < sizeof($queries_A); $w++) {
								    $lineDomainPredResult = $queries_A[$w] ."\t". $query_B ."\t". $domainInteraction ."\t". json_encode($description_A) ."\t".json_encode($description_B). "\t". implode("\t",$lineQueryResult) ."\t". trim($databaseList[$i]);
								    fwrite($infoTabularFile, $lineDomainPredResult);
									fwrite($infoTabularFile, PHP_EOL);
								}
						    }
			
						} else if (strpos($query_B, ',') !== false) {
					    	$queries_B = explode(",",$query_B);

					    	for ($y = 0; $y < sizeof($queries_B); $y++) {
							    $lineDomainPredResult = $query_A ."\t". $queries_B[$y] ."\t". $domainInteraction ."\t". json_encode($description_A) ."\t".json_encode($description_B). "\t". implode("\t",$lineQueryResult) ."\t". trim($databaseList[$i]);
							    fwrite($infoTabularFile, $lineDomainPredResult);
								fwrite($infoTabularFile, PHP_EOL);
							}

					    } else {
							$lineDomainPredResult = $query_A ."\t". $query_B ."\t". $domainInteraction ."\t". json_encode($description_A) ."\t".json_encode($description_B). "\t". implode("\t",$lineQueryResult) ."\t". trim($databaseList[$i]);
							fwrite($infoTabularFile, $lineDomainPredResult);
							fwrite($infoTabularFile, PHP_EOL);
						}
		    		}
	    		

	    		}

	    	}

	    	fclose($infoTabularFile);


	    }


	}
	

	mysqli_close($link);

		// Code to make thw writing of the results, I should map the hmmer query ids to the mysql queries
	
	


	$nodeHostArray = array();
	$nodePathogenArray = array();

	for ($i = 0; $i < sizeof($databaseList); $i++) {
		$fileTabular = fopen($outTabularFilename,"r");
		
		if(trim($databaseList[$i])=="DDI1"){
			$databaseColor = '#7fdb6a';
			$databaseIdIndex = 0;
		} else if(trim($databaseList[$i])=="iPfam"){
			$databaseColor = '#7fdb6a';
			$databaseIdIndex = 2;
		} else if(trim($databaseList[$i])=="iddi"){
			$databaseColor = '#7fdb6a';
			$databaseIdIndex = 1;
		} 

		while(! feof($fileTabular)){
		    $line = fgets($fileTabular);
		    if($line != false && $line != '\n' && trim($line) != ''){
		        $separatedLine = explode("\t",$line);

		        $query_A = $separatedLine[0];
		        $query_B = $separatedLine[1];
		        $hit_A = $separatedLine[5];
		        $hit_B = $separatedLine[7];
		        $description_A = $separatedLine[3];
		        $description_B = $separatedLine[4];

		        $domainInteraction = $hit_A."_".$query_A." with ".$hit_B."_".$query_B;
		        //echo($description_A);#004085#ff5d48

				$nodeHostArray = array('id' => $query_A, 'hitName'=> $hit_A, 'description' => json_encode($description_A), 'typeColor' => '#004085');
				$nodePathogenArray = array('id' => $query_B, 'hitName'=> $hit_B, 'description' => json_encode($description_B), 'typeColor' => '#ff5d48');



				$edgeArray = array('id' => $domainInteraction."_".trim($databaseList[$i]), 'name' => $hit_A." dd ".$hit_B, 'source' => $query_A, 'target' => $query_B, 'database' => trim($databaseList[$i]), 'databaseColor'=> $databaseColor);

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
		fclose($fileTabular);

	}


	
	$netElements= array('nodes' => $netNodes,'edges' => $netEdges);

	$netFile = fopen($outNetFilename, "w") or die("Unable to open file!");
	
	fwrite($netFile, json_encode($netElements));
	fclose($netFile);
	


	if($emailAddress != "noemail"){
		$msgEmail = "Your DomainPred prediction job at PredHPI is done! \nPlease go to http://bioinfo.usu.edu/PredHPI/domain-results.php?result=$namer to see it.";
		$msgEmail = wordwrap($msgEmail,70);
		$from = "noreply@bioinfo.biotec.usu.edu";
		$headers = "From: $from"; 
		$mail= mail($emailAddress,"DomainPred PredHPI results",$msgEmail,$headers,'-f '.$from);
		if($mail){
		  //echo "Email sent";
		}else{
		  //echo "Something went wrong with Mail."; 
		}
	}
	print_r($namer);
	//print_r($query);

} else {
	echo("fastaerror-".$errorFasta);
}

if($domainPredType == 1){

	unlink($inFilenameHost);
	unlink($outFilenameTabularHost);
	unlink($outFilenameTabularHostFix);
	unlink($inFilenameHostLink);
	unlink($slurmHost);
	unlink($outFilenameTabularHostLink);


} else if($domainPredType == 2){

	unlink($inFilenameHost);
	unlink($inFilenamePathogen);

	unlink($outFilenameTabularHost);
	unlink($outFilenameTabularPathogen);

	unlink($outFilenameTabularHostFix);
	unlink($outFilenameTabularPathogenFix);

	unlink($inFilenameHostLink);
	unlink($inFilenamePathogenLink);

	unlink($slurmHost);
	unlink($slurmPathogen);
	unlink($outFilenameTabularHostLink);
	unlink($outFilenameTabularPathogenLink);



} else if($domainPredType == 3){

	unlink($inFilenamePathogen);
	unlink($outFilenameTabularPathogen);
	unlink($outFilenameTabularPathogenFix);
	unlink($inFilenamePathogenLink);
	unlink($slurmPathogen);
	unlink($outFilenameTabularPathogenLink);
}









//print_r($_FILES);

//echo($_POST['evalue']);

?>

