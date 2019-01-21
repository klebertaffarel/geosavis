<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 16/09/2016
 * Time: 12:08
 */
//echo "<BR>SvsZabbixLib include: ";
//echo dirname(__FILE__).'/includes/autoload.php';
require_once(dirname(__FILE__).'/includes/autoload.php');
ini_set('display_errors', E_ALL);


class SvsZabbixLib{

    private $debug = false;

    function SvsZabbixLib(){
        if ($this->debug) echo "iniciando SvsZabbixLib";

//        echo "iniciando SvsZabbixLibss";

        $this->conexao = new SvsConexao();
        $this->conexao->setDebug($this->debug);
        if ($this->debug) echo "<br>SvsConexao inicializada";
        $this->conexao->conectar();
        if ($this->debug) echo "<br>SvsConexao conectado";
    }

    function setDebug($deb){
        $this->debug = $deb;
    }

    function getHostInfo($host_id){
        if ($this->debug) echo 'recuperando informacoes do host='.$host_id;

        $hosts = new SvsGrupos();
        if ($this->debug) echo "\n<br>SvsGrupos instanciado";
        $host_info = $hosts->get_host($host_id);

        if ($this->debug) echo 'getHostInfo finalizada';
        return $host_info;
    }

    function getSysmapByName($sysmap_name){
        //Executa a query
        $sql_map_id = "SELECT * FROM sysmaps where name='".$sysmap_name."'";
//        $sql_map_id = "SELECT * FROM sysmaps";
        if ($this->debug) echo "\n<br>Executando query: ".$sql_map_id;
        $ar_result = $this->conexao->exec_query($sql_map_id);
        if ($this->debug) echo "<BR>Resultado_ar_result:<BR>";
//        print_r($ar_result);

        //Retorna os dados
        $mapinfo = null;
        if (count($ar_result)===1){
            $mapinfo = $ar_result[0];
        }

        return $mapinfo;
    }

    function getSysmapElements($sysmapid, $element_type=null){
        $sql_sysmap_elements = "SELECT * FROM sysmaps_elements WHERE sysmapid=".$sysmapid;
        if ($element_type!==null){
            $sql_sysmap_elements .= " AND elementtype=".$element_type;
        }
        if ($this->debug) echo "\n<br>sql_sysmap_elements: ".$sql_sysmap_elements;

        $ar_elements = $this->conexao->exec_query($sql_sysmap_elements);

        print_r($ar_elements);
    }

