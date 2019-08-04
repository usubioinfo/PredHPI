<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description" content="Network Visualization">
        <meta name="author" content="Cristian Loaiza">

        <!-- App Favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <!-- App title -->
        <title>NetworkVisualization - PredHPI</title>

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

        <link href="assets/css/cytoscape.js-panzoom.css" rel="stylesheet" type="text/css" />

        <link  href="assets/css/spectrum.css" rel="stylesheet" type="text/css" />


        <!-- Cytoscape JS -->
        <script src="https://unpkg.com/cytoscape/dist/cytoscape.min.js"></script>

        <script src="https://unpkg.com/webcola/WebCola/cola.min.js"></script>
        <script src="assets/js/cytoscape-cola.js"></script>
        <script src="assets/js/cytoscape-cose-bilkent.js"></script>
        <script src="assets/js/predHPInet.js"></script>

        <script src="http://code.jquery.com/jquery-2.0.3.min.js"></script>

        <script src="assets/js/cytoscape-panzoom.js"></script>

        <script src='assets/js/spectrum.js'></script>

        <!--<link href="font-awesome-4.0.3/css/font-awesome.css" rel="stylesheet" type="text/css" /> -->


        <!-- for testing with local version of cytoscape.js -->
        <!-- <script src="../cytoscape.js/build/cytoscape.js"></script> -->




    <!-- for testing with local version of cytoscape.js -->
    <!--<script src="../cytoscape.js/build/cytoscape.js"></script>-->


        <style>
          #cy {
            width: 100%;
            height: 100%;
          }


          .card-box{
            height: 70vh;
            background-color: white;
          }



          .card-inverse{
            height: 35vh;

          }

          .alert{
            margin-right: 1%;
          }


          p {
                word-break: break-all;
                white-space: normal;
            }


        </style>


        <!-- Modernizr js -->
        <script src="assets/js/modernizr.min.js"></script>

        <?php
        // Start the session
        header("Access-Control-Allow-Origin: *");
        ini_set('display_errors',1);
        $namer = $_GET['result'];
        $method = $_GET['method'];
        $filename = "tmp/" . $namer . ".json";
        
        ?>

    


    </head>


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
                <div style="margin-top: 10px"></div>
                <div class="col-12">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="cyPanel" class="card-box" style="background-color: #0b1f26;">
                                <div id="cy" ></div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="card-box" style="background-color: #f6f6f6;">
                             <div id="onInterolog">
                                <h5 id="interolog-ID" >ID</h5>
                                <b>Hit Name:</b><p id="interolog-hitName"></p>
                                <b>Hit Taxon:</b><p id="interolog-hitTaxon"></p>
                             </div>
                             <div id="onDomain">
                                <h5 id="domain-ID" >ID</h5>
                                <b>Hit Name:</b><p id="domain-hitName"></p>
                                <b>Description:</b><p id="domain-description"></p>
                             </div>
                             <div id="onGOpred">
                                <h5 id="GOpred-ID" >ID</h5>
                                <b>GO terms:</b><p id="GOpred-terms"></p>
                             </div>
                             <div id="onPhylo">
                                <h5 id="Phylo-ID" >ID</h5>
                                <b>Phylo profile:</b><p id="Phylo-profile"></p>
                             </div>

                                
                                <ul class="nav nav-tabs m-b-10" id="myTabalt" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active show" id="proteinTab-tab1" data-toggle="tab" href="#proteinTab" role="tab" aria-controls="proteinTab" aria-expanded="true" aria-selected="true">Protein</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="interactionTab-tab1" data-toggle="tab" href="#interactionTab" role="tab" aria-controls="interactionTab" aria-selected="false">Interaction</a>
                                    </li>
                                </ul>

                                                            <?php

                                $namer = $_GET['result'];
                                $filenameInfo = 'tmp/' . $namer . '_InterologTabularInfo.txt';
                                $index_a=1;
                                $index_b=4;

                                if($method=="interolog"){
                                    $filenameInfo = 'tmp/' . $namer . '_InterologTabularInfo.txt';
                                    $index_a=1;
                                    $index_b=4;

                                } else if($method=="domainPred"){
                                    $filenameInfo = 'tmp/' . $namer . '_DomainPredTabularInfo.txt';

                                } else if($method=="GOPred"){
                                    $filenameInfo = 'tmp/' . $namer . '_GOppiPredTabularInfo.txt';

                                } else if($method=="phyloPred"){
                                    $filenameInfo = 'tmp/' . $namer . '_PhyloPredTabularInfo.txt';

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
                                        echo '<td class="sorting_1">' . $separatedLine[$index_a] . '</td>';
                                        echo '<td>' . $separatedLine[$index_b] . '</td>';
                                        echo '<td> Host </td>';
                                        echo '</tr>';
                                        
                                    }
                                  }
                                fclose($fileTabular);


                                if($method=="interolog"){
                                    $filenameInfo = 'tmp/' . $namer . '_InterologTabularInfo.txt';
                                    $index_a=2;
                                    $index_b=5;

                                } else if($method=="domainPred"){
                                    $filenameInfo = 'tmp/' . $namer . '_DomainPredTabularInfo.txt';

                                } else if($method=="GOPred"){
                                    $filenameInfo = 'tmp/' . $namer . '_GOppiPredTabularInfo.txt';

                                } else if($method=="phyloPred"){
                                    $filenameInfo = 'tmp/' . $namer . '_PhyloPredTabularInfo.txt';

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
                                        echo '<td class="sorting_1">' . $separatedLine[$index_a] . '</td>';
                                        echo '<td>' . $separatedLine[$index_b] . '</td>';
                                        echo '<td> Pathogen </td>';
                                        echo '</tr>';
                                        
                                    }
                                  }
                                fclose($fileTabular);


                                ?>
                        
                            <div style="background-color: #f6f6f6;">
                                <b>Layout:</b>
                                <select id="layout" class="form-control select2">
                                    <option value="cosebilkent" selected>Cose-bilkient</option>
                                    <option value="cola" >Force-directed</option>
                                    <option value="concentricCentrality">Concentric by centrality</option>
                                    <option value="concentricHierarchyCentrality">Hierarchy by centrality</option>
                                </select>
                                <b>Shape:</b>
                                <select id="shape" class="form-control select2">
                                    <option value="ellipse" selected>Ellipse</option>
                                    <option value="pentagon">Pentagon</option>
                                    <option value="star">Star</option>
                                    <option value="rectangle">Rectangle</option>
                                    <option value="diamond">Diamond</option>
                                    <option value="triangle">Triangle</option>
                                </select>

                                <b>Font size:</b>
                                <select id="fontsize" class="form-control select2">
                                    <option value="12" selected>Large</option>
                                    <option value="10" selected>Normal</option>
                                    <option value="8">Small</option>
                                    <option value="6">Extra-Small</option>
                                </select>
                                <b>Background color:</b>
                                <input type='color' id="colorPicker" value="#0b1f26" />

                            </div>

                                 <div class="tab-content" id="myTabaltContent">
                                    <div class="tab-pane fade in active show" id="proteinTab" role="tabpanel" aria-labelledby="proteinTab-tab">
                                        <table id="datatable-buttons" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                            <thead>
                                                <tr role="row">
                                                    <th class="sorting_asc" tabindex="0" aria-controls="datatable-buttons" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Protein: activate to sort column descending" style="width: 169px;">Protein</th>
                                                    <th class="sorting" tabindex="0" aria-controls="datatable-buttons" rowspan="1" colspan="1" aria-label="Query: activate to sort column ascending" style="width: 248px;">Query</th>
                                                    <th class="sorting" tabindex="0" aria-controls="datatable-buttons" rowspan="1" colspan="1" aria-label="Type: activate to sort column ascending" style="width: 248px;">Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                         </table>
                                    </div>
                                </div>



