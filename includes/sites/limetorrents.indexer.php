<?php

/*
 * Author: Chris Samarinas (https://algoprog.com)
 * Licensed under: GPL v.3
 */

require_once(dirname(dirname(__FILE__)).'/config.php');
require_once(dirname(dirname(__FILE__)).'/htmldom.php');
require_once(dirname(dirname(__FILE__)).'/functions.php');
require_once(dirname(dirname(__FILE__)).'/rivr.php');

class Limetorrents{
	
	private $urls;
	private $source_id = 5;
	private $added;
	private $updated;
	
	public function __construct($days){
		for($i=1;$i<=10*$days;$i++){
			$urls[] = "https://www.limetorrents.cc/browse-torrents/Movies/date/$i/";
		}
		for($i=1;$i<=10*$days;$i++){
			$urls[] = "https://www.limetorrents.cc/browse-torrents/TV-shows/date/$i/";
		}
		for($i=1;$i<=10*$days;$i++){
			$urls[] = "https://www.limetorrents.cc/browse-torrents/Music/date/$i/";
		}
		for($i=1;$i<=10*$days;$i++){
			$urls[] = "https://www.limetorrents.cc/browse-torrents/Games/date/$i/";
		}
		for($i=1;$i<=10*$days;$i++){
			$urls[] = "https://www.limetorrents.cc/browse-torrents/Applications/date/$i/";
		}
		for($i=1;$i<=10*$days;$i++){
			$urls[] = "https://www.limetorrents.cc/browse-torrents/Anime/date/$i/";
		}
		for($i=1;$i<=10*$days;$i++){
			$urls[] = "https://www.limetorrents.cc/browse-torrents/Other/date/$i/";
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
			file_put_contents('status.txt','Limetorrents::...'.$this->urls[$i]);
			$res = gethtml($this->urls[$i]);
			
			if(strstr($this->urls[$i],'Movies')){
				$category = 'movies';
			}elseif(strstr($this->urls[$i],'TV')){
				$category = 'tv';
			}elseif(strstr($this->urls[$i],'Music')){
				$category = 'music';
			}elseif(strstr($this->urls[$i],'Games')){
				$category = 'games';
			}elseif(strstr($this->urls[$i],'Applications')){
				$category = 'software';
			}elseif(strstr($this->urls[$i],'Anime')){
				$category = 'anime';
			}else{
				$category = 'other';
			}
			$type = Rivr::getType($category);
			
			$this->index($res,$type,$db,$sdb);
		}
		
		$time = microtime(true)-$t1;
		
		$db->query("INSERT INTO crawls SET source_id = '{$this->source_id}', added_torrents = '{$this->added}', updated_torrents = '{$this->updated}', time = '$time';");
		return array($this->added,$this->updated);
	}
	
	private function index($data,$type,$db,$sdb){
		$data = str_get_html($data);
		
		if(!$data->find('.table2')) return;
		
		$data = $data->find('.table2',0);
		
		if(!$data->find('tr')) continue;

		foreach($data->find('tr') as $result){
			$tt = $result->find('.tt-name');
			if(!$tt) continue;
			
			$title = $result->find('.tt-name',0)->find('a',1)->plaintext;
			
			$url = 'https://www.limetorrents.cc'.$result->find('.tt-name',0)->find('a',1)->href;
			
			$m = $result->find('.tt-name',0)->find('a',0)->href;
			$hash = strtoupper(between($m,'torrent/','.torrent'));
			
			$size = round(strToBytes($result->find('td',2)->plaintext)/1024);
			
			$seeds = $result->find('.tdseed',0)->plaintext;
			
			$peers = $result->find('.tdleech',0)->plaintext;
			
			$date = strtotime('-'.str_replace(' ago','',$result->find('td',1)->plaintext));
			
			$uploader = '';
			
			$files = 0;
			
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