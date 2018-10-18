<?php
//echo "<br>incluindo autoload: ";
//echo dirname(__FILE__).'/includes/autoload.php';
require_once(dirname(__FILE__).'/includes/autoload.php');
//echo "<br>autoload incluido";
error_reporting(0);
//ini_set('display_errors', E_ALL);

$DEBUG = false;

$svsZabLib = new SvsZabbixLib();
$svsZabLib->setDebug($DEBUG);
if ($DEBUG) echo "<pre>";
//Recupera o Mapa
//$mapinfo = $svsZabLib->getSysmapByName('Mapa_Base_GMaps_0');
$mapinfo = $svsZabLib->getSysmapByName('Infovia');

//echo "<br>opaaa";
if ($DEBUG) print_r($mapinfo);

//Recupera os HostGroups
if ($DEBUG) echo "\n<br>host_groups:";
$hostGroups = $svsZabLib->getSysmapHostsGroups($mapinfo['sysmapid']);
if ($DEBUG) print_r($hostGroups);

//Recupera os Links
if ($DEBUG) echo "\n<br>links:";
$ar_links = $svsZabLib->getSysmapLinks($mapinfo['sysmapid']);
if ($DEBUG) print_r($ar_links);

if ($DEBUG) echo "</pre>";


?>

<html>
<head>

	<link rel="stylesheet" href="leaflet/leaflet.css" />
	<script src="leaflet/leaflet.js"></script>


	<script src="leaflet/Label.js"></script>
	<script src="leaflet/BaseMarkerMethods.js"></script>
	<script src="leaflet/Marker.Label.js"></script>
	<script src="leaflet/CircleMarker.Label.js"></script>
	<script src="leaflet/Path.Label.js"></script>
	<script src="leaflet/Map.Label.js"></script>
	<script src="leaflet/FeatureGroup.Label.js"></script>

	<link rel="SHORTCUT ICON" HREF="leaflet/wea/owm.ico">
	<link rel="stylesheet" type="text/css" href="leaflet/wea/leaflet-openweathermap.css" />
	<script type="text/javascript" src="leaflet/wea/leaflet-openweathermap.js"></script>

	<link rel="stylesheet" type="text/css" href="leaflet/wea/map.css" />
	<script src="leaflet/wea/Permalink.js"></script>
	<script src="leaflet/wea/Permalink.Layer.js"></script>
	<script src="leaflet/wea/Permalink.Overlay.js"></script>
	<link rel="stylesheet" type="text/css" href="leaflet/wea/leaflet-languageselector.css" />
	<script src="leaflet/wea/leaflet-languageselector.js"></script>
	<script src="leaflet/wea/map_i18n.js"></script>
	<script src="leaflet/wea/map.js"></script>


	<script src="leaflet/kml/KML.js"></script>

   
