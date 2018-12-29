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
    require_once("lib/simple_pg.php");

    //自動更新しない場合は終了
    if (WEATHER_AUTO_UPDATE == 0) {
        exit();
    };

    //load locations
    $dbid = db_connect(DB_HOST,DB_PORT,DB_NAME,DB_USER_NAME,DB_PWD);
    
    $sql = "select * from location_table where delflag=0";
    $locations_result = db_query($dbid,$sql);
    if (!$locations_result) {
        db_close($dbid);
        die("db select error");
    }

    $locations = db_fetch_all($locations_result);
    
    foreach ($locations as $row) {
        $url = DARKSKY_API_URL . $row['latitude'] . "," . $row['longitude'] . DARKSKY_API_URL_OPTION;
        
        $contents = file_get_contents($url);
        $decode = json_decode($contents, true);
        
        $pkey = $row['pkey'];
        $current_data = $decode['currently'];
        $date = date('Y-m-d',$current_data['time']);
        $time = date('H:i:s',$current_data['time']);
        $wind_speed = $current_data['windSpeed'];
        $wind_direction_angle = $current_data['windBearing'];
        $wind_direction_jp = convert_wind_direction_string($wind_direction_angle);
        
        $sql = "select * from weather_table where location_table_pkey=".$pkey;
        $weather_result = db_query($dbid,$sql);
        if (!$weather_result) {
            db_close($dbid);
            die("db select error 2");
        }
        if (db_record_count($weather_result) > 0) {
            $wrow = db_fetch_assoc($weather_result);
            $sql = "update weather_table set date='".$date."',time='".$time."',wind='".$wind_speed."',wind_direction_angle='".$wind_direction_angle."',wind_direction_jp='".$wind_direction_jp."' where location_table_pkey='".$pkey."'";
            $result = db_query($dbid,$sql);

        } else {
            $sql = "insert into weather_table (location_table_pkey,date,time,wind,wind_direction_angle,wind_direction_jp) values ('".$pkey."','".$date."','".$time."','".$wind_speed."','".$wind_direction_angle."','".$wind_direction_jp."')";
            $result = db_query($dbid,$sql);
        }
    }
    db_close($dbid);

    //Convert wind distance angle to string
    function convert_wind_direction_string($wind_direction) {
        $wind_direction_string_array = array(
            "北",
            "北北東",
            "北東",
            "東北東",
            "東",
            "東南東",
            "南東",
            "南南東",
            "南",
            "南南西",
            "南西",
            "西南西",
            "西",
            "西北西",
            "北西",
            "北北西",
            "北",
        );
        $angle_skip = 360.0 / (count($wind_direction_string_array)-1);

        $from_angle = -$angle_skip/2;
        for($i=0;$i<count($wind_direction_string_array);$i++) {
            $to_angle = $from_angle + $angle_skip;

            if (($wind_direction >= $from_angle) && ($wind_direction < $to_angle)) {
                return $wind_direction_string_array[$i];
            }
            $from_angle += $angle_skip;
        }
        return "未定義";
    }
    ?>
