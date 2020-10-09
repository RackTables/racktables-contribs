<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <title>Google Maps</title>
  </head>
  <body onunload="GUnload()">

    <!-- you can use tables or divs for the overall layout -->
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=REPLACE_THIS_STRING_WITH_YOUR_GMAPS_KEY" type="text/javascript"></script>
    <table border=1>
      <tr>
        <td>
           <div id="map" style="width: 1000px; height: 600px"></div>
        </td>
        <td width = 200 valign="top" style="text-decoration: underline; color: #4444ff;">
           <div id="side_bar"></div>
        </td>
      </tr>

    </table>

    <noscript><b>JavaScript must be enabled in order for you to use Google Maps.</b> 
      However, it seems JavaScript is either disabled or not supported by your browser. 
      To view Google Maps, enable JavaScript by changing your browser options, and then 
      try again.
    </noscript>


    <script type="text/javascript">
    //<![CDATA[

    if (GBrowserIsCompatible()) {
      // this variable will collect the html which will eventualkly be placed in the side_bar
      var side_bar_html = "";
    
      // arrays to hold copies of the markers used by the side_bar
      // because the function closure trick doesn't work there
      var gmarkers = [];

      // A function to create the marker and set up the event window
      function createMarker(point,name,html) {
        var marker = new GMarker(point, {title: name });
        GEvent.addListener(marker, "click", function() {
          marker.openInfoWindowHtml(html);
        });
        // save the info we need to use later for the side_bar
        gmarkers.push(marker);
        // add a line to the side_bar html
        side_bar_html += '<a href="javascript:myclick(' + (gmarkers.length-1) + ')">' + name + '<\/a><br>';
        return marker;
      }


      // This function picks up the click and opens the corresponding info window
      function myclick(i) {
        GEvent.trigger(gmarkers[i], "click");
      }
      
      // create the map
      var map = new GMap2(document.getElementById("map"));
      map.addControl(new GLargeMapControl());
      map.addControl(new GMapTypeControl());
      map.setCenter(new GLatLng( 50.87879,4.6999), 14);
      map.enableScrollWheelZoom();

      // Read the data from example.xml
      GDownloadUrl("data.php", function(doc) {
        var xmlDoc = GXml.parse(doc);
        var markers = xmlDoc.documentElement.getElementsByTagName("marker");
          
        for (var i = 0; i < markers.length; i++) {
          // obtain the attribues of each marker
          var lat = parseFloat(markers[i].getAttribute("lat"));
          var lng = parseFloat(markers[i].getAttribute("lng"));
          var point = new GLatLng(lat,lng);
          var html = GXml.value(markers[i].getElementsByTagName("infowindow")[0]);
          //var html = markers[i].getAttribute("html");
          var label = markers[i].getAttribute("label");
          // create the marker
          var marker = createMarker(point,label,html);
          map.addOverlay(marker);
        }
        // put the assembled side_bar_html contents into the side_bar div
        document.getElementById("side_bar").innerHTML = side_bar_html;
        
        var lines = xmlDoc.documentElement.getElementsByTagName("line");
          // read each line
          for (var a = 0; a < lines.length; a++) {
            // get any line attributes
            var colour = lines[a].getAttribute("colour");
            var width  = parseFloat(lines[a].getAttribute("width"));
            // read each point on that line
            var points = lines[a].getElementsByTagName("point");
            var pts = [];
            for (var i = 0; i < points.length; i++) {
               pts[i] = new GLatLng(parseFloat(points[i].getAttribute("lat")),
                                   parseFloat(points[i].getAttribute("lng")));
            }
            map.addOverlay(new GPolyline(pts,colour,width));
          }

      });
    }

    else {
      alert("Sorry, the Google Maps API is not compatible with this browser");
    }


    //]]>
    </script>
  </body>

</html>