<script language="javascript">


	function addMarkersToMap(map) {


	var myIcon = L.icon({
		iconUrl: 'images/torre.png',
		iconSize: [27, 37],
		iconAnchor: [10, 10],
		labelAnchor: [6, 0] // as I want the label to appear 2px past the icon (10 + 2 - 6)
	});

	<?php
	//---GROUP HOSTS----------------------------------------------------------------
                $i = 0;
                $aux_grouphosts_by_selementid = [];
                while ($i < count($hostGroups)){
                        //Salva a posicao para facilitar a criacao das linhas
                        $aux_grouphosts_by_selementid[$hostGroups[$i]['selementid']] = $hostGroups[$i];

                //Recupera informacoes de Sub-mapas
                echo "/*";
                $mapaDetalhado = $svsZabLib->getSysmapByName($hostGroups[$i]['name']);
                echo "*/";

                ?>
			var marker<?=$hostGroups[$i]['groupid']?> = new L.LatLng(<?=$hostGroups[$i]['lat']?>,<?=$hostGroups[$i]['lon']?>);
//			L.marker(marker<?=$hostGroups[$i]['groupid']?>, {draggable:true}).bindLabel('<?=$hostGroups[$i]['label']?>', { noHide: true }).addTo(map);
			L.marker([<?=$hostGroups[$i]['lat']?>,<?=$hostGroups[$i]['lon']?>], {icon: myIcon}).bindLabel('<?=$hostGroups[$i]['label']?>', { noHide: true}).addTo(map);
//			L.marker([<?=$hostGroups[$i]['lat']?>,<?=$hostGroups[$i]['lon']?>]).addTo(map);
               <?php
                $i++;
                }
                   print_r($aux_grouphosts_byid);
                ?>

        }

	function addPolylineToMap(map) {

		<?php
		$i=0;
			while ($i < count($ar_links)){
			// print_r($ar_links[$i]['selementid1']);
		?>
			var polylinePoints = [new L.LatLng(<?=$aux_grouphosts_by_selementid[$ar_links[$i]['selementid1']]['lat']?>,
				<?=$aux_grouphosts_by_selementid[$ar_links[$i]['selementid1']]['lon']?>),
				new L.LatLng(<?=$aux_grouphosts_by_selementid[$ar_links[$i]['selementid2']]['lat']?>,
				<?=$aux_grouphosts_by_selementid[$ar_links[$i]['selementid2']]['lon']?>)
			];
			var polylineOptions = {
				color: '#<?=$ar_links[$i]['cor_link']?>',
				weight: 3.9,
				opacity: 0.9
			};
			var polyline = new L.Polyline(polylinePoints, polylineOptions);
			map.addLayer(polyline);
		<?php
		$i++;
		}
		?>
			// zoom the map to the polyline
			//map.fitBounds(polyline.getBounds());
	}

	function init() {

	
		var openWeatherMapKey = '46060fbef173cdd54a18b2fcf65bd884';
		var clouds = L.OWM.clouds({showLegend: true, opacity: 0.8, appId: '46060fbef173cdd54a18b2fcf65bd884'});
		var city = L.OWM.current({intervall: 15, lang: 'pt_br', appId: '46060fbef173cdd54a18b2fcf65bd884'});

		// Initialise base map layers
		var osmStandard =
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png?appid=46060fbef173cdd54a18b2fcf65bd884', {
			attribution: '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
			maxZoom: 19
	          })

		var mapbox = 
			L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1Ijoia2xlYmVydGFmZmFyZWwiLCJhIjoiY2puMHFoeDNuNHNnNjNxbnl1d3A0MDlmaSJ9.FliRQ8BNbB5NGpgtH9lkpQ', {
			attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
			maxZoom: 18,
			id: 'mapbox.streets',
			accessToken: 'pk.eyJ1Ijoia2xlYmVydGFmZmFyZWwiLCJhIjoiY2puMHFoeDNuNHNnNjNxbnl1d3A0MDlmaSJ9.FliRQ8BNbB5NGpgtH9lkpQ'
		})
		var mapout =
                        L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1Ijoia2xlYmVydGFmZmFyZWwiLCJhIjoiY2puMHFoeDNuNHNnNjNxbnl1d3A0MDlmaSJ9.FliRQ8BNbB5NGpgtH9lkpQ', {
                        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                        maxZoom: 18,
                        id: 'mapbox.outdoors',
                        accessToken: 'pk.eyJ1Ijoia2xlYmVydGFmZmFyZWwiLCJhIjoiY2puMHFoeDNuNHNnNjNxbnl1d3A0MDlmaSJ9.FliRQ8BNbB5NGpgtH9lkpQ'
                })


		var wikimedia =
			L.tileLayer('https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors. Base map: <a href="https://www.mediawiki.org/wiki/Maps#Production_maps_cluster">wikimedia maps</a>',
			maxZoom: 18
		})

		var OpenMapSurfer_Roads = 
			L.tileLayer('https://korona.geog.uni-heidelberg.de/tiles/roads/x={x}&y={y}&z={z}', {
			maxZoom: 20,
			attribution: 'Imagery from <a href="http://giscience.uni-hd.de/">GIScience Research Group @ University of Heidelberg</a> &mdash; Map data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
		});
		


		// Initialise overlay layers
		var hiking =
			L.tileLayer('//tile.lonvia.de/hiking/{z}/{x}/{y}.png', {
			maxZoom: 18
		})

		var cycling =
			L.tileLayer('//tile.waymarkedtrails.org/cycling/{z}/{x}/{y}.png', {
			maxZoom: 18
		})


		var srdt = new L.KML("https://raw.githubusercontent.com/klebertaffarel/geosavis/master/geosavisK/kmz/coberturasrdt.kml", {async: true});

		// Name the layers
		var baseMaps = {
	            'OpenStreetMap standard': osmStandard,
		    'Mapbox': mapbox,
		    'Mapbox Out': mapout,
        	    'Wikimedia maps': wikimedia,
	            'Open Roads': OpenMapSurfer_Roads

	        };

		var overlayMaps = {
			'Nuvens': clouds,
			'Clima': city,
			'SRDT': srdt
		};



		var map = L.map('map', {
			center: [-21.30,-55.99],
			zoom: 9,
			layers: city
		});


		// Add our preferred default layer choice directly to the map
                map.addLayer(mapout);


        	// Add the layer picker control
	        var layerControl = L.control.layers(baseMaps, overlayMaps).addTo(map);
		L.control.scale().addTo(map);
	
	        map.attributionControl.setPrefix('Powered by Leaflet'); // Don't show the 'Powered by Leaflet' text. Attribution overload

	
		map.attributionControl.setPrefix(''); // Don't show the 'Powered by Leaflet' text.

		addMarkersToMap(map);        
		addPolylineToMap(map);

	//Para recarregar automaticamente
	setInterval(function(){
        	map.setView([-23.465439,-54.961861]);
	        setTimeout(function(){
			location.reload();
			map.setView([-21.30,-55.99]);
	       }, 29000);
	}, 29000);	


//        setTimeout(function(){
//                location.reload();
//        }, 115000);


      }

   </script>
</head>


<body onLoad="javascript:init();">
   <div id="map" style="height: 1075px"></div>
   
</body>                                                                                                                          
</html>
