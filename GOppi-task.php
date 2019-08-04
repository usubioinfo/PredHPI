<?php
// Start the session
header("Access-Control-Allow-Origin: *");


ini_set('display_errors', 0);
error_reporting(0);

include('assets/Net/SSH2.php');


$threshold= $_POST['threshold'];
$combineMethod= $_POST['combineMethod'];
$orgDB= $_POST['orgDB'];


//$fileSeqHost= $_POST['fileSeqHost'];
$areaSeqHost= $_POST['areaSeqHost'];
//$fileSeqPathogen= $_POST['fileSeqPathogen'];
$areaSeqPathogen= $_POST['areaSeqPathogen'];


$emailAddress= $_POST['emailAddress'];


$namer = $_POST['namer'];

$inFilenameHost= '/var/www/html/PredHPI/tmp/' . $namer . '_HostInterproInput.txt';
$inFilenamePathogen= '/var/www/html/PredHPI/tmp/' . $namer . '_PathogenInterproInput.txt';

# Intepro GO mapping
$outFilenameTabularHost= '/var/www/html/PredHPI/tmp/' . $namer . '_HostInterproOutput.txt';
$outFilenameTabularPathogen= '/var/www/html/PredHPI/tmp/' . $namer . '_PathogenInterproOutput.txt';


$inFilenameHostLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_HostInterproInput.txt';
$inFilenamePathogenLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_PathogenInterproInput.txt';

$inFilenameHostLinkBase= $namer . '_HostInterproInput.txt';
$inFilenamePathogenLinkBase= $namer . '_PathogenInterproInput.txt';

$slurmHost= '/var/www/html/PredHPI/SLURM/' . $namer . '_Host-GObased.sl';
$slurmPathogen= '/var/www/html/PredHPI/SLURM/' . $namer . '_Pathogen-GObased.sl';
$slurmGOsemsim= '/var/www/html/PredHPI/SLURM/' . $namer . '_gosemsim.sl';

$slurmHostLink= '/home/user/PredHPI_SLURM/' . $namer . '_Host-GObased.sl';
$slurmPathogenLink= '/home/user/PredHPI_SLURM/' . $namer . '_Pathogen-GObased.sl';
$slurmGOsemsimLink= '/home/user/PredHPI_SLURM/' . $namer . '_gosemsim.sl';

//$outFilenameTabularHostL= '/home/user/PredHPI_SLURM/' . $namer . '_HostInterproOutput.txt';
//$outFilenameTabularPathogenL= '/home/user/PredHPI_SLURM/' . $namer . '_Pathogen-GObased.txt';

//$outTabularFilenameLink= '/home/user/PredHPI_SLURM/' . $namer . '_GOppiPredTabularInfo.txt';


$outFilenameTabularHostBase= $namer . '_HostInterproOutput.txt';
$outFilenameTabularPathogenBase= $namer . '_PathogenInterproOutput.txt';

$outFilenameTabularHostLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_HostInterproOutput.txt';
$outFilenameTabularPathogenLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_PathogenInterproOutput.txt';



$outInfoFilename= '/var/www/html/PredHPI/tmp/' . $namer . '_GOppiPredTaskInfo.txt';
$outTabularFilename= '/var/www/html/PredHPI/tmp/' . $namer . '_GOppiPredTabularInfo.txt';
$outTabularFilenameLink= '/var/www/html/PredHPI/SLURM/' . $namer . '_GOppiPredTabularInfo.txt';
$outTabularFilenameBase= $namer . '_GOppiPredTabularInfo.txt';
$outNetFilename= '/var/www/html/PredHPI/tmp/' . $namer . '.json';

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



if (!copy($_FILES['fileSeqHost']['tmp_name'], $inFilenameHost)) {
    //echo "Unable to open file!...\n";
    $hostSequence = $areaSeqHost;
    $typeSeqHost = "text";
} else {
	$hostSequence = file_get_contents($inFilenameHost);
	$typeSeqHost = "file";
}

$correctFastaFileHost = testFasta($hostSequence);



