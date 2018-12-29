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

	function db_connect($sv,$port,$dbname,$dbuser,$dbpwd){
		$conn = pg_connect("dbname=".$dbname." host=".$sv." port=".$port." user=".$dbuser." password=".$dbpwd);
		if (!$conn) {
			die("db connect error");
        }
		
		return $conn;
	}	

	function db_close($conn) {
		pg_close($conn);
	}

	function db_query($conn,$sql) {
		$res = pg_query($conn,$sql) or die("sql query error.");
		return $res;
	}

	function db_record_count($res) {
		return pg_num_rows($res);
	}

	function db_fetch_all($res) {
		return pg_fetch_all($res);
	}

	function db_fetch_assoc($res) {
		return pg_fetch_assoc($res);
	}
?>
