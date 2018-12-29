<?php

/*
The MIT License (MIT)

Copyright (c) 2018 Jun Masuda/mash studio.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

    require_once("incs/config.inc.php");
    require_once("incs/constant.php");
    require_once("lib/simple_pg.php");

    $mode = $_GET['mode'];
    if ($mode == 'location') targetLocation();          
    if ($mode == 'best3') best3();
    if ($mode == 'kite') targetKite();
    if ($mode == 'score') score();
    exit();

    function targetLocation() {
        $target_name = $_GET['name'];
        $target_name = str_replace(array(" "," "),"",$target_name);  //delete space
        $targetInfo = searchLocationInfo($target_name);

        //Not found location in DB
        if (count($targetInfo["info"]) == 0) {
            $result_json = json_encode(array("result"=>"NotFoundLocation"));
            echo $result_json;
            return;
        }

        $windRating = $targetInfo['info']['windRating'];
        $condition = 0;
        if ($windRating >= 0.9) { $condition = 2; }
        if (($windRating >= 0.8) && ($windRating < 0.9)) { $condition = 1; }

        $target_data = array(
            "name"=>$targetInfo['info']['name'],
            "hour"=>substr($targetInfo['info']['time'],0,2),
            "wind"=>sprintf("%0.1f",$targetInfo['info']['wind']),
            "windDirectionAngle"=>$targetInfo['info']['wind_direction_angle'],
            "windDirectionJp"=>$targetInfo['info']['wind_direction_jp'],
        );

        //Condition bad
        if ($condition == 0) {
            if ($targetInfo['ranking'] > 1) {
                //If it is not 1st rank, suggest alternative place.
                $locations = getLocationInfo(HOME_LATITUDE,HOME_LONGITUDE);
                sortArrayWithKey($locations,'score_favorite_wind_distance',SORT_DESC);
                
                $alternative_data = array(
                    "name"=>$locations[0]['name'],
                    "hour"=>substr($locations[0]['time'],0,2),
                    "wind"=>sprintf("%0.1f",$locations[0]['wind']),
                    "windDirectionAngle"=>$locations[0]['wind_direction_angle'],
                    "windDirectionJp"=>$locations[0]['wind_direction_jp'],
                );
                $recommendKite = recommendKiteTypeString($alternative_data['wind']);
                $result_json = json_encode(array("result"=>"condition_bad","target"=>$target_data,"alternative"=>$alternative_data,"recommendKite"=>$recommendKite));
            } else {
                //If it is 1st rank. Do not suggest alternative place.
                $result_json = json_encode(array("result"=>"condition_bad","target"=>$target_data,"alternative"=>"none"));
            }
        }
        //Condition normal
        if ($condition == 1) {
            $recommendKite = recommendKiteTypeString($target_data['wind']);
            $result_json = json_encode(array("result"=>"condition_normal","target"=>$target_data,"alternative"=>"none","recommendKite"=>$recommendKite));

        }
        //Condition Best
        if ($condition == 2) {
            $recommendKite = recommendKiteTypeString($target_data['wind']);
            $result_json = json_encode(array("result"=>"condition_good","target"=>$target_data,"alternative"=>"none","recommendKite"=>$recommendKite));
        }
        echo $result_json;
    }

    //Suggest best 3 location
    function best3() {
        //load locations
        $locations = getLocationInfo(HOME_LATITUDE,HOME_LONGITUDE);

        //Sort score
        sortArrayWithKey($locations,'score_favorite_wind_distance',SORT_DESC);

        //Select 3 places
        $count = 0;
        $best3Locations = array();
        for($i=0;$i<count($locations);$i++) {
            $location = $locations[$i];
            if ($location['score_favorite_wind_distance'] > 0.0) {
                array_push($best3Locations,$location);
                $count++;
                if ($count >= 3) {
                    break;
                }
            }
        }
        
        $result = array();
        for($i=0;$i<count($best3Locations);$i++) {
            $location = $best3Locations[$i];
            $hour = substr($location['time'],0,2);
            $recommendKite = recommendKiteTypeString($location['wind']);
            $location_data = array(
                "name"=>$location['name'],
                "hour"=>$hour,
                "wind"=>sprintf("%0.1f",$location['wind']),
                "windDirectionAngle"=>$location['wind_direction_angle'],
                "windDirectionJp"=>$location['wind_direction_jp'],
                "recommendKite"=>$recommendKite,
            );
            array_push($result,$location_data);
        }
        $result_json = json_encode($result);
        echo $result_json;
    }

    //Suggest best place by kite type.
    function targetKite() {
        $target_kitetype = $_GET['type'];
        $kite_info = kiteInfo($target_kitetype);

        //Not found kite type.
        if (count($kite_info) == 0) {
            $result_json = json_encode(array("result"=>"NotFoundKite"));
            echo $result_json;
            return;
        }

       //load locations
       $locations = getLocationInfo(HOME_LATITUDE,HOME_LONGITUDE);
       //Sort score
       sortArrayWithKey($locations,'score_favorite_wind_distance',SORT_DESC);

       //Select location
       $targetKiteTypeLocations = array();
       for($i=0;$i<count($locations);$i++) {
            $location = $locations[$i];
            if (($location['wind'] >= $kite_info['wind_min']) && ($location['wind'] < $kite_info['wind_max'])) {
               array_push($targetKiteTypeLocations,$location);
            }
        }

        if (count($targetKiteTypeLocations) > 0) {
            $hour = substr($targetKiteTypeLocations[0]['time'],0,2);
            $location_data = array(
                "name"=>$targetKiteTypeLocations[0]['name'],
                "hour"=>$hour,
                "wind"=>sprintf("%0.1f",$targetKiteTypeLocations[0]['wind']),
                "windDirectionAngle"=>$targetKiteTypeLocations[0]['wind_direction_angle'],
                "windDirectionJp"=>$targetKiteTypeLocations[0]['wind_direction_jp'],
            );
        } else {
            $result_json = json_encode(array("result"=>"NotFoundLocation","kite"=>$kite_info));
            echo $result_json;
            return;
        }

        $result = array("result"=>"OK","location"=>$location_data,"kite"=>$kite_info);
        $result_json = json_encode($result);
        echo $result_json;

    }

    //display location and score. (debug command)
    function score() {
        //load locations
        $locations = getLocationInfo(HOME_LATITUDE,HOME_LONGITUDE);
        
        //Sort score
        sortArrayWithKey($locations,'score_favorite_wind_distance',SORT_DESC);

        for($i=0;$i<count($locations);$i++) {
            $location = $locations[$i];
            print $location['name']." , date=".$location['date']." , time=".$location['time']." , wind=".$location['wind']." , distance=".$location['distance']." , fR=".$location['favoriteRating']." , wR=".$location['windRating']." , dR=".$location['distanceRating']." , FWscore=".$location['score_favorite_wind']." , FWDscore=".$location['score_favorite_wind_distance'];
            print "<br>";
        }
    }

    //Get location data
    function getLocationInfo($lat,$lng) {

        //load locations
        $dbid = db_connect(DB_HOST,DB_PORT,DB_NAME,DB_USER_NAME,DB_PWD);

        $sql = "select * from location_table,weather_table where location_table.pkey = weather_table.location_table_pkey and delflag=0";
        $result = db_query($dbid,$sql);
        if (!$result) {
            db_close($dbid);
            die("db location_table select error");
        }
        $locations_result = db_fetch_all($result);

        $locations = array();
        foreach ($locations_result as $row) {
                       
            //Calculate distance
            $distance = calcDistanceSphericalTrigonometry($lat,$lng,$row['latitude'],$row['longitude']) / 1000.0; //単位km
            
            //get rating
            $favoriteRating = $row['rating'];
            $windRating = windRating($row['wind']);
            $distanceRating = pow(0.993023,$distance);      //distance 100km = 0.5

            //Calcurate score
            $scoreFavioriteWind = $favoriteRating * $windRating;
            $scoreFavoriteWindDistance = $favoriteRating * $windRating * $distanceRating; 

            $data = array(
                'pkey'=>$row['pkey'],
                'name'=>$row['name'],
                'keyword'=>$row['keyword'],
                'latitude'=>$row['latitude'],
                'longitude'=>$row['longitude'],
                'distance'=>$distance,
                'favoriteRating'=>$favoriteRating,
                'windRating'=>$windRating,
                'distanceRating'=>$distanceRating,
                'date'=>$row['date'],
                'time'=>$row['time'],
                'wind'=>$row['wind'],
                'wind_direction_angle'=>$row['wind_direction_angle'],
                'wind_direction_jp'=>$row['wind_direction_jp'],                    
                'score_favorite_wind'=>$scoreFavioriteWind,
                'score_favorite_wind_distance'=>$scoreFavoriteWindDistance
            );

            array_push($locations,$data);
        }

        db_close($dbid);

        return $locations;
    }

    //Get place information
    function searchLocationInfo($target_location_name) {
        $locations = getLocationInfo(HOME_LATITUDE,HOME_LONGITUDE);
        //Sort score
        sortArrayWithKey($locations,'score_favorite_wind_distance',SORT_DESC);

        //select target
        $targetInfo = [];
        $ranking = 99999;
        for($i=0;$i<count($locations);$i++) {
            //echo $locations[$i]['keyword']."<br>";
            if(strpos($locations[$i]['keyword'],$target_location_name) !== false) {
                $targetInfo = $locations[$i];
                $ranking = $i;
                break;
            }
        }

        return array('info'=>$targetInfo,'ranking'=>$ranking);
    }

    // convert degree to radian
    function rad($deg) {
        $rad = $deg * M_PI / 180;
        return $rad;
    }

    // Calcurate distance
    function calcDistanceSphericalTrigonometry($lat1, $lng1, $lat2, $lng2) {
        $R = 6378137.0;
        return $R *
            acos(
                cos(rad($lat1)) *
                cos(rad($lat2)) *
                cos(rad($lng2) - rad($lng1)) +
                sin(rad($lat1)) *
                sin(rad($lat2))
            );
    }

    // Sort Array
    function sortArrayWithKey( &$array, $sortKey, $sortType = SORT_ASC ) {
        $tmpArray = array();
        foreach ( $array as $key => $row ) {
            $tmpArray[$key] = $row[$sortKey];
        }
        array_multisort( $tmpArray, $sortType, $array );
        unset( $tmpArray );
    }

    //Calcurate wind rating
    function windRating($wind) {
        global $kite_data;

        $recommendationKiteCount = 0;
        $kiteRating = [
            0.0,
            0.6,
            0.8,
            0.9,
            1.0,
        ];
        
        for($i=0;$i<count($kite_data);$i++) {
            $kite = $kite_data[$i];
            if (($wind >= $kite['wind_min']) && ($wind < $kite['wind_max'])) {
                $recommendationKiteCount++;
            }
        }
        if ($recommendationKiteCount >= count($kiteRating)-1) $recommendationKiteCount = count($kiteRating)-1;

        return $kiteRating[$recommendationKiteCount];
    }

    //Recommend kite type
    function recommendKiteTypeString($wind) {
        global $kite_data;
        
        $kiteTypeString = "";
        for($i=0;$i<count($kite_data);$i++) {
            $kite = $kite_data[$i];
            //echo $kite."<br>";
            if (($wind >= $kite['wind_min']) && ($wind < $kite['wind_max'])) {
                $kiteTypeString = $kiteTypeString.$kite['type']."、";
            }
        }
        return $kiteTypeString;
    }

    //Convert 24Hour to 12Hour
    function convertHour24to12($hour) {
        $hour12 = $hour % 12;
        $ampm = 0;
        if ($hour >= 12) {
            $ampm = 1;
        }
        return array($ampm,$hour12);
    }

    //Get kite information
    function kiteInfo($kitetype) {
        global $kite_data;

        $kite_info = [];
        for($i=0;$i<count($kite_data);$i++) {
            $kite_info = $kite_data[$i];
            if(strpos($kite_info['keyword'],$kitetype) !== false) {
                break;
            }
        }
        return $kite_info;
    }

?>
