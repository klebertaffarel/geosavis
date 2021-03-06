<?php
//echo "<br>incluindo autoload: ";
//echo dirname(__FILE__).'/includes/autoload.php';
require_once(dirname(__FILE__).'/includes/autoload.php');
//echo "<br>autoload incluido";
error_reporting(1);
ini_set('display_errors', E_ALL);

$DEBUG = false;

$svsZabLib = new SvsZabbixLib();
$svsZabLib->setDebug($DEBUG);
if ($DEBUG) echo "<pre>";
//Recupera o Mapa
//$mapinfo = $svsZabLib->getSysmapByName('Mapa_Base_GMaps_0');
$mapinfo = $svsZabLib->getSysmapByName('Infovia');

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
	<script src="leaflet/Path.Label.js"></script>
	<script src="leaflet/Map.Label.js"></script>
	<script src="leaflet/FeatureGroup.Label.js"></script>

	<link rel="SHORTCUT ICON" HREF="leaflet/wea/owm.ico">
	<link rel="stylesheet" type="text/css" href="leaflet/wea/leaflet-openweathermap.css" />
	<link rel="stylesheet" type="text/css" href="geral.css" />
	<script type="text/javascript" src="leaflet/wea/leaflet-openweathermap.js"></script>

	<script src="https://code.jquery.com/jquery-2.2.3.min.js"></script>

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
	
	//imagens utilizadas nos mapas
	function addMarkersToMap(map) {
	var myIcon = L.icon({
		iconUrl: 'images/torre.png',
		iconSize: [27, 37],
		iconAnchor: [10, 10],
		labelAnchor: [6, 0] // as I want the label to appear 2px past the icon (10 + 2 - 6)
	});

	var customOptions =
		{
			'maxWidth': '500',
			'className' : 'custom'
	}



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
	
			$link_banda = $svsZabLib->getSysmapLinksBanda($hostGroups[$i]['groupid']);
			$alarme_site = $svsZabLib->getAlarmeSite($hostGroups[$i]['groupid']);

	
//			if ($DEBUG) print_r($link_banda);
//			if ($DEBUG) print_r($link_banda[0]['banda']);

			if ($DEBUG) print_r($alarme_site);
			if ($DEBUG) print_r($alarme_site[0]['host']);
			if ($DEBUG) print_r($alarme_site[1]['host']);


			//pegar valor de banda se houver
	                echo "*/";
			if (($link_banda[0]['banda']!='')){
				$banda="<span class=\"leaflet-label-link\">".$link_banda[0]['name']." :<span style=\"color:blue;\"> ".$link_banda[0]['banda']." Mbps</span>";
			} else {
				$banda="";
			};

			 //pegar alarme se houver
                        if (($alarme_site[0]['host']!='')){
                                $alarme="<span style=\"color:red;\">".$alarme_site[0]['description']."</span>";
                                ?>
				L.circle([<?=$hostGroups[$i]['lat']?>,<?=$hostGroups[$i]['lon']?>], 9500,
                                		{color: 'red', fillColor: '#f03', fillOpacity: 0.5}).bindPopup('<?=$alarme?>').addTo(map);

                        <?php
                        } else {
                                $alarme="";
                        };



		?>

			var customPopup = "<a target='_blank' href='/zabbix/zabbix.php?action=map.view&sysmapid=<?=$mapaDetalhado['sysmapid']?>'>" +
                        "<?=$hostGroups[$i]['label']?> - Infraestrutura" +
                        "</a>" ;
//			var marker<?=$hostGroups[$i]['groupid']?> = new L.LatLng(<?=$hostGroups[$i]['lat']?>,<?=$hostGroups[$i]['lon']?>);
			L.marker([<?=$hostGroups[$i]['lat']?>,<?=$hostGroups[$i]['lon']?>], {icon: myIcon}).bindLabel('<?=$hostGroups[$i]['label'].$banda?>', { noHide:true}).bindPopup(customPopup,customOptions).addTo(map);
			
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
			var polyliner<?=$hostGroups[$i]['groupid']?> = new L.Polyline(polylinePoints, polylineOptions);
			map.addLayer(polyliner<?=$hostGroups[$i]['groupid']?>);
		<?php
		$i++;
		}
		?>
			// zoom the map to the polyline
			//map.fitBounds(polyline.getBounds());
	}

	//Função que mostra a Longitude e Latitude no cursor do Mouse
	function cursorLonLat() {
		L.CursorHandler = L.Handler.extend({
			addHooks: function () {
				this._popup = new L.Popup();
				this._map.on('mouseover', this._open, this);
				this._map.on('mousemove', this._update, this);
				this._map.on('mouseout', this._close, this);
			},
			removeHooks: function () {
				this._map.off('mouseover', this._open, this);
				this._map.off('mousemove', this._update, this);
				this._map.off('mouseout', this._close, this);
			},
			_open: function (e) {
				this._update(e);
				this._popup.openOn(this._map);
			},
			_close: function () {
				this._map.closePopup(this._popup);
			},
			_update: function (e) {
				this._popup.setLatLng(e.latlng)
				.setContent(e.latlng.toString());
			}
    		});
		L.Map.addInitHook('addHandler', 'cursor', L.CursorHandler);
	}


	function init() {

		var openWeatherMapKey = '46060fbef173cdd54a18b2fcf65bd884';
		var clouds = L.OWM.clouds({showLegend: true, opacity: 0.8, appId: '46060fbef173cdd54a18b2fcf65bd884'});
		var rain = L.OWM.rain({showLegend: true, opacity: 0.8, appId: '46060fbef173cdd54a18b2fcf65bd884'});
		var precipitation = L.OWM.precipitation({showLegend: true, opacity: 0.8, appId: '46060fbef173cdd54a18b2fcf65bd884'});
		var precipitationClassic = L.OWM.precipitationClassic({showLegend: true, opacity: 0.8, appId: '46060fbef173cdd54a18b2fcf65bd884'});
		var temperature = L.OWM.temperature({showLegend: true, opacity: 0.8, appId: '46060fbef173cdd54a18b2fcf65bd884'});
		var city = L.OWM.current({intervall: 15, lang: 'pt_br', appId: '46060fbef173cdd54a18b2fcf65bd884'});

		// Initialise base map layers
		var osmStandard =
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png?appid=46060fbef173cdd54a18b2fcf65bd884', {
			attribution: '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
			maxZoom: 19
	          })

		var mapbox = 
			L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1Ijoibm9jc2F2aXNjZ3IiLCJhIjoiY2szYWgwam9lMGFldDNucWxvZmYyMjhxZSJ9.r0NSBE9cUF6Wsz04v7DAAA', {
			attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
			maxZoom: 18,
			id: 'mapbox.streets',
			accessToken: 'pk.eyJ1Ijoia2xlYmVydGFmZmFyZWwiLCJhIjoiY2puMHFoeDNuNHNnNjNxbnl1d3A0MDlmaSJ9.FliRQ8BNbB5NGpgtH9lkpQ'
		})
		var mapout =
                        L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1Ijoibm9jc2F2aXNjZ3IiLCJhIjoiY2szYWgwam9lMGFldDNucWxvZmYyMjhxZSJ9.r0NSBE9cUF6Wsz04v7DAAA', {
                        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                        maxZoom: 18,
                        id: 'mapbox.outdoors',
                        accessToken: 'pk.eyJ1Ijoia2xlYmVydGFmZmFyZWwiLCJhIjoiY2puMHFoeDNuNHNnNjNxbnl1d3A0MDlmaSJ9.FliRQ8BNbB5NGpgtH9lkpQ'
                })

		var OpenStreetMap_Detail= L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
			maxZoom: 20,
			attribution: '&copy; Openstreetmap | &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		});

		var thunderforest =  L.tileLayer('https://tile.thunderforest.com/cycle/{z}/{x}/{y}.png?apikey=3eb76fa6db204028a691ab37a0275a62', {
			maxZoom: 18
		});

		var OpenMapSurfer_Roads = L.tileLayer('https://maps.heigit.org/openmapsurfer/tiles/roads/webmercator/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: 'Imagery from <a href="http://giscience.uni-hd.de/">GIScience Research Group @ University of Heidelberg</a> | Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		});
		
		var darkmatter = L.tileLayer('http://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png',{ attribution: '© OpenStreetMap contributors, © CartoDB'});

		var midnigth = L.tileLayer('https://cartocdn_{s}.global.ssl.fastly.net/base-midnight/{z}/{x}/{y}.png',{ attribution: '© OpenStreetMap contributors, © CartoDB'});


		// Initialise overlay layers
		var hiking =
			L.tileLayer('//tile.lonvia.de/hiking/{z}/{x}/{y}.png', {
			maxZoom: 18
		})

		var cycling =
			L.tileLayer('//tile.waymarkedtrails.org/cycling/{z}/{x}/{y}.png', {
			maxZoom: 18
		})

		var TonerLines = L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner-lines/{z}/{x}/{y}.{ext}', {
			attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
			subdomains: 'abcd',
			minZoom: 0,
			maxZoom: 20,
			ext: 'png'
		});

		var TonerLabels = L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner-labels/{z}/{x}/{y}.{ext}', {
			attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
			subdomains: 'abcd',
			minZoom: 0,
			maxZoom: 20,
			ext: 'png'
		});


		var srdt = new L.KML("leaflet/kml/coberturasrdt.kml", {async: true});
		var sisfron = new L.KML("leaflet/kml/sisfronfase1.kml", {async: true});
		var proxys = new L.KML("leaflet/kml/proxyinfovia.kml", {async: true});

		// Name the layers
		var baseMaps = {
	            'OpenStreetMap standard': osmStandard,
		    'Mapbox': mapbox,
		    'Mapbox Out': mapout,
        	    'OpenStreetMapDetail': OpenStreetMap_Detail,
		    'Estradas': OpenMapSurfer_Roads,
		    'Floresta': thunderforest,
		    'DarkMatter': darkmatter,
		    'MidNigth': midnigth	    	
	        };

		var overlayMaps = {
			'Nuvens': clouds,
			'Chuva': rain,
			'Precipitação': precipitation,
			'Precipitação Legendada': precipitationClassic,
			'Temperatura': temperature,
			'Clima': city,
			'SRDT': srdt,
			'Sisfron Fase 1': sisfron,
			'Zabbix Proxys' : proxys,
			'Divisas':TonerLines,
			'Labels':TonerLabels

		};

		//chamar função do cursor do mouse com Lon/Lat
		if (document.getElementById('option3').checked)
			cursorLonLat();

		var map = L.map('map', {
			center: [-22.00,-55.40],
			zoom: 8,
			cursor: true,
			layers: [city,sisfron]
		});


		//Adicionar imagem de fundo no mapa
		L.Control.Watermark = L.Control.extend({
			onAdd: function(map) {
				var img = L.DomUtil.create('img');
				img.src = 'images/sisfron_transp_mapa.png';
				img.style.width = '380px';
				return img;
			},
		
			onRemove: function(map) {
				// Nothing to do here
			}
		});
		L.control.watermark = function(opts) {
			return new L.Control.Watermark(opts);
		}
		L.control.watermark({ position: 'bottomright' }).addTo(map);


		// Add our preferred default layer choice directly to the map
                map.addLayer(mapout);


        	// Add the layer picker control
	        var layerControl = L.control.layers(baseMaps, overlayMaps).addTo(map);
		L.control.scale().addTo(map);
	
	        map.attributionControl.setPrefix('Powered by Leaflet'); // Don't show the 'Powered by Leaflet' text. Attribution overload
		//map.attributionControl.setPrefix(''); // Don't show the 'Powered by Leaflet' text.

		addMarkersToMap(map);        
		addPolylineToMap(map);

		//Rotina para setar tempos de exibicao de cada parte do mapa,sendo que o mapa pode rotacionar ou nao dependendo da opcao marcada no checkbox
		if (!document.getElementById('option2').checked)
		{
			if (document.getElementById('option1').checked)
			{
				setTimeout(function(){
					map.setView([-23.10,-55.40],9);
					console.log("setInterval de 4 segundo parte norte");
				},10000);
				setTimeout(function(){
//					map.setView([-20.95,-55.40]);
					map.setView([-19.80,-55.10]);
					console.log("setTimeOut Centro");
				}, 20000);
			}

			setInterval(function(){
				map.setView([-21.20,-55.40],9);
				setTimeout(function(){
					map.setView([-22.00,-55.40],8);
				}, 2000);
			}, 30000);
	
			setTimeout(function(){
				window.location.reload();
			}, 60000);
		}
	}

   </script>
