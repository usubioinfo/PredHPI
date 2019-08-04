<?php
// Start the session
header("Access-Control-Allow-Origin: *");


ini_set('display_errors', 0);

//$fileSeqHost= $_POST['fileSeqHost'];
$areaSeqHost= $_POST['areaSeqHost'];
//$fileSeqPathogen= $_POST['fileSeqPathogen'];
$areaSeqPathogen= $_POST['areaSeqPathogen'];

$interologType= $_POST['type'];

$module= $_POST['module'];

$namer = microtime(true);
$inFilenameHost= '/var/www/html/PredHPI/tmp/' . $namer . '_HostTestInput.txt';
$inFilenamePathogen= '/var/www/html/PredHPI/tmp/' . $namer . '_PathogenTestInput.txt';


$numberHost= 0;
$numberPathogen= 0;



function testFasta($fastaText,$type) {
    $checkPass  = true;
    $protSeq = false;
    $fastasequences = $fastaText;
    $checkStatus = 0;
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


            } else if($type==2){
                $numberPathogen= $numberPathogen + 1;

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


                        } else if($type==2){
                            $numberPathogen= $numberPathogen + 1;

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
	$correctFastaFileHost = testFasta($hostSequence,1);
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
	$correctFastaFilePathogen = testFasta($pathogenSequence,2);
}


if($correctFastaFileHost > 5 && $interologType <= 2){
	$proceedHost = true;
	//echo "Proceed pathogen ". $proceedHost ."\n";
}

if($correctFastaFilePathogen > 5 && $interologType >= 2 ){
	$proceedPathogen = true;
	//echo "Proceed pathogen ". $proceedPathogen ."\n";
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

if($proceed){
    if($module=="Interolog"){
        if($numberHost>50000 && $numberPathogen>10000){
            $errorFasta = 6;
        } else if($numberHost>50000){
            $errorFasta = 4;
        } else if($numberPathogen>10000){
            $errorFasta = 5;
        }


    } else if ($module=="Domain-based") {
        if($numberHost>50000 && $numberPathogen>6000){
            $errorFasta = 6;
        } else if($numberHost>50000){
            $errorFasta = 4;
        } else if($numberPathogen>6000){
            $errorFasta = 5;
        }

    } else if ($module=="GOsim") {
        if($numberHost>4000 && $numberPathogen>3000){
            $errorFasta = 6;
        } else if($numberHost>4000){
            $errorFasta = 4;
        } else if($numberPathogen>3000){
            $errorFasta = 5;
        }

    } else if ($module=="Phylo-profiling") {
        if($numberHost>4000 && $numberPathogen>3000){
            $errorFasta = 6;
        } else if($numberHost>4000){
            $errorFasta = 4;
        } else if($numberPathogen>3000){
            $errorFasta = 5;
        }

    }
}

if($errorFasta>=4){
    $proceed = false;
}

unlink($inFilenameHost);
unlink($inFilenamePathogen);

if($proceed){
    echo("proceed");
} else {
    echo("fastaerror-".$errorFasta);
}



?>