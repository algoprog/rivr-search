<?php

/*
 * Author: Chris Samarinas (https://algoprog.com)
 * Licensed under: GPL v.3
 */

require_once(dirname(__FILE__).'/htmldom.php');
require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/torrent_decoder.php');

class Rivr{
	
	private $data;
	private $results;
	private $total;
	private $types;
	private $type;
	private $qualities;
	private $imdb_id;
	
	public function __construct($q){
		if(!$q) return;
		return $this->doSearch($q);
	}
	
	public function getResultsType(){
		return $this->type;
	}
	
	public function getResults(){
		return $this->results;
	}
	
	public function setResults($str){
		$this->results = $str;
	}
	
	public function getTotal(){
		return $this->total;
	}
	
	public function getTypes(){
		return $this->types;
	}
	
	public function getQualities(){
		return $this->qualities;
	}
	
	public function saveCache($path){
		if(file_exists($path)){
			unlink($path);
		}
		file_put_contents($path,json_encode($this->data));
	}
	
	public function loadCache($path){
		if(file_exists($path)){
			$this->data = json_decode(file_get_contents($path),true);
		}
	}
	
	public static function getType($str){
		switch($str){
			case 'movies':
				return 0;
			case 'tv':
				return 1;
			case 'software':
				return 2;
			case 'games':
				return 3;
			case 'music':
				return 4;
			case 'porn':
				return 5;
			case 'anime':
				return 6;
			case 'books':
				return 7;
			default:
				return 8;
		}
	}
	
	public static function getTypeInt($int){
		switch($int){
			case 0:
				return 'movies';
			case 1:
				return 'tv';
			case 2:
				return 'software';
			case 3:
				return 'games';
			case 4:
				return 'music';
			case 5:
				return 'porn';
			case 6:
				return 'anime';
			case 7:
				return 'books';
			default:
				return 'other';
		}
	}
	
	public static function getQuality($str){
		$str = mb_strtolower(clean_text($str));
		if(strstr($str,'720p')){
			return '720p';
		}elseif(strstr($str,'1080p')){
			return '1080p';
		}elseif(strstr($str,'3D')){
			return '3D';
		}elseif(strstr($str,'hdrip')){
			return 'HDRIP';
		}elseif(strstr($str,'dvdrip')){
			return 'DVDRIP';
		}elseif(strstr($str,'brrip')){
			return 'BRRIP';
		}elseif(strstr($str,'hd ts')||strstr($str,'hdts')){
			return 'HDTS';
		}elseif(strstr($str,'dvdscr')){
			return 'DVDSCR';
		}elseif(strstr($str,'cam')){
			return 'CAM';
		}elseif(strstr($str,'hdtv')){
			return 'HDTV';
		}
		return '';
	}
	
	public function getImdbId(){
		return $this->imdb_id;
	}
	
	public static function findImdbId($q){
		$title = clean_text(str_replace(' ','_',$q));
		$imdb = gethtml("http://sg.media-imdb.com/suggests/{$q[0]}/$title.json");

		while(strstr($imdb,'<Error>') && strlen($title)>3){
			$title = substr($title, 0, -1);
			$imdb = gethtml("http://sg.media-imdb.com/suggests/{$q[0]}/$title.json");
		}
	
		if(!strstr($imdb,'<code>AccessDenied</code>')){
			$imdb = str_replace('imdb$'.$title,'imdb',$imdb);
			$imdb = str_replace('imdb({','{',$imdb);
			$imdb = substr($imdb, 0, -1);
			$imdb = json_decode($imdb,true);
			$imdb = $imdb['d'][0];
		
			if($imdb['q']=='feature'||$imdb['q']=='TV series'){
				$imdb_id = str_replace('tt','',$imdb['id']);
			}
		}
		if($imdb_id==0){
			$imdb_id = -1;
		}
		return $imdb_id;
	}
	
	public static function isParent($sdb, $id, $hash, $seeds, $peers){
		if($hash){
			$sp = $seeds+$peers;
			if($id){
				$query = $sdb->query("SELECT COUNT(*) AS better, seeds+peers AS score FROM rtindex0,rtindex1,rtindex2,rtindex3 WHERE hash = '$hash' AND id != $id AND score > $sp");
			}else{
				$query = $sdb->query("SELECT COUNT(*) AS better, seeds+peers AS score FROM rtindex0,rtindex1,rtindex2,rtindex3 WHERE hash = '$hash' AND score > $sp");
			}
			$info = $query->fetch_assoc();
			if(!$info['better']){
				return 1;
			}
		}
	}
	