</head>


<body onLoad="javascript:init();">

   <div id="map" style="height: 890px"></div>
	<table>
		<tr>
			<td class="pagina-principal-topo">
				<div id="checkbox-container">				
					<div id="checkbox-rotacionar"><input type="checkbox" id="option1"/><label class="labelcalcrota" for="txtAreaCobertura"> Rotacionar Mapa </label></div>
					<div id="congelar"><input type="checkbox" id="option2"/><label class="labelcalcrota" for="txtAreaCobertura"> Congelar Mapa </label></div>
					<div id="latitude"><input type="checkbox" id="option3"/><label class="labelcalcrota" for="txtAreaCobertura"> Cursor Lat/Lon </label></div>			
				</div>
			</td>
		</tr>

	</table>



<script language="javascript">

var formValues = JSON.parse(localStorage.getItem('formValues')) || {};
var $checkboxes = $("#checkbox-container :checkbox");
var $button = $("#checkbox-container button");

function allChecked(){
  return $checkboxes.length === $checkboxes.filter(":checked").length;
}

function updateButtonStatus(){
  $button.text(allChecked()? "Uncheck all" : "Check all");
}

function handleButtonClick(){
  $checkboxes.prop("checked", allChecked()? false : true)
}

function updateStorage(){
  $checkboxes.each(function(){
    formValues[this.id] = this.checked;
  });

  formValues["buttonText"] = $button.text();
  localStorage.setItem("formValues", JSON.stringify(formValues));
}

$button.on("click", function() {
  handleButtonClick();
  updateButtonStatus();
  updateStorage();
});

$checkboxes.on("change", function(){
  updateButtonStatus();
  updateStorage();
});

// On page load
$.each(formValues, function(key, value) {
  $("#" + key).prop('checked', value);
});

$button.text(formValues["buttonText"]);

</script>

</body>                                                                                                                          
</html>
