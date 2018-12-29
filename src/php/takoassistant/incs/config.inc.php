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

    //PHP設定
    ini_set( 'display_errors', 1 );
    ini_set( 'max_execution_time',60);
	date_default_timezone_set('Asia/Tokyo');

    //Weather auto update
    define("WEATHER_AUTO_UPDATE","1");
    
    //Database 
    define("DB_HOST", "<DB_ADDRESS>");
    define("DB_PORT", "<DB_PORT>");
    define("DB_USER_NAME", "<DB_USER_NAME>");
    define("DB_PWD", "<DB_PASSWORD>");
    define("DB_NAME", "<DB_NAME>");

    define("HOME_LATITUDE","34.822602");
    define("HOME_LONGITUDE","137.396672");
    
    //Darksky API
    define("DARKSKY_API_URL","https://api.darksky.net/forecast/<DARKSKY_API_KEY>/");
    define("DARKSKY_API_URL_OPTION","?lang=ja&units=si")

?>
