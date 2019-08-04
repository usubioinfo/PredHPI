<!DOCTYPE html>
<html>
    <head>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'UA-119048537-7');
        </script>
        <meta charset="utf-8">
        <meta name="description" content="Results">
        <meta name="author" content="Cristian Loaiza">

        <!-- App Favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- App title -->
        <title>DomainPred Results - PredHPI</title>

        <!-- Plugins css-->
        <link href="assets/plugins/multiselect/css/multi-select.css"  rel="stylesheet" type="text/css" />


        <!-- DataTables -->
        <link href="assets/plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="assets/plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Custom box css -->
        <link href="assets/plugins/custombox/css/custombox.min.css" rel="stylesheet">

        <!-- Switchery css -->
        <link href="assets/plugins/switchery/switchery.min.css" rel="stylesheet" />

        <!-- Bootstrap CSS -->
        <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

        <!-- App CSS -->
        <link href="assets/css/style.css" rel="stylesheet" type="text/css" />

        <!-- Modernizr js -->
        <script src="assets/js/modernizr.min.js"></script>


    </head>
<?php

header("Access-Control-Allow-Origin: *");
?>

        <body>



        <header id="topnav">
            <div class="topbar-custom">
                <div class="container">
                    <div class="row" style='background-image: url("assets/images/gallery/network.png");background-color: #d0d0d0;height: 110px;'>
                        <div align="left" class="col-sm-8 col-xs-12">
                            <a href="https://www.usu.edu"  target="_blank"><img src='assets/images/gallery/usulogo.png'></a>
                            <a href="http://bioinfo.usu.edu"  target="_blank" ><h6 style="font-size: 14px;">Kaundal Bioinformatics Laboratory</h6></a>
                        </div>
                        <div align="right" class="col-sm-4 col-xs-12">
                            <a href="http://bioinfo.usu.edu/PredHPI"><img src='assets/images/gallery/favicon.png'></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="navbar-custom">
                <div class="container">
                    <div id="navigation">
                        <ul class="navigation-menu">
                            <li>
                                <a href="index.html" style="font-size: 18px;"><i class="zmdi zmdi-device-hub" style="font-size: 25px;"></i> <span> Submit </span> </a>
                            </li>
                            <li>
                                <a href="about.html" style="font-size: 18px;"><i class="zmdi zmdi-grid" style="font-size: 25px;"></i> <span> About </span> </a>
                            </li>
                            <li>
                                <a href="help.html" style="font-size: 18px;"><i class="zmdi zmdi-info" style="font-size: 25px;"></i> <span> Help </span> </a>
                            </li>
                             <li>
                                <a href="faq.html" style="font-size: 18px;"><i class="zmdi zmdi-help" style="font-size: 25px;"></i><span> FAQ </span> </a>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
        </header>



        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="wrapper" style='margin-top: 180px;'>
            <div class="container">

                <!-- end row --> 

<?php


$namer = $_GET['result'];
$filenameInfo = 'tmp/' . $namer . '_DomainPredTaskInfo.txt';

$running_bool = file_exists('tmp/' . $namer . '_DomainPredTaskInfo.txt'); ///if exist is running
$finished_bool = file_exists('tmp/' . $namer . '_DomainPredTabularInfo.txt'); ///if exist is finished

$empty_results = true;


if($finished_bool){

    $num_interactions = sizeof(file('tmp/' . $namer . '_DomainPredTabularInfo.txt'));

    if($num_interactions > 0){
        $empty_results = false;
    }

    if(!($empty_results)){

        $downloadFile = "tmp/" . $namer . "_DomainPredTabularInfo.txt";echo '<a href="'.$downloadFile.'" class="btn btn-success waves-effect waves-light"download>Full Results <i class="fa fa-download" style="font-size: 20px"></i></a>';
        echo ' <button id="seeNetwork"  class="btn btn-primary waves-effect waves-light "> <i class="ion-network" style="font-size: 20px"></i> Visualize Network</button>';
        $downloadFile = "tmp/" . $namer . ".json";echo ' <a href="'.$downloadFile.'" target="_blank" class="btn btn-purple waves-effect waves-light"download> Download Network <i class="fa fa-download" style="font-size: 20px"></i></a>';
        echo '<p>';

        $fileInfo = fopen($filenameInfo,"r");
        $odd = true;
        while(! feof($fileInfo)){
            $line = fgets($fileInfo);
            if($line != false && $line != '\n' && trim($line) != ''){
                $separatedLine = explode(":",$line);
                if($odd){
                    echo "<b>".$separatedLine[0].":</b>".$separatedLine[1]. ", ";
                    $odd = false;
                } else {
                    echo "<b>".$separatedLine[0].":</b>".$separatedLine[1]. "</br>";
                    $odd = true;
                }
            }
          }
        fclose($fileInfo);
    }
}

?>
                            </p>


<?php


