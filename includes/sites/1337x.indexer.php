<?php

/*
 * Author: Chris Samarinas (https://algoprog.com)
 * Licensed under: GPL v.3
 */

require_once(dirname(dirname(__FILE__)).'/config.php');
require_once(dirname(dirname(__FILE__)).'/htmldom.php');
require_once(dirname(dirname(__FILE__)).'/functions.php');
require_once(dirname(dirname(__FILE__)).'/rivr.php');

class _1337x{
	
	private $urls;
	private $source_id = 4;
	private $added;
	private $updated;
	
	public function __construct(){
		for($i=0;$i<300;$i++){
			$urls[] = "https://1337x.to/cat/Movies/$i/";
		}
		for($i=0;$i<300;$i++){
			$urls[] = "https://1337x.to/cat/TV/$i/";
		}
		for($i=0;$i<300;$i++){
			$urls[] = "https://1337x.to/cat/Apps/$i/";
		}
		for($i=0;$i<300;$i++){
			$urls[] = "https://1337x.to/cat/Games/$i/";
		}
		for($i=0;$i<300;$i++){
			$urls[] = "https://1337x.to/cat/XXX/$i/";
		}
		for($i=0;$i<300;$i++){
			$urls[] = "https://1337x.to/cat/Music/$i/";
		}
		for($i=0;$i<300;$i++){
			$urls[] = "https://1337x.to/cat/Other/$i/";
		}
		for($i=0;$i<300;$i++){
			$urls[] = "https://1337x.to/cat/Anime/$i/";
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
			file_put_contents('status.txt','1337x::...'.$this->urls[$i]);
			$res = gethtml($this->urls[$i]);
			$this->index($res,$db,$sdb);
		}
		
		$time = microtime(true)-$t1;
		
		$db->query("INSERT INTO crawls SET source_id = '{$this->source_id}', added_torrents = '{$this->added}', updated_torrents = '{$this->updated}', time = '$time';");
		return array($this->added,$this->updated);
	}
	
	private function index($data,$db,$sdb){
		$data = str_get_html($data);
		if(!$data->find('div.tab-detail')) return;
		$data = $data->find('div.tab-detail',0);
		foreach($data->find('li') as $result){
			if(!$result->find('a')) continue;
			
			$title = $result->find('a',1)->plaintext;
			
			$url = 'https://1337x.to'.$result->find('a',1)->href;
			
			$size = round(strToBytes($result->find('div.coll-4',0)->plaintext)/1024);
			
			$seeds = $result->find('span.green',0)->plaintext;
			
			$peers = $result->find('span.red',0)->plaintext;
			
			$hash = '';
			
			$uploader = $result->find('.uploader, .vip',0)->plaintext;
			
			$cdata = $result->find('.coll-1',0)->find('i',0)->class;
			if(strstr($cdata,'Movies')){
				$type = 'movies';
			}elseif(strstr($cdata,'Games')){
				$type = 'games';
			}elseif(strstr($cdata,'Music')){
				$type = 'music';
			}elseif(strstr($cdata,'Apps')){
				$type = 'software';
			}elseif(strstr($cdata,'TV')){
				$type = 'tv';
			}elseif(strstr($cdata,'XXX')){
				$type = 'porn';
			}elseif(strstr($cdata,'Anime')){
				$type = 'anime';
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