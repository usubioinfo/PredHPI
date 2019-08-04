(function(){
  document.addEventListener('DOMContentLoaded', function(){

      let $$ = selector => Array.from( document.querySelectorAll( selector ) );
      let $ = selector => document.querySelector( selector );

      let tryPromise = fn => Promise.resolve().then( fn );

      let $layout = $('#layout');


      let calculateCachedCentrality = () => {
      let nodes = cy.nodes();

      if( nodes.length > 0 && nodes[0].data('centrality') == null ){
      let centrality = cy.elements().closenessCentralityNormalized();

      nodes.forEach( n => n.data( 'centrality', centrality.closeness(n) ) );
      }
      };

      let maxLayoutDuration = 1500;
      let layoutPadding = 50;
      let concentric = function( node ){
      calculateCachedCentrality();

      return node.data('centrality');
      };
      let levelWidth = function( nodes ){
      calculateCachedCentrality();

      let min = nodes.min( n => n.data('centrality') ).value;
      let max = nodes.max( n => n.data('centrality') ).value;


      return ( max - min ) / 5;
      };

      console.log(jsonFilename);

      let layouts = {
        cola: {
          name: 'cola',
          padding: 50,
          nodeSpacing: 12,
          edgeLengthVal: 45,
          animate: true,
          randomize: true,
          maxSimulationTime: 1500,
          edgeLength: function( e ){
            let w = e.data('weight');

            if( w == null ){
              w = 0.5;
            }

            return 45 / w;
          }
        },
        concentricCentrality: {
          name: 'concentric',
          padding: 50,
          animate: true,
          animationDuration: 1500,
          concentric: concentric,
          levelWidth: levelWidth
        },
        concentricHierarchyCentrality: {
          name: 'concentric',
          padding: 50,
          animate: true,
          animationDuration: 1500,
          concentric: concentric,
          levelWidth: levelWidth,
          sweep: Math.PI * 2 / 3,
          clockwise: true,
          startAngle: Math.PI * 1 / 6
        },
        cosebilkent: {
          name: 'cose-bilkent',
          zoomingEnabled: true,
          animate: false,
          randomize: false
        },
      };

      let prevLayout;
      let getLayout = name => Promise.resolve( layouts[ name ] );
      let applyLayout = layout => {
        if( prevLayout ){
          prevLayout.stop();
        }

        let l = prevLayout = cy.makeLayout( layout );

        return l.run().promiseOn('layoutstop');
      }

      let applyLayoutFromSelect = () => Promise.resolve( $layout.value ).then( getLayout ).then( applyLayout );

     $layout.addEventListener('change', applyLayoutFromSelect);
     /*
     var b64key = 'base64,';
      var b64 = cy.png().substring( content.indexOf(b64key) + b64key.length );
      var imgBlob = base64ToBlob( b64, 'image/png' );

      saveAs( imgBlob, 'graph.png' );

*/


  });
})();

// tooltips with jQuery