    function getSysmapLinks($sysmapid){
/*
SELECT 
	sysmaps_link_triggers.linktriggerid,
	sysmaps_link_triggers.linkid,
	sysmaps_link_triggers.triggerid,
	sysmaps_links.sysmapid,
	triggers.description,
	triggers.value,
	triggers.status,
	triggers.state
FROM sysmaps_link_triggers
LEFT JOIN sysmaps_links 
	ON sysmaps_links.linkid = sysmaps_link_triggers.linkid
LEFT JOIN triggers
	ON triggers.triggerid = sysmaps_link_triggers.triggerid
WHERE sysmapid=17
	;
*/

		//Versao que precisa, necessariamente, ter triggers de status para os links
/*		$sql_sysmap_links = " 
		    SELECT 
				sysmaps_link_triggers.linkid,
				GROUP_CONCAT(sysmaps_link_triggers.linktriggerid),
			#	sysmaps_link_triggers.triggerid,
				sysmaps_links.*,
			#	triggers.description,
				SUM(triggers.value),
				SUM(triggers.status),
				SUM(triggers.state)
			FROM sysmaps_link_triggers
			LEFT JOIN sysmaps_links 
				ON sysmaps_links.linkid = sysmaps_link_triggers.linkid
			LEFT JOIN triggers
				ON triggers.triggerid = sysmaps_link_triggers.triggerid
			WHERE sysmapid=".$sysmapid."
			GROUP BY sysmaps_link_triggers.linkid
			";
*/
/*
		$sql_sysmap_links = "
			SELECT 
				sysmaps_links.*,
				(SELECT COUNT(*) FROM sysmaps_link_triggers WHERE sysmaps_link_triggers.linkid = sysmaps_links.linkid) AS num_triggers,
				(SELECT COUNT(*) FROM sysmaps_link_triggers WHERE sysmaps_link_triggers.linkid = sysmaps_links.linkid) AS sum_value
			FROM sysmaps_links
			WHERE sysmapid=".$sysmapid;
*/
			//     	$sql_sysmap_links = " SELECT * from sysmaps_links 
//                              WHERE sysmapid=".$sysmapid."
//                              ";

		$sql_sysmap_links = "




		SELECT 
		 	sysmaps_links.*,
			(SELECT	SUM(triggers.value)FROM sysmaps_link_triggers LEFT JOIN triggers ON triggers.triggerid = sysmaps_link_triggers.triggerid WHERE sysmaps_link_triggers.linkid=sysmaps_links.linkid 
                                   GROUP BY sysmaps_link_triggers.linkid) AS sum_value,	
			(SELECT COUNT(*) FROM sysmaps_link_triggers WHERE sysmaps_link_triggers.linkid = sysmaps_links.linkid) AS num_triggers,
			IF(
				(SELECT	SUM(triggers.value)FROM sysmaps_link_triggers LEFT JOIN triggers ON triggers.triggerid = sysmaps_link_triggers.triggerid WHERE sysmaps_link_triggers.linkid=sysmaps_links.linkid 
                                     GROUP BY sysmaps_link_triggers.linkid) IS NULL,
			   sysmaps_links.color,
			   IF(
				   (SELECT	SUM(triggers.value)FROM sysmaps_link_triggers LEFT JOIN triggers ON triggers.triggerid = sysmaps_link_triggers.triggerid WHERE sysmaps_link_triggers.linkid=sysmaps_links.linkid 
                                               GROUP BY sysmaps_link_triggers.linkid)=0,
					 \"00FF00\",
					if((SELECT SUM(triggers.value)FROM sysmaps_link_triggers LEFT JOIN triggers ON triggers.triggerid = sysmaps_link_triggers.triggerid
                       		   	WHERE sysmaps_link_triggers.linkid=sysmaps_links.linkid and priority = 4 GROUP BY sysmaps_link_triggers.linkid)=1,
				        \"ff9900\",
                       			\"DD0000\" )
	                             )		
			) AS cor_link
			FROM sysmaps_links
			WHERE sysmapid=".$sysmapid;



        if ($this->debug) echo "\n<br>sql_sysmap_links:".$sql_sysmap_links;

        $ar_links = $this->conexao->exec_query($sql_sysmap_links);

        return $ar_links;
    }

    function getSysmapHostsGroups($sysmapid){
        $sql_sysmap_groups = "SELECT sysmaps_elements.*,
                                       groups.*
                                FROM sysmaps_elements 
                                LEFT JOIN groups ON groups.groupid = sysmaps_elements.elementid
                                WHERE sysmaps_elements.elementtype = 3
                                  AND sysmapid=".$sysmapid;
        if ($this->debug) echo "\n<br>sql_sysmap_groups: ".$sql_sysmap_groups;

        $ar_groups = $this->conexao->exec_query($sql_sysmap_groups);
//        print_r($ar_groups);

        //Recupera informacoes dos HOSTS de cada grupo
        $i=0;
        while ($i < count($ar_groups)){
            //Recupera a lista de Hosts do grupo
            $ar_groups[$i]['hosts'] = $this->getGroupHosts($ar_groups[$i]['groupid']);

            //Calcula a latitude e longitude do Grupo
            $localizacao = $this->calcGroupLatLong($ar_groups[$i]['hosts']);

            $ar_groups[$i]['lat'] = $localizacao['lat'];
            $ar_groups[$i]['lon'] = $localizacao['lon'];

            $i++;
        }

        return $ar_groups;
    }

//Alterado por Taffarel para pegar somente grupos de radios
//31/01/2018
function getSysmapHostsGroupsRad($sysmapid){
        $sql_sysmap_groups = "SELECT sysmaps_elements.*,
                                       groups.*
                                FROM sysmaps_elements
                                LEFT JOIN groups ON groups.groupid = sysmaps_elements.elementid
                                WHERE  sysmaps_elements.elementtype = 3 
				AND sysmapid=".$sysmapid;
        if ($this->debug) echo "\n<br>sql_sysmap_groups: ".$sql_sysmap_groups;

        $ar_groups = $this->conexao->exec_query($sql_sysmap_groups);
//        print_r($ar_groups);

        //Recupera informacoes dos HOSTS de cada grupo
        $i=0;
        while ($i < count($ar_groups)){
            //Recupera a lista de Hosts do grupo
            $ar_groups[$i]['hosts'] = $this->getGroupHostsRad($ar_groups[$i]['groupid']);

            //Calcula a latitude e longitude do Grupo
            $localizacao = $this->calcGroupLatLong($ar_groups[$i]['hosts']);

            $ar_groups[$i]['lat'] = $localizacao['lat'];
            $ar_groups[$i]['lon'] = $localizacao['lon'];

            $i++;
        }

        return $ar_groups;
    }






    function calcGroupLatLong($ar_hosts){
        $i=0;
        $lats = [];
        $lngs = [];
        while ($i < count($ar_hosts)){
            if (($ar_hosts[$i]['location_lat']!='')&&($ar_hosts[$i]['location_lon']!='')){
                $lats[] = $ar_hosts[$i]['location_lat'];
                $lngs[] = $ar_hosts[$i]['location_lon'];
            }
            $i++;
        }
        $localizacao = [];
        $localizacao['lat'] = null;
        $localizacao['lon'] = null;
        if (count($lats)>0){
            $localizacao['lat'] = array_sum($lats)/count($lats);
            $localizacao['lon'] = array_sum($lngs)/count($lngs);
        }

        return $localizacao;
    }

    function getGroupHosts($groupid){
        $sql_group_hosts = "SELECT DISTINCT HG.hostgroupid, HG.hostid, HG.groupid, H.host, I.ip, I.dns, HI.location_lat, HI.location_lon 
				  FROM hosts_groups as HG , hosts as H, interface as I, host_inventory as HI
				  WHERE HG.groupid = ".$groupid." 
				  AND HG.hostid = H.hostid 
				  AND I.hostid = H.hostid
				  AND HI.hostid = H.hostid
				  ORDER BY H.host ASC";
//				  AND HI.location_lat != ''
//          		  	  AND HI.location_lon != ''

        $ar_hosts = $this->conexao->exec_query($sql_group_hosts);
//	echo "<BR><BR>\n\nsql_group_hosts: ".$sql_group_hosts;
//        $ponto_lat 	=  array_sum($lat)/count($host);
//        $ponto_lon =  array_sum($lon)/count($host);

        return $ar_hosts;
    }

     function getGroupHostsRad($groupid){
        $sql_group_hosts = "SELECT DISTINCT HG.hostgroupid, HG.hostid, HG.groupid, H.host, I.ip, I.dns, HI.location_lat, HI.location_lon
                                  FROM hosts_groups as HG , hosts as H, interface as I, host_inventory as HI
                                  WHERE HG.groupid = ".$groupid."
                                  AND HG.hostid = H.hostid
                                  AND I.hostid = H.hostid
                                  AND HI.hostid = H.hostid
			 	  AND H.host like 'RD0%'
                                  ORDER BY H.host ASC";
//                                AND HI.location_lat != ''
//                                AND HI.location_lon != ''

        $ar_hosts = $this->conexao->exec_query($sql_group_hosts);
//      echo "<BR><BR>\n\nsql_group_hosts: ".$sql_group_hosts;
//        $ponto_lat    =  array_sum($lat)/count($host);
//        $ponto_lon =  array_sum($lon)/count($host);

        return $ar_hosts;
    }



    function getMapInfo($mapid){

    }

};






?>
