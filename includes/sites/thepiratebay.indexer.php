<?php

/*
 * Author: Chris Samarinas (https://algoprog.com)
 * Licensed under: GPL v.3
 */

require_once(dirname(dirname(__FILE__)).'/config.php');
require_once(dirname(dirname(__FILE__)).'/htmldom.php');
require_once(dirname(dirname(__FILE__)).'/functions.php');
require_once(dirname(dirname(__FILE__)).'/rivr.php');

class Thepiratebay{
	
	private $urls;
	private $source_id = 2;
	private $added;
	private $updated;
	
	public function __construct($days){
		for($i=0;$i<5*$days;$i++){
			$urls[] = "https://thepiratebay.se/browse/201/$i/3";
		}
		for($i=0;$i<5*$days;$i++){
			$urls[] = "https://thepiratebay.se/browse/207/$i/3";
		}
		for($i=0;$i<5*$days;$i++){
			$urls[] = "https://thepiratebay.se/browse/205/$i/3";
		}
		for($i=0;$i<5*$days;$i++){
			$urls[] = "https://thepiratebay.se/browse/208/$i/3";
		}
		for($i=0;$i<5*$days;$i++){
			$urls[] = "https://thepiratebay.se/browse/301/$i/3";
		}
		for($i=0;$i<5*$days;$i++){
			$urls[] = "https://thepiratebay.se/browse/302/$i/3";
		}
		for($i=0;$i<5*$days;$i++){
			$urls[] = "https://thepiratebay.se/browse/306/$i/3";
		}
		for($i=0;$i<5*$days;$i++){
			$urls[] = "https://thepiratebay.se/browse/401/$i/3";
		}
		for($i=0;$i<5*$days;$i++){
			$urls[] = "https://thepiratebay.se/browse/500/$i/3";
		}
		for($i=0;$i<5*$days;$i++){
			$urls[] = "https://thepiratebay.se/browse/600/$i/3";
		}
		$this->urls = $urls;
	}
	
	public function start(){
		
		$t1 = microtime(true);
		
		$db = db_connect(DBHOST, DBUSER, DBPASS, DBNAME);
		$sdb = db_connect('localhost:9306', '', '', 'rtindex');
		
		$total_urls = count($this->urls);
		for($i=0;$i<$total_urls;$i++){
			unset($res);
			file_put_contents('status.txt','Kickass::...'.$this->urls[$i]);
			$res = file_get_contents($this->urls[$i]);
			$this->index($res,$db,$sdb);
		}
		
		$time = microtime(true)-$t1;
		
		$db->query("INSERT INTO crawls SET source_id = '{$this->source_id}', added_torrents = '{$this->added}', updated_torrents = '{$this->updated}', time = '$time';");
		return array($this->added,$this->updated);
	}
	
	private function index($data,$db,$sdb){
		$data = str_get_html($data);
		if(!$data->find('#searchResult')) return;
		$data = $data->find('#searchResult',0);
		foreach($data->find('tr') as $result){
			if(!$result->find('.detLink')) continue;
			
			$title = $result->find('.detLink',0)->plaintext;
			
			$url = explode('/','https://thepiratebay.se'.$result->find('.detLink',0)->href);
			array_pop($url);
			$url = implode('/', $url);
			
			$magnet = $result->find('td',1);
			$magnet = $magnet->find('a',1)->href;
			$hash = strtoupper(between($magnet,'magnet:?xt=urn:btih:','&dn='));
			
			$size = str_replace('&nbsp;',' ',$result->find('.detDesc',0)->plaintext);
			$words = mb_split('iB',$size);
			$words2 = mb_split('\s',$words[0]);
			$length = count($words2);
			$size = $words2[$length-2].' '.$words2[$length-1].'B';
			$size = round(strToBytes($size)/1024);
			
			$seeds = $result->find('td',2)->plaintext;
			
			$peers = $result->find('td',3)->plaintext;
			
			$date = str_replace('&nbsp;',' ',$result->find('.detDesc',0)->plaintext);
			$date = str_replace('-','/',between($date,'Uploaded ',', Size'));
			$date = strtotime($date);
			
			$uploader = $result->find('a.detDesc');
			if($uploader){
				$uploader = $uploader[0]->plaintext;
			}
			
			$cdata = $result->find('a',1)->href;
			$cdata2 = $result->find('a',0)->href;
			if(strstr($cdata,'browse/201')||strstr($cdata,'browse/207')){
				$type = 'movies';
			}elseif(strstr($cdata2,'browse/400')){
				$type = 'games';
			}elseif(strstr($cdata,'browse/101')){
				$type = 'music';
			}elseif(strstr($cdata2,'browse/300')){
				$type = 'software';
			}elseif(strstr($cdata,'browse/205')||strstr($cdata,'browse/208')){
				$type = 'tv';
			}elseif(strstr($cdata2,'browse/500')){
				$type = 'porn';
			}else{
				$type = 'other';
			}
			$type = Rivr::getType($type);
			
			$cdate = time();
			
			$query = $db->query("SELECT id FROM rtindex WHERE url = '$url' LIMIT 1;");
			if($query->num_rows){
				$this->updated++;
				$info = $query->fetch_assoc();
				Rivr::updateTorrent($db, $sdb, $info['id'], $title, $hash, $this->source_id, $seeds, $peers, $date, $url, $cdate, $type, $size, $files, $uploader);
			}else{
				$this->added++;
				Rivr::addTorrent($db, $sdb, $title, $hash, $this->source_id, $seeds, $peers, $date, $url, $cdate, $type, $size, $files, $uploader);
			}		
		}
		$data->clear(); 
		unset($data);
	}
	
}

?>