if($finished_bool && !($empty_results)){

    $namer = $_GET['result'];
    $filenameInfo = 'tmp/' . $namer . '_DomainPredTabularInfo.txt';

    $num_interactions = sizeof(file($filenameInfo));

    if($num_interactions<=10000){
        echo '                         <table id="datatable-buttons" class="table table-striped table-bordered" cellspacing="0" width="100%">';
        echo '                          <thead>
                                        <tr role="row">
                                            <th class="sorting_asc" tabindex="0" aria-controls="datatable-buttons" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Host: activate to sort column descending" style="width: 169px;">Host Protein</th>
                                            <th class="sorting_asc" tabindex="0" aria-controls="datatable-buttons" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Pathogen: activate to sort column descending" style="width: 169px;">Pathogen Protein</th>
                                            <th class="sorting" tabindex="0" aria-controls="datatable-buttons" rowspan="1" colspan="1" aria-label="Domains Interacting: activate to sort column ascending" style="width: 248px;">Domains Interacting</th>
                                            <th class="sorting" tabindex="0" aria-controls="datatable-buttons" rowspan="1" colspan="1" aria-label="Database: activate to sort column ascending" style="width: 248px;">Domain-Domain Database</th>
                                        </tr>
                                    </thead>';

    } else {
        echo '                         <table class="table">';
        echo '                          <thead>
                                        <tr>
                                            <th>Host</th>
                                            <th>Pathogen</th>
                                            <th>Domains Interacting</th>
                                            <th>Database</th>
                                        </tr>
                                    </thead>';

    }


    $fileTabular = fopen($filenameInfo,"r");
    $odd = true;
    while(! feof($fileTabular)){
        $line = fgets($fileTabular);
        if($line != false && $line != '\n' && trim($line) != ''){
            $separatedLine = explode("\t",$line);
            if($odd){
                echo '<tr role="row" class="odd">';
            } else {
                echo '<tr role="row" class="even">';
            }
            echo '<td class="sorting_1">' . $separatedLine[0] . '</td>';
            echo '<td>' . $separatedLine[1] . '</td>';
            echo '<td>' . $separatedLine[2] . '</td>';
            echo '<td>' . $separatedLine[count($separatedLine)-1] . '</td>';
            echo '</tr>';
            
        }
      }
    fclose($fileTabular);

} else if($finished_bool && $empty_results){

    echo '<h1 style="color: #039cfd;">FINISHED.</h1><h2 style="color: #1bb99a;">No successfull Host-Pathogen Interactions were predicted using Domain-based  method on this dataset, please try another PredHPI module and resubmit. Thanks.</h2>';

}  else if($running_bool){

    header("Refresh: 30;");
    echo '<h1 style="color: #039cfd;">RUNNING.</h1><h2 style="color: #1bb99a;">This page will refresh in 30 seconds.</h2>';


} else{

    header("Refresh: 60;");
    echo '<h1 style="color: #039cfd;">QUEUED or INACTIVE.</h1><h2 style="color: #1bb99a;">This page will refresh in 60 seconds.</h2>';


}

?>
                                </tbody>
                            </table>
                    </div>


                </div>


                <!-- end row -->

            </div> <!-- container -->


            <!-- Footer -->
            <footer class="footer" style='background-color: #d0d0d0;position: fixed;z-index: 1;'>
                © 2018 &nbsp|&nbsp <a  href="https://www.usu.edu"  target="_blank">Utah State University</a> &nbsp|&nbsp  <a href="http://bioinfo.usu.edu"  target="_blank">Kaundal Bioinformatics Laboratory</a> &nbsp|&nbsp <a href="https://www.psc.usu.edu"  target="_blank">Department of Plants, Soils and Climate</a> &nbsp|&nbsp <a href="http://www.biosystems.usu.edu"  target="_blank">Center for Integrated BioSystems </a>
            </footer>
            <!-- End Footer -->

        


        </div> <!-- End wrapper -->




        

        <!-- jQuery  -->
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/popper.min.js"></script><!-- Tether for Bootstrap -->
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/waves.js"></script>
        <script src="assets/js/jquery.nicescroll.js"></script>
        <script src="assets/plugins/switchery/switchery.min.js"></script>

        <script src="assets/plugins/bootstrap-tagsinput/js/bootstrap-tagsinput.js"></script>
        <script type="text/javascript" src="assets/plugins/multiselect/js/jquery.multi-select.js"></script>
        <script type="text/javascript" src="assets/plugins/jquery-quicksearch/jquery.quicksearch.js"></script>
        <script src="assets/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>


        <!-- file uploads js -->
        <script src="assets/plugins/fileuploads/js/dropify.min.js"></script>

        <!-- Modal-Effect -->
        <script src="assets/plugins/custombox/js/custombox.min.js"></script>
        <script src="assets/plugins/custombox/js/legacy.min.js"></script>

        <!-- App js -->
        <script src="assets/js/jquery.core.js"></script>
        <script src="assets/js/jquery.app.js"></script>

        <!-- Required datatable js -->
        <script src="assets/plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="assets/plugins/datatables/dataTables.bootstrap4.min.js"></script>
        <!-- Buttons examples -->
        <script src="assets/plugins/datatables/dataTables.buttons.min.js"></script>
        <script src="assets/plugins/datatables/buttons.bootstrap4.min.js"></script>
        <script src="assets/plugins/datatables/jszip.min.js"></script>
        <script src="assets/plugins/datatables/pdfmake.min.js"></script>
        <script src="assets/plugins/datatables/vfs_fonts.js"></script>
        <script src="assets/plugins/datatables/buttons.html5.min.js"></script>
        <script src="assets/plugins/datatables/buttons.print.min.js"></script>


        <script type="text/javascript">
            $(document).ready(function() {

                // Default Datatable
                $('#datatable').DataTable();

                //Buttons examples
                var table = $('#datatable-buttons').DataTable({
                    lengthChange: false,
                    buttons: ['copy', 'excel', 'pdf']
                });

                // Key Tables

                $('#key-table').DataTable({
                    keys: true
                });

                // Responsive Datatable
                $('#responsive-datatable').DataTable();

                // Multi Selection Datatable
                $('#selection-datatable').DataTable({
                    select: {
                        style: 'multi'
                    }
                });

                table.buttons().container()
                        .appendTo('#datatable-buttons_wrapper .col-md-6:eq(0)');
            } );

            $("#seeNetwork").click(function() {
                var resultUrl = "http://bioinfo.usu.edu/PredHPI/network-visualization.php?result=" + <?php  echo(json_encode($namer)); ?>+"&method=domainPred";
                window.open(resultUrl);
            });


        </script>

    </body>
</html>