	public static function addTorrent($db, $sdb, $title, $hash, $source_id, $seeds, $peers, $date, $url, $indexed, $category, $size, $files, $uploader){		
		
		$parent = Rivr::isParent($sdb, 0, $hash, $seeds, $peers);
		
		$title = mysql_real_escape_string($title);
		$uploader = mysql_real_escape_string($uploader);
		$seeds = str_replace('.','',$seeds);
		$peers = str_replace(',','',$peers);
		$seeds = intval($seeds);
		$peers = intval($peers);
		$date = intval($date);
		
		//die("INSERT INTO rtindex SET title = '$title', hash = '$hash', source_id = '$source_id', seeds = '$seeds', peers = '$peers', date = '$date', url = '$url', indexed = '$indexed', category = '$category', size = '$size', files = '$files', uploader = '$uploader'<br/>");
		
		$q = $db->query("INSERT INTO rtindex SET title = '$title', hash = '$hash', source_id = '$source_id', seeds = '$seeds', peers = '$peers', date = '$date', url = '$url', indexed = '$indexed', category = '$category', size = '$size', files = '$files', uploader = '$uploader';");
		if(!$q) die("INSERT INTO rtindex SET title = '$title', hash = '$hash', source_id = '$source_id', seeds = '$seeds', peers = '$peers', date = '$date', url = '$url', indexed = '$indexed', category = '$category', size = '$size', files = '$files', uploader = '$uploader';");
		
		$id = $db->insert_id;
		$bin = $id%4;
		
		$q = $sdb->query("INSERT INTO rtindex".$bin." VALUES($id, '$title', '$hash', $source_id, $seeds, $peers, $date, $category, $size)");
		if(!$q) die("INSERT INTO rtindex".$bin." VALUES($id, '$title', '$hash', $source_id, $seeds, $peers, $date, $category, $size)");
		
		if($parent){
			$q = $db->query("UPDATE rtindex SET parent = 0 WHERE hash = '$hash'");
			if(!$q) die("UPDATE rtindex SET parent = 0 WHERE hash = '$hash'");
			$q = $sdb->query("DELETE FROM rtindex".$bin." WHERE hash = '$hash' AND id != $id");
			if(!$q) die("DELETE FROM rtindex".$bin." WHERE hash = '$hash' AND id != $id");
		}
	}
	
	public static function updateTorrent($db, $sdb, $id, $title, $hash, $source_id, $seeds, $peers, $date, $url, $indexed, $category, $size, $files, $uploader){
		
		$parent = Rivr::isParent($sdb, $id, $hash, $seeds, $peers);
		
		$bin = $id%4;
		
		$title = mysql_real_escape_string($title);
		$uploader = mysql_real_escape_string($uploader);
		$seeds = str_replace('.','',$seeds);
		$peers = str_replace(',','',$peers);
		$seeds = intval($seeds);
		$peers = intval($peers);
		$date = intval($date);
		
		if($parent){
			$q = $db->query("UPDATE rtindex SET parent = 0 WHERE hash = '$hash'");
			if(!$q) die("UPDATE rtindex SET parent = 0 WHERE hash = '$hash'");
			
			$q = $sdb->query("DELETE FROM rtindex".$bin." WHERE hash = '$hash' AND id != $id");
			if(!$q) die("DELETE FROM rtindex".$bin." WHERE hash = '$hash' AND id != $id");
		}
		
		$q = $db->query("UPDATE rtindex SET title = '$title', hash = '$hash', source_id = '$source_id', seeds = '$seeds', peers = '$peers', date = '$date', url = '$url', indexed = '$indexed', category = '$category', size = '$size', files = '$files', uploader = '$uploader' WHERE id = '$id';");
		if(!$q) die("UPDATE rtindex SET title = '$title', hash = '$hash', source_id = '$source_id', seeds = '$seeds', peers = '$peers', date = '$date', url = '$url', indexed = '$indexed', category = '$category', size = '$size', files = '$files', uploader = '$uploader' WHERE id = '$id';");
		
		$q = $sdb->query("REPLACE INTO rtindex".$bin." VALUES($id, '$title', '$hash', $source_id, $seeds, $peers, $date, $category, $size)");
		if(!$q) die("REPLACE INTO rtindex".$bin." VALUES($id, '$title', '$hash', $source_id, $seeds, $peers, $date, $category, $size)");
	}
	
	public static function getTorrentInfo($hash){
		$url = "http://torcache.net/torrent/$hash.torrent";
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_REFERER, 'https://torcache.net/');
		curl_setopt($ch,CURLOPT_ENCODING,"gzip");
		$data = curl_exec($ch);
		curl_close($ch);
		try{
			$decoder = new torrent_decoder($data);
			$torrent =  $decoder->decode();
		}catch(Exception $e){
			return null;
		}
		return $torrent;
	}
	
	public static function getTorrent($hash){
		$url = "http://torcache.net/torrent/$hash.torrent";
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_REFERER, 'https://torcache.net/');
		curl_setopt($ch,CURLOPT_ENCODING,"gzip");
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
}

?>
