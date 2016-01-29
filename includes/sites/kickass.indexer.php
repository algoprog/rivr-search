<?php

/*
 * Author: Chris Samarinas (https://algoprog.com)
 * Licensed under: GPL v.3
 */

require_once(dirname(dirname(__FILE__)).'/config.php');
require_once(dirname(dirname(__FILE__)).'/htmldom.php');
require_once(dirname(dirname(__FILE__)).'/functions.php');
require_once(dirname(dirname(__FILE__)).'/rivr.php');

class Kickass{
	
	private $urls;
	private $source_id = 1;
	private $added;
	private $updated;
	
	public function __construct($days){
		for($i=0;$i<=20*$days;$i++){
			$urls[] = "https://kat.cr/movies/$i/?field=time_add&sorder=desc";
		}
		for($i=0;$i<=20*$days;$i++){
			$urls[] = "https://kat.cr/tv/$i/?field=time_add&sorder=desc";
		}
		for($i=0;$i<=20*$days;$i++){
			$urls[] = "https://kat.cr/music/$i/?field=time_add&sorder=desc";
		}
		for($i=0;$i<=20*$days;$i++){
			$urls[] = "https://kat.cr/games/$i/?field=time_add&sorder=desc";
		}
		for($i=0;$i<=20*$days;$i++){
			$urls[] = "https://kat.cr/applications/$i/?field=time_add&sorder=desc";
		}
		for($i=0;$i<=20*$days;$i++){
			$urls[] = "https://kat.cr/anime/$i/?field=time_add&sorder=desc";
		}
		for($i=0;$i<=20*$days;$i++){
			$urls[] = "https://kat.cr/books/$i/?field=time_add&sorder=desc";
		}
		for($i=0;$i<=20*$days;$i++){
			$urls[] = "https://kat.cr/lossless/$i/?field=time_add&sorder=desc";
		}
		for($i=0;$i<=20*$days;$i++){
			$urls[] = "https://kat.cr/xxx/$i/?field=time_add&sorder=desc";
		}
		for($i=0;$i<=20*$days;$i++){
			$urls[] = "https://kat.cr/other/$i/?field=time_add&sorder=desc";
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
			$res = gethtml($this->urls[$i]);
			$this->index($res,$db,$sdb);
		}
		
		$time = microtime(true)-$t1;
		
		$db->query("INSERT INTO crawls SET source_id = '{$this->source_id}', added_torrents = '{$this->added}', updated_torrents = '{$this->updated}', time = '$time';");
		return array($this->added,$this->updated);
	}
	
	private function index($data,$db,$sdb){
		$data = str_get_html($data);
		foreach($data->find('.even, .odd') as $result){
			$title = $result->find('.cellMainLink',0)->plaintext;
			
			$url = 'https://kat.cr'.$result->find('.cellMainLink',0)->href;
			
			$m = $result->find('div.iaconbox',0);
			$hash = strtoupper(between($m,'magnet:?xt=urn:btih:','&dn='));
			
			$size = round(strToBytes($result->find('td.nobr',0)->plaintext)/1024);
			
			$seeds = $result->find('td.green',0)->plaintext;
			
			$peers = $result->find('td.red',0)->plaintext;
			
			$date = strtotime($result->find('td',3)->title);
			
			$uploader = $result->find('a.plain',1)->plaintext;
			
			$files = $result->find('td',2)->plaintext;
			
			if(!$result->find('span.block')) continue;
			
			$cdata = $result->find('span.block',0)->find('strong',0)->plaintext;
			if(strstr($cdata,'Movies')){
				$type = 'movies';
			}elseif(strstr($cdata,'Games')){
				$type = 'games';
			}elseif(strstr($cdata,'Music')){
				$type = 'music';
			}elseif(strstr($cdata,'Applications')){
				$type = 'software';
			}elseif(strstr($cdata,'TV')){
				$type = 'tv';
			}elseif(strstr($cdata,'XXX')){
				$type = 'porn';
			}elseif(strstr($cdata,'Anime')){
				$type = 'anime';
			}elseif(strstr($cdata,'Books')){
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