<!--
                                </tbody>
                            </table>
                                    </div>
                                    <div class="tab-pane fade in active show" id="interactionTab" role="tabpanel" aria-labelledby="interactionTab-tab">
                                    </div>
                                </div>
                      !-->              
                    </div>
                </div>



                <div class="modal fade" id="onNodeInterologSelected" tabindex="-1" role="dialog" aria-labelledby="onNodeInterologSelectedLabel" aria-hidden="true" data-animation="fadein" >
                    <div class="modal-dialog" role="document" style="padding-top: 15%;">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #17a2b8; color: #ffffff;">
                                <h5 id="NodeInterolog-ID" class="modal-title" id="onNodeInterologSelectedLabel" >ID</h5>
                            </div>
                            <div class="modal-body" id="onNodeInterologSelectedModalBody">
                                <b>Hit Name:</b><p id="NodeInterolog-hitName"></p>
                                <b>Hit Taxon:</b><p id="NodeInterolog-hitTaxon"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="onEdgeInterologSelected" tabindex="-1" role="dialog" aria-labelledby="onEdgeInterologSelectedLabel" aria-hidden="true" data-animation="fadein" >
                    <div class="modal-dialog" role="document" style="padding-top: 15%;">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #17a2b8; color: #ffffff;">
                                <h5 id="EdgeInterolog-ID" class="modal-title" id="onEdgeInterologSelectedLabel" >ID</h5>
                            </div>
                            <div class="modal-body" id="onEdgeInterologSelectedModalBody">
                                <b>Database:</b><p id="EdgeInterolog-database"></p>
                                <b>Interaction Type:</b><p id="EdgeInterolog-interactionType"></p>
                                <b>Detection Method:</b><p id="EdgeInterolog-detectionMethod"></p>
                                <b>Author:</b><p id="EdgeInterolog-authorName"></p>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal fade" id="onNodeDomainPredSelected" tabindex="-1" role="dialog" aria-labelledby="onNodeDomainPredSelectedLabel" aria-hidden="true" data-animation="fadein" >
                    <div class="modal-dialog" role="document" style="padding-top: 15%;">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #17a2b8; color: #ffffff;">
                                <h5 id="NodeDomainPred-ID" class="modal-title" id="onNodeDomainPredSelectedLabel" >ID</h5>
                            </div>
                            <div class="modal-body" id="onNodeDomainPredSelectedModalBody">
                                <b>Hit Name:</b><p id="NodeDomainPred-hitName"></p>
                                <b>Description:</b><p id="NodeDomainPred-description"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="onEdgeDomainPredSelected" tabindex="-1" role="dialog" aria-labelledby="onEdgeDomainPredSelectedLabel" aria-hidden="true" data-animation="fadein" >
                    <div class="modal-dialog" role="document" style="padding-top: 15%;">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #17a2b8; color: #ffffff;">
                                <h5 id="EdgeDomainPred-ID" class="modal-title" id="onEdgeDomainPredSelectedLabel" >ID</h5>
                            </div>
                            <div class="modal-body" id="onEdgeDomainPredSelectedModalBody">
                                <b>Database:</b><p id="EdgeDomainPred-database"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="onNodeGOPredSelected" tabindex="-1" role="dialog" aria-labelledby="onNodeGOPredSelectedLabel" aria-hidden="true" data-animation="fadein" >
                    <div class="modal-dialog" role="document" style="padding-top: 15%;">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #17a2b8; color: #ffffff;">
                                <h5 id="NodeGOPred-ID" class="modal-title" id="onNodeGOPredSelectedLabel" >ID</h5>
                            </div>
                            <div class="modal-body" id="onNodeGOPredSelectedModalBody">
                                <b>GO terms:</b><p id="NodeGOPred-goTerms"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="onEdgeGOPredSelected" tabindex="-1" role="dialog" aria-labelledby="onEdgeGOPredSelectedLabel" aria-hidden="true" data-animation="fadein" >
                    <div class="modal-dialog" role="document" style="padding-top: 15%;">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #17a2b8; color: #ffffff;">
                                <h5 id="EdgeGOPred-ID" class="modal-title" id="onEdgeGOPredSelectedLabel" >ID</h5>
                            </div>
                            <div class="modal-body" id="onEdgeGOPredSelectedModalBody">
                                <b>Similarity:</b><p id="EdgeGOPred-Sim"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="onNodePhyloPredSelected" tabindex="-1" role="dialog" aria-labelledby="onNodePhyloPredSelectedLabel" aria-hidden="true" data-animation="fadein" >
                    <div class="modal-dialog" role="document" style="padding-top: 15%;">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #17a2b8; color: #ffffff;">
                                <h5 id="NodePhyloPred-ID" class="modal-title" id="onNodePhyloPredSelectedLabel" >ID</h5>
                            </div>
                            <div class="modal-body" id="onNodePhyloPredSelectedModalBody">
                                <b>Phylo profile:</b><p id="NodePhyloPred-pattern"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="onEdgePhyloPredSelected" tabindex="-1" role="dialog" aria-labelledby="onEdgePhyloPredSelectedLabel" aria-hidden="true" data-animation="fadein" >
                    <div class="modal-dialog" role="document" style="padding-top: 15%;">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #17a2b8; color: #ffffff;">
                                <h5 id="EdgePhyloPred-ID" class="modal-title" id="onEdgePhyloPredSelectedLabel" >ID</h5>
                            </div>
                            <div class="modal-body" id="onEdgePhyloPredSelectedModalBody">
                                <b>Similarity:</b><p id="EdgePhyloPred-Sim"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="alert alert-primary" role="alert">
                        <strong>Host proteins!</strong> are in blue.
                    </div>
                    <div class="alert alert-danger" role="alert">
                        <strong>Pathogen proteins!</strong> are in red.
                    </div>
                    <div class="alert alert-secondary" role="alert">
                        Interaction from different databases sources are colored with different colors.
                    </div>
                    <?php  $namer = $_GET['result'];$downloadFile = "tmp/" . $namer . ".json";echo '<a href="'.$downloadFile.'" target="_blank" <i class="fa fa-download" style="font-size: 40px"></i></a>'; ?>
                    <a id="downloadPNG" href="" target="_blank" style="margin-left: 10px" download="network.png">  <i class="fa ion-image" style="font-size: 40px"></i></a> 
                    
                     
                     

                </div>



                <!-- end row -->

            </div> <!-- container -->


            <!-- Footer -->
            <footer class="footer" style='background-color: #d0d0d0; '>
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


        <script>

            $('#onInterolog').hide();
            $('#onGOpred').hide();
            $('#onPhylo').hide();
            $('#onDomain').hide();




            var method= <?php  echo(json_encode($method)); ?>;
            var jsonFilename = <?php  echo(json_encode($filename)); ?>;


            var downloadPNG = $('#downloadPNG');

            var jsonSize = <?php  echo(filesize($filename)); ?>;
            var linearCurve = 'bezier';

            if(jsonSize>=300000){
                linearCurve = 'haystack';
            }





            $.getJSON(jsonFilename, function(json) {



                var cy = window.cy = cytoscape({
                  container: document.getElementById('cy'), 
                  ready: function(){
                    },                 
                  layout: {
                    name: 'cose-bilkent',
                    zoomingEnabled: true,
                    animate: false,
                    randomize: false
                  },
                  elements: json,

                  style: [ // the stylesheet for the graph
                    {
                      selector: 'node',
                      style: {
                        'label': 'data(id)',
                        'background-color': 'data(typeColor)',
                        //'background-image': 'assets/images/gallery/rednode.png',
                        "font-size": "10px",
                        "text-valign": "center",
                        "text-halign": "center",
                        "text-outline-color": "#555",
                        "text-outline-width": "2px",
                        "min-zoomed-font-size":"6px",
                        "shape" : "ellipse",
                        "color": "#fff",
                        "overlay-padding": "6px",
                        "z-index": "10",
                        "height": function( n ){ if(n.data('height')==null){return 30;}else{return n.data('height');}},
                        "width": function( n ){ if(n.data('width')==null){return 35;}else{return n.data('width');}}
                      }
                    },

                    {
                      selector: 'edge',
                      style: {
                        'line-color': 'data(databaseColor)',
                        'curve-style': linearCurve,
                        'opacity': 0.5
                      }
                    }
                  ]
               });

                
               $("#shape").change(function() {
                 var newShape = this.value;
                 cy.style().selector('node').style({'shape': newShape}).update();
               });

               $("#fontsize").change(function() {
                 var newFontSize= this.value+"px";
                 cy.style().selector('node').style({'font-size': newFontSize}).update();
               });

               $("#colorPicker").change(function(){
                    $("#cyPanel").css("background-color",this.value);
                });


                cy.panzoom({});


               cy.on('click', 'node', function(evt){

                      var png64 = cy.png();
                      $('#downloadPNG').attr('href', png64);

                      var nodeSelected = cy.getElementById(evt.target.id());


                      if(method=="interolog"){

                        $('#onDomain').hide();
                        $('#onGOpred').hide();
                        $('#onPhylo').hide();
                        $('#onInterolog').show();

                        var externalLinksQuery= '<h6><a target="_blank" href="https://www.uniprot.org/uniprot/'+nodeSelected.data("id")+'"> Uniprot Description </a> | <a target="_blank" href="https://www.ncbi.nlm.nih.gov/gene/?term='+nodeSelected.data("id")+'"> Gene Entrez ID</a><h6>';

                        var externalLinks= '<a target="_blank" href="https://www.uniprot.org/uniprot/'+nodeSelected.data("hitName")+'"> Uniprot Description </a> | <a target="_blank" href="https://www.ncbi.nlm.nih.gov/gene/?term='+nodeSelected.data("hitName")+'"> Gene Entrez ID</a>';

                        document.getElementById("interolog-ID").innerHTML =nodeSelected.data("id")+" "+externalLinksQuery;
                        document.getElementById("interolog-hitName").innerHTML = nodeSelected.data("hitName")+"  "+externalLinks;
                        $('#interolog-hitTaxon').text(nodeSelected.data("hitTaxon"));

                        //$('#onNodeInterologSelected').modal();

                        $('#NodeInterolog-ID').text(nodeSelected.data("id"));
                        document.getElementById("NodeInterolog-hitName").innerHTML = nodeSelected.data("id")+"  "+externalLinks;
                        $('#NodeInterolog-hitTaxon').text(nodeSelected.data("hitTaxon"));

                      } else if(method=="domainPred"){


                        $('#onInterolog').hide();
                        $('#onGOpred').hide();
                        $('#onPhylo').hide();
                        $('#onDomain').show();

                        var externalLinksQuery= '<h6><a target="_blank" href="https://www.uniprot.org/uniprot/'+nodeSelected.data("id")+'"> Uniprot Description </a> | <a target="_blank" href="https://www.ncbi.nlm.nih.gov/gene/?term='+nodeSelected.data("id")+'"> Gene Entrez ID</a><h6>';

                        var externalLinks= '<a target="_blank" href="https://www.ncbi.nlm.nih.gov/cdd?term='+nodeSelected.data("hitName")+'"> NCBI CDD </a> | <a target="_blank" href="https://www.ebi.ac.uk/interpro/search?q='+nodeSelected.data("hitName")+'"> Interpro Query </a>';

                        //$('#onNodeDomainPredSelected').modal();

                        document.getElementById("domain-ID").innerHTML =nodeSelected.data("id")+" "+externalLinksQuery;
                        document.getElementById("domain-hitName").innerHTML = nodeSelected.data("hitName")+"  "+externalLinks;
                        document.getElementById("domain-description").innerHTML = nodeSelected.data("description");

                        $('#NodeDomainPred-ID').text(nodeSelected.data("id"));
                        document.getElementById("NodeDomainPred-hitName").innerHTML = nodeSelected.data("hitName")+"  "+externalLinks;
                        document.getElementById("NodeDomainPred-description").innerHTML = nodeSelected.data("description");

                      } else if(method=="GOPred"){

                        $('#onInterolog').hide();
                        $('#onDomain').hide();
                        $('#onPhylo').hide();
                        $('#onGOpred').show();

                        //$('#onNodeGOPredSelected').modal();
                        var externalLinksQuery= '<h6><a target="_blank" href="https://www.uniprot.org/uniprot/'+nodeSelected.data("id")+'"> Uniprot Description </a> | <a target="_blank" href="https://www.ncbi.nlm.nih.gov/gene/?term='+nodeSelected.data("id")+'"> Gene Entrez ID</a><h6>';


                        var GOtermsString = String(nodeSelected.data("GOterms"));
                        var GOtermsSplitted =  new Array();
                        GOtermsSplitted = GOtermsString.split(",");
                        var GOterms = "";
                        var i;

                        for (i = 0; i < GOtermsSplitted.length; i++) {
                            if(i+1<GOtermsSplitted.length){
                                GOterms += '<a target="_blank" href="http://amigo.geneontology.org/amigo/term/'+ GOtermsSplitted[i] + '">'+GOtermsSplitted[i]+'</a>,';
                            } else {
                                GOterms += '<a target="_blank" href="http://amigo.geneontology.org/amigo/term/'+ GOtermsSplitted[i] + '">'+GOtermsSplitted[i]+'</a>';
                            }
                        }

                        console.log(GOterms);

                        document.getElementById("GOpred-ID").innerHTML =nodeSelected.data("id")+" "+externalLinksQuery;
                        document.getElementById("GOpred-terms").innerHTML = GOterms;

                        $('#NodeGOPred-ID').text(nodeSelected.data("id"));
                        document.getElementById("NodeGOPred-goTerms").innerHTML = nodeSelected.data("GOterms");
                        
                      } else if(method=="phyloPred"){

                        $('#onInterolog').hide();
                        $('#onDomain').hide();
                        $('#onGOpred').hide();
                        $('#onPhylo').show();

                        //$('#onNodePhyloPredSelected').modal();

                        var externalLinksQuery= '<h6><a target="_blank" href="https://www.uniprot.org/uniprot/'+nodeSelected.data("id")+'"> Uniprot Description </a> | <a target="_blank" href="https://www.ncbi.nlm.nih.gov/gene/?term='+nodeSelected.data("id")+'"> Gene Entrez ID</a><h6>';

                        document.getElementById("Phylo-ID").innerHTML =nodeSelected.data("id")+" "+externalLinksQuery;
                        document.getElementById("Phylo-profile").innerHTML = nodeSelected.data("pattern");

                        $('#NodePhyloPred-ID').text(nodeSelected.data("id"));
                        document.getElementById("NodePhyloPred-pattern").innerHTML = nodeSelected.data("pattern");
                      }
                      
                });

               cy.on('click', 'edge', function(evt){

                      var png64 = cy.png();
                      $('#downloadPNG').attr('href', png64);
                      
                      var edgeSelected = cy.getElementById(evt.target.id());



                      if(method=="interolog"){

                          $('#onEdgeInterologSelected').modal();
                          $('#EdgeInterolog-ID').text(edgeSelected.data("name"));
                          $('#EdgeInterolog-database').text(edgeSelected.data("database"));
                          $('#EdgeInterolog-interactionType').text(edgeSelected.data("interactionType"));
                          $('#EdgeInterolog-detectionMethod').text(edgeSelected.data("detectionMethod"));
                          $('#EdgeInterolog-authorName').text(edgeSelected.data("authorName"));
                      } else if(method=="domainPred"){

                        $('#onEdgeDomainPredSelected').modal();
                        $('#EdgeDomainPred-ID').text(edgeSelected.data("name"));
                        $('#EdgeDomainPred-database').text(edgeSelected.data("database"));

                      } else if(method=="GOPred"){

                        $('#onEdgeGOPredSelected').modal();
                        $('#EdgeGOPred-ID').text(edgeSelected.data("name"));
                        $('#EdgeGOPred-Sim').text(edgeSelected.data("similatirity"));
                        
                      } else if(method=="phyloPred"){

                        $('#onEdgePhyloPredSelected').modal();
                        $('#EdgePhyloPred-ID').text(edgeSelected.data("name"));
                        $('#EdgePhyloPred-Sim').text(edgeSelected.data("similatirity"));
                        
                      }

                });

               /*
                cy.nodes().forEach(
                    function(n){ 
                      var w = n.degree(true);
                      var factor = 0;
                      //console.log(n.degree(true));
                        if( w == null ){
                         factor = 1;
                        } else if(w <= 3){
                         factor =1.3;
                        } else if(w > 3 & w <= 10){
                         factor =2;
                        } else if(w > 10 & w <= 20){
                         factor =3;
                        } else if(w > 20 & w <= 50){
                         factor =4;
                        }

                        n.data('height',factor*30);
                        n.data('width',factor*30);
               });
                */







            });

                
            
    
        </script>



    </body>
</html>