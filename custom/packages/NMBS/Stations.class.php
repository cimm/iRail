<?php
/**
 * Copyright (C) 2011 by iRail vzw/asbl
 *
 * @author Jens Segers
 * @author  Pieter Colpaert <pieter aŧ iRail.be>
 * @license AGPLv3
 *
 */

include_once (dirname(__FILE__) . "/../iRailStations.class.php");

class NMBSStations extends iRailStations {
    
    /**
     * This function should get all stations from the NMBS/SNCB and return them.
     * Can't we get all information from DBPedia?
     * Does Open Street Map has a SPARQL end-point?
     */
    public function call() {
        // TODO
        $o = new StdClass();
        $o->stations = iRailTools::$railtimenames;
        return $o;
    }
    
    public static function getStationFromName($name, $lang = "en") {
        $station = self::getStationsFromName(array($name), $lang);
        return reset($station);
    }
    
    public static function getStationIdFromName($name, $lang = "en") {
        $station = self::getStationIdsFromName(array($name), $lang);
        return reset($station);
    }
    
    public static function getStationIdsFromName($names, $lang = "en") {
        $stations = self::getStationsFromName($names, $lang);
        
        $ids = array();
        foreach($names as $i=>$name)
            $ids[$name] = $stations[$i]->id;
            
        return $ids;
    }
    
    public static function getStationsFromName($names, $lang = "en") {
        $url = "http://hari.b-rail.be/Hafas/bin/extxml.exe";
        
        $post = '<?xml version="1.0 encoding="iso-8859-1"?>
        		 	<ReqC ver="1.1" prod="iRail API v1.0" lang="' . $lang . '">';
        
        $i = 1;
        foreach($names as $name) {
            $post .= '<LocValReq id="stat'.$i.'" maxNr="1">
                      	<ReqLoc match="' . $name . '" type="ST"/>
                      </LocValReq>';
        }
        $post .= '</ReqC>';
        
        $options = array("method"=>"POST", "data"=>$post);
        $request = TDT::HttpRequest($url, $options);
        
        if (isset($request->error)) {
            throw new HttpOutTDTException($url);
        }
        
        $stations = array();
        
        $data = new SimpleXMLElement($request->data);
        foreach($data->LocValRes as $elem) {
            $station = new stdClass();
            
            $name = (string) $elem->Station["name"];
            $name = str_replace(array(" [B]", " (nl)", " (fr)", " (de)"), "", $name);
            
            $station->name = $name;
            $station->id = (string) $elem->Station["externalStationNr"];
            $station->longitude = (double)(substr($elem->Station["x"],0, -6).".".substr($elem->Station["x"], -6));
            $station->latitude = (double)(substr($elem->Station["y"],0, -6).".".substr($elem->Station["y"], -6));
            $station->type = (string) $elem->Station["type"];
             
            $stations[] = $station;
        }
        
        return $stations;
    }
}

?>