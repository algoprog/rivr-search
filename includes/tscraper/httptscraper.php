<?php
	/* 	Torrent HTTP Scraper
		v1.0
		
		2010 by Johannes Zinnau
		johannes@johnimedia.de
		
		Licensed under a Creative Commons Attribution-ShareAlike 3.0 Unported License
		http://creativecommons.org/licenses/by-sa/3.0/
		
		It would be very nice if you send me your changes on this class, so that i can include them if they are improve it.
		Thanks!
		
		Usage:
		try{
			$timeout = 2;
			//Read only 4MiB of the scrape response
			$maxread = 1024 * 4;
			
			$scraper = new httptscraper($timeout,$maxread);
			$ret = $scraper->scrape('http://tracker.tld:port/announce',array('0000000000000000000000000000000000000000'));
			
			print_r($ret);
		}catch(ScraperException $e){
			echo('Error: ' . $e->getMessage() . "<br />\n");
			echo('Connection error: ' . ($e->isConnectionError() ? 'yes' : 'no') . "<br />\n");
		}
	*/
	
	require_once(dirname(__FILE__) . '/tscraper.php');
	require_once(dirname(__FILE__) . '/lightbenc.php');
	
	class httptscraper extends tscraper{
		protected $maxreadsize;
		
		public function __construct($timeout=2,$maxreadsize=4096){
			$this->maxreadsize = $maxreadsize;
			parent::__construct($timeout);
		}
		
		/* 	$url: Tracker url like: http://tracker.tld:port/announce or http://tracker.tld:port/scrape
			$infohash: Infohash string or array. 40 char long infohash. 
			*/
		public function scrape($url,$infohash){
			if(!is_array($infohash)){ $infohash = array($infohash); }
			foreach($infohash as $hash){
				if(!preg_match('#^[a-f0-9]{40}$#i',$hash)){ throw new ScraperException('Invalid infohash: ' . $hash . '.'); }
			}
			$url = trim($url);		
			if (preg_match('%(http://.*?/)announce([^/]*)$%i', $url, $m)){ 
				$url = $m[1] . 'scrape' . $m[2];
			}else if (preg_match('%(http://.*?/)scrape([^/]*)$%i', $url, $m)){ 
			}else{
				throw new ScraperException('Invalid tracker url.');
			}
			
			$sep = preg_match ('/\?.{1,}?/i', $url) ? '&' : '?';
			$requesturl = $url;
			foreach($infohash as $hash){
				$requesturl .= $sep . 'info_hash=' . rawurlencode(pack('H*', $hash));
				$sep = '&';
			}
			
			ini_set('default_socket_timeout',$this->timeout);
			$rh = @fopen($requesturl,'r');
			if(!$rh){ throw new ScraperException('Could not open HTTP connection.',0,true); }
			stream_set_timeout($rh, $this->timeout);
			
			$return = '';
			$pos = 0;
			while (!feof($rh) && $pos < $this->maxreadsize){
				$return .= fread($rh,1024);
			}
			fclose($rh);
			
			if(!substr($return, 0, 1) == 'd'){ throw new ScraperException('Invalid scrape response.'); }
			$arr_scrape_data = lightbenc::bdecode($return);
			
			$torrents = array();
			foreach($infohash as $hash){
				$ehash = pack('H*', $hash);
				if (isset($arr_scrape_data['files'][$ehash])){
					$torrents[$hash] = array(	'infohash'=>$hash,
												'seeders'=>(int) $arr_scrape_data['files'][$ehash]['complete'],
												'completed'=>(int) $arr_scrape_data['files'][$ehash]['downloaded'],
												'leechers'=>(int) $arr_scrape_data['files'][$ehash]['incomplete']
												);
				}else{
					$torrents[$hash] = false;
				}
			}
			
			return($torrents);
		}
	}
?>