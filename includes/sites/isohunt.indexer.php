<?php

/*
 * Author: Chris Samarinas (https://algoprog.com)
 * Licensed under: GPL v.3
 */

require_once(dirname(dirname(__FILE__)).'/config.php');
require_once(dirname(dirname(__FILE__)).'/htmldom.php');
require_once(dirname(dirname(__FILE__)).'/functions.php');
require_once(dirname(dirname(__FILE__)).'/rivr.php');

class Isohunt{
	
	private $urls;
	private $source_id = 3;
	private $added;
	private $updated;
	
	public function __construct($days){
		for($i=0;$i<3*$days;$i++){
			$p = 40*$i;
			$urls[] = "https://isohunt.to/torrents/?iht=5&Torrent_page=$p&Torrent_sort=-created_at";
		}
		for($i=0;$i<3*$days;$i++){
			$p = 40*$i;
			$urls[] = "https://isohunt.to/torrents/?iht=8&Torrent_page=$p&Torrent_sort=-created_at";
		}
		for($i=0;$i<3*$days;$i++){
			$p = 40*$i;
			$urls[] = "https://isohunt.to/torrents/?iht=3*$days&Torrent_page=$p&Torrent_sort=-created_at";
		}
		for($i=0;$i<3*$days;$i++){
			$p = 40*$i;
			$urls[] = "https://isohunt.to/torrents/?iht=2&Torrent_page=$p&Torrent_sort=-created_at";
		}
		for($i=0;$i<3*$days;$i++){
			$p = 40*$i;
			$urls[] = "https://isohunt.to/torrents/?iht=1&Torrent_page=$p&Torrent_sort=-created_at";
		}
		for($i=0;$i<3*$days;$i++){
			$p = 40*$i;
			$urls[] = "https://isohunt.to/torrents/?iht=6&Torrent_page=$p&Torrent_sort=-created_at";
		}
		for($i=0;$i<3*$days;$i++){
			$p = 40*$i;
			$urls[] = "https://isohunt.to/torrents/?iht=9&Torrent_page=$p&Torrent_sort=-created_at";
		}
		for($i=0;$i<3*$days;$i++){
			$p = 40*$i;
			$urls[] = "https://isohunt.to/torrents/?iht=7&Torrent_page=$p&Torrent_sort=-created_at";
		}
		for($i=0;$i<3*$days;$i++){
			$p = 40*$i;
			$urls[] = "https://isohunt.to/torrents/?iht=4&Torrent_page=$p&Torrent_sort=-created_at";
		}
		$this->urls = $urls;
	}
	
	public function start(){
		
		$t1 = microtime(true);
		
		$db = db_connect(DBHOST, DBUSER, DBPASS, DBNAME);
		$sdb = db_connect('localhost:9306', '', '', 'rtindex');
		
		$total_urls = count($this->urls);
		$cookies = 'movie-old-style=1';
		for($i=0;$i<$total_urls;$i++){
			unset($res);
			file_put_contents('status.txt','Isohunt::...'.$this->urls[$i]);
			$res = gethtml($this->urls[$i],$cookies);
			$this->index($res,$db,$sdb);
		}
		
		$time = microtime(true)-$t1;
		
		$db->query("INSERT INTO crawls SET source_id = '{$this->source_id}', added_torrents = '{$this->added}', updated_torrents = '{$this->updated}', time = '$time';");
		return array($this->added,$this->updated);
	}
	
	private function index($data,$db,$sdb){
		$data = str_get_html($data);
		if(!$data->find('.table-torrents')) return;
		$data = $data->find('.table-torrents',0);
		foreach($data->find('tr') as $result){
			if(!$result->find('td.title-row')) continue;
			
			$title = $result->find('td.title-row',0)->find('a',0)->plaintext;
			
			$url = 'https://isohunt.to'.$result->find('td.title-row',0)->find('a',0)->href;
			
			$size = round(strToBytes($result->find('td.size-row',0)->plaintext)/1024);
			
			$seeds = $result->find('td.sy',0)->plaintext;
			
			$peers = 0;
			
			$hash = '';
			
			$date = strtotime($result->find('td.date-row',0)->plaintext);
			
			$cdata = strtolower($result->find('span.torrent-icon',0)->title);
			if(strstr($cdata,'movies')){
				$type = 'movies';
			}elseif(strstr($cdata,'games')){
				$type = 'games';
			}elseif(strstr($cdata,'music')){
				$type = 'music';
			}elseif(strstr($cdata,'software')){
				$type = 'software';
			}elseif(strstr($cdata,'tv')){
				$type = 'tv';
			}elseif(strstr($cdata,'adult')){
				$type = 'porn';
			}elseif(strstr($cdata,'anime')){
				$type = 'anime';
			}elseif(strstr($cdata,'books')){
				$type = 'books';
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