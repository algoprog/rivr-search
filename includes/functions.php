<?php

function multiCurl($requests, $cookies)
{
    // init curl multi handle
    $mh = curl_multi_init();
    // create running flag
    $running = null;
    // cycle through requests and set up
    foreach ($requests as $key => $request){
        // init individual curl handle
        $chs[$key] = curl_init();
        // set url
        curl_setopt($chs[$key], CURLOPT_URL, $request['url']);
        // check for post data and handle if present
        if($request['post_data']){
            curl_setopt($chs[$key], CURLOPT_POST, 1);
            curl_setopt($chs[$key], CURLOPT_POSTFIELDS, $request['post_array']);
        }
		
		/* Uncomment to use proxy */
		/*
		$proxylist = file(dirname(__FILE__).'/http_proxies.txt');
		if(count($proxylist)){
			$line = $proxylist[array_rand($proxylist)];
			$proxy = $line;
			curl_setopt($chs[$key], CURLOPT_PROXYTYPE, 'HTTP');
			curl_setopt($chs[$key], CURLOPT_PROXY, $proxy);
		}
		*/
	
		curl_setopt($chs[$key], CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)");
		
		$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
		$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[] = "Cache-Control: max-age=0";
		$header[] = "Connection: keep-alive";
		$header[] = "Keep-Alive: 300";
		$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[] = "Accept-Language: en-US;q=0.6,en;q=0.4";
		$header[] = "Pragma: ";
		
		curl_setopt($chs[$key], CURLOPT_HTTPHEADER, $header);
		curl_setopt($chs[$key], CURLOPT_ENCODING , "");
		curl_setopt($chs[$key], CURLOPT_HEADER, 0);
		curl_setopt($chs[$key], CURLOPT_VERBOSE, true);
		curl_setopt($chs[$key], CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chs[$key], CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($chs[$key], CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($chs[$key], CURLOPT_COOKIE, $cookies);
		curl_setopt($chs[$key], CURLOPT_FOLLOWLOCATION, false);
		
        curl_multi_add_handle($mh, $chs[$key]);
    }
	$running = 1;
    while($running > 0){
        // execute curl requests
        curl_multi_exec($mh, $running);
        // block to avoid needless cycling until change in status
        curl_multi_select($mh);
		// check flag to see if we're done
    }
    // cycle through requests
    foreach($chs as $key => $ch){
        // handle error
        if(curl_error($ch)){
            $responses[$key] = null;
			file_put_contents(dirname(__FILE__).'/errors.txt', '['.date('l jS F Y h:i:s A').']: Curl error: '.curl_error($ch)."\r\n", FILE_APPEND | LOCK_EX);
        }else{
            // save successful response
			$data = curl_multi_getcontent($ch);
            $responses[$key] = $data;
        }
        // close individual handle
        curl_multi_remove_handle($mh, $ch);
    }
    // close multi handle
    curl_multi_close($mh);
    // return respones
    return $responses;
}

function clean_text($str){
	return mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $str));
}

function between($string, $start, $end){
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
}

function strToBytes($str){
	$w = explode(' ',$str);
	if($w[1]=='TB') $size = floatval($w[0])*1024*1024*1024*1024;
	elseif($w[1]=='GB') $size = floatval($w[0])*1024*1024*1024;
	elseif($w[1]=='MB') $size = floatval($w[0])*1024*1024;
	elseif($w[1]=='KB') $size = floatval($w[0])*1024;
	elseif($w[1]=='B') $size = floatval($w[0]);
	return $size;
}

function bytesToStr($bytes, $precision = 2){  
    $kilobyte = 1024;
    $megabyte = $kilobyte * 1024;
    $gigabyte = $megabyte * 1024;
    $terabyte = $gigabyte * 1024;
    if(($bytes >= 0) && ($bytes < $kilobyte)){
        return $bytes . ' B';
 
    }elseif(($bytes >= $kilobyte) && ($bytes < $megabyte)){
        return round($bytes / $kilobyte, $precision).' KB';
 
    }elseif(($bytes >= $megabyte) && ($bytes < $gigabyte)){
        return round($bytes / $megabyte, $precision).' MB';
 
    }elseif(($bytes >= $gigabyte) && ($bytes < $terabyte)){
        return round($bytes / $gigabyte, $precision).' GB';
 
    }elseif($bytes >= $terabyte){
        return round($bytes / $terabyte, $precision).' TB';
    }else{
        return $bytes . ' B';
    }
}



function dateToAgo($ptime){
    $etime = time() - $ptime;

    if ($etime < 1)
    {
        return '0 seconds';
    }

    $a = array( 365 * 24 * 60 * 60  =>  'year',
                 30 * 24 * 60 * 60  =>  'month',
                      24 * 60 * 60  =>  'day',
                           60 * 60  =>  'hour',
                                60  =>  'minute',
                                 1  =>  'second'
                );
    $a_plural = array( 'year'   => 'years',
                       'month'  => 'months',
                       'day'    => 'days',
                       'hour'   => 'hours',
                       'minute' => 'minutes',
                       'second' => 'seconds'
                );

    foreach ($a as $secs => $str)
    {
        $d = $etime / $secs;
        if ($d >= 1)
        {
            $r = round($d);
            return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
        }
    }
}

function db_connect($host, $user, $pass, $dbname){
	$db = new mysqli($host, $user, $pass, $dbname);
	if($db->connect_error){
		trigger_error('Database connection failed: '.$db->connect_error, E_USER_ERROR);
	}
	$db->query("SET NAMES 'utf8'");
	return $db;
}

?>