if (!copy($_FILES['fileSeqPathogen']['tmp_name'], $inFilenamePathogen)) {
    //echo "Unable to open file!...\n";
    $pathogenSequence = $areaSeqPathogen;
    $typeSeqPathogen = "text";
} else {
	$pathogenSequence = file_get_contents($inFilenamePathogen);
	$typeSeqPathogen = "file";
}
$correctFastaFilePathogen = testFasta($pathogenSequence);



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

//echo "GOppiPred ". $domainPredType . "\n";
//echo "Proceed ";
//echo $proceed ? 'true' : 'false';

if($proceed){

	$infoTaskFile = fopen($outInfoFilename, "w") or die("Unable to open file!");
	
	$infoText = "GO annotation DB: ".$orgDB."\n";
	fwrite($infoTaskFile, $infoText);

	$infoText = "Combine similarities strategy per GO term pair calculation: ".$combineMethod."\n";
	fwrite($infoTaskFile, $infoText);

	$infoText = "Threshold: ".$threshold."\n";
	fwrite($infoTaskFile, $infoText);
	
	$infoText = "Email address provided: $emailAddress\n\n";
	fwrite($infoTaskFile, $infoText);
	
	fclose($infoTaskFile);



	$interproChangerHost = "/var/www/html/PredHPI/SLURM/interproChanger.sh ".$inFilenameHostLinkBase." ".$outFilenameTabularHostBase;

	copy($inFilenameHost, $inFilenameHostLink);
	exec($interproChangerHost,$outputSlurmHFile,$return_varHostS);

	file_put_contents($slurmHost, implode("\n",$outputSlurmHFile));

	$ssh->exec('sbatch --wait '.$slurmHostLink);

	//copy($outFilenameTabularHostLink, $outFilenameTabularHost);



	$interproChangerPathogen = "/var/www/html/PredHPI/SLURM/interproChanger.sh ".$inFilenamePathogenLinkBase." ".$outFilenameTabularPathogenBase;

	copy($inFilenamePathogen, $inFilenamePathogenLink);
	exec($interproChangerPathogen,$outputSlurmPFile,$return_varPathogenS);

	file_put_contents($slurmPathogen, implode("\n",$outputSlurmPFile));

	$ssh->exec('sbatch --wait '.$slurmPathogenLink);

	//copy($outFilenameTabularPathogenLink, $outFilenameTabularPathogen);

	#$interproInstructionHost = '/bin/sh /home/interproscan/interproscan-web.sh -appl CDD,Gene3D,Pfam,SUPERFAMILY -i '.$inFilenameHost. ' -f tsv -iprlookup -goterms -o '.$outFilenameTabularHost. ' -u /var/www/html/PredHPI/tmp';
	#$interproInstructionPathogen = '/bin/sh /home/interproscan/interproscan-web.sh -appl CDD,Gene3D,Pfam,SUPERFAMILY -i '.$inFilenamePathogen. ' -f tsv -iprlookup -goterms -o '.$outFilenameTabularPathogen. ' -u /var/www/html/PredHPI/tmp';

	//$interproInstructionHost = '/bin/sh /home/interproscan/interproscan-web.sh -i '.$inFilenameHost. ' -f tsv -appl Pfam,PROSITEPATTERNS,PROSITEPROFILES -iprlookup -goterms -o '.$outFilenameTabularHost. ' -u /var/www/html/PredHPI/tmp';
	//$interproInstructionPathogen = '/bin/sh /home/interproscan/interproscan-web.sh  -i '.$inFilenamePathogen. ' -f tsv -appl Pfam,PROSITEPATTERNS,PROSITEPROFILES -iprlookup -goterms -o '.$outFilenameTabularPathogen. ' -u /var/www/html/PredHPI/tmp';

	//echo($interproInstructionHost.'\n');
	#exec($interproInstructionHost, $outputHost, $return_varHost);
	//exec($interproInstructionHost);
	#echo($outputHost.'\n');

	//echo($interproInstructionPathogen.'\n');
    #exec($interproInstructionPathogen, $outputPathogen, $return_varPathogen);
    //exec($interproInstructionPathogen);
	#echo($outputPathogen.'\n');
	
	$goSemSimInstruction = 'Rscript GOsimCalculation.R '. $outFilenameTabularHostLink .' '.$outFilenameTabularPathogenLink .' '.$threshold .' '.$orgDB .' '.$outTabularFilename .' '.$combineMethod;


	$goSemSimChangerPathogen = "/var/www/html/PredHPI/SLURM/gosemsimChanger.sh ".$outFilenameTabularHostBase." ".$outFilenameTabularPathogenBase .' '.$threshold .' '.$orgDB .' '.$outTabularFilenameBase .' '.$combineMethod;


	exec($goSemSimChangerPathogen,$outputSlurmGoSemSim,$return_varGoSemSim);

	file_put_contents($slurmGOsemsim, implode("\n",$outputSlurmGoSemSim));

	$ssh->exec('sbatch --wait '.$slurmGOsemsimLink);

	copy($outTabularFilenameLink, $outTabularFilename);


	//$ssh->exec('Rscript /home/user/PredHPI_SLURM/GOsimCalculation.R '.$outFilenameTabularHostLink .' '.$outFilenameTabularPathogenLink .' '.$threshold .' '.$orgDB .' '.$outTabularFilename .' '.$combineMethod);
	//print_r($goSemSimInstruction);
	#$goSemSimInstruction = 'Rscript /var/www/html/PredHPI/GOsimCalculation.R /var/www/html/PredHPI/tmp/1532742196.5613_HostInterproOutput.txt /var/www/html/PredHPI/tmp/1532742196.5613_PathogenInterproOutput.txt 0.2 org.At.tair.db /var/www/html/PredHPI/tmp/1532742196.5613_GOppiPredTabularInfo.txt BMA';
	//echo($goSemSimInstruction.'\n');

//	exec($goSemSimInstruction, $outputSemSim, $return_varSemSim);
	//print_r($goSemSimInstruction);
	#echo shell_exec($goSemSimInstruction);

	### Arrays to create json object

	$netNodes = array();
	$netEdges = array();
	$nodesAdded = array();
	$netElements = array();

	#$outTabularFilename = '/var/www/html/PredHPI/tmp/1532742196.5613_GOppiPredTabularInfo.txt';
	
	$infoTabularFile = fopen($outTabularFilename, "r") or die("Unable to open file!");

	$headerLine = fgets($infoTabularFile);
	
	while(! feof($infoTabularFile)){
	    $line = fgets($infoTabularFile);
	    if($line != false && $line != '\n' && trim($line) != ''){
	    	$separatedLine = explode("\t",$line);
	    	if($separatedLine[0]=="YES"){

	    		$hostID = $separatedLine[1];
	    		$pathogenID = $separatedLine[2];
	    		$simValue = $separatedLine[3];
	    		$hostGOterms = $separatedLine[4];
	    		$pathogenGOterms = $separatedLine[5];
	    		$nodeHostArray = array('id' => $hostID, 'GOterms' => $hostGOterms, 'typeColor' => '#004085');
				$nodePathogenArray = array('id' => $pathogenID, 'GOterms' => $pathogenGOterms, 'typeColor' => '#ff5d48');

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
		$msgEmail = "Your GOppiPred prediction job at PredHPI is done! \nPlease go to http://bioinfo.usu.edu/PredHPI/GOppi-results.php?result=$namer to see it.";
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
unlink($outFilenameTabularHost);
unlink($outFilenameTabularPathogen);
unlink($inFilenameHostLink);
unlink($inFilenamePathogen);
unlink($inFilenameHost);
unlink($inFilenamePathogenLink);
unlink($slurmHost);
unlink($slurmPathogen);
unlink($slurmGOsemsim);
unlink($outFilenameTabularHostLink);
unlink($outFilenameTabularPathogenLink);
unlink($outTabularFilenameLink);

//unlink($outFilenameTabularHost);
//unlink($outFilenameTabularPathogen);








//print_r($_FILES);

//echo($_POST['evalue']);


?>

