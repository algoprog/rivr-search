<?php

require_once('includes/config.php');
require_once('includes/rivr.php');
require_once('includes/functions.php');

if($_GET['dl']){
	$hash = $_GET['hash'];
}else{
	$hash = explode('/',$_SERVER['REQUEST_URI']);
	$hash = $hash[count($hash)-1];	
}

$hash = strtoupper(trim(htmlspecialchars(urldecode($hash))));

$query = $sdb->query("SELECT id, source_id, date, category FROM rtindex0,rtindex1,rtindex2,rtindex3 WHERE hash = '$hash';");
if(!$query->num_rows){
	header("Location: error");
	die();
}
while($data = $query->fetch_assoc()){
	$tquery = $db->query("SELECT title, url FROM rtindex WHERE id = '{$data['id']}';");
	$tdata = $tquery->fetch_assoc();
	$data['title'] = $tdata['title'];
	$data['url'] = $tdata['url'];
	
	$cdata[] = $data;
}
$count = count($cdata);
$title = $cdata[0]['title'];

if($_GET['dl']){
	$tt = '[rivr.eu]'.str_replace(' ','.',$title);
	$data = Rivr::getTorrent($hash);
	header("Content-type: application/x-download");
    header("Content-Length: ".strlen($data));
    header("Content-Disposition: attachment; filename=$tt.torrent");
    header("Content-Transfer-Encoding: binary");
	echo $data;
	die();
}

$query = $db->query("SELECT id, url FROM sources;");
if($query->num_rows){
	while($data = $query->fetch_assoc()){
		$sources[$data['id']] = $data['url'];
	}
}

$torrent = Rivr::getTorrentInfo($hash);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="images/favicon.png">
<title><?php echo $title .' ~ '. ENGINE_NAME; ?></title>
<link rel="stylesheet" type="text/css" href="css/main.css"/>
<link rel="stylesheet" type="text/css" href="css/jquery-ui.css"/>
<link rel="stylesheet" type="text/css" href="css/popup.css"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="js/search.js"></script>
<script src="js/popup.js"></script>
</head>

<body>

<img src="images/loading.png" id="loading"/>

<div id="container">

<div id="top">
<form action="search" method="GET" id="search">
<a href="index"><img src="images/logo_sm.png" id="logo_top"/></a> <input type="search" name="q" id="q" class="qs" value="<?php echo $q; ?>" required/><span id="sbt"></span>
</form>
</div>

<div id="ad_top">
<script type="text/javascript">
var ad_idzone = "1751578",
	 ad_width = "728",
	 ad_height = "90";
</script>
<script type="text/javascript" src="https://ads.exoclick.com/ads.js"></script>
<noscript><a href="http://main.exoclick.com/img-click.php?idzone=1751578" target="_blank"><img src="https://syndication.exoclick.com/ads-iframe-display.php?idzone=1751578&output=img&type=728x90" width="728" height="90"></a></noscript>
</div>

<script>
function cdir(nfid){
	$('.'+nfid).slideToggle();
}
</script>

<div id="results">

<p align="center">
<?php

$magnet = "magnet:?xt=urn:btih:$hash&dn=".urlencode($title).'&tr='.implode('&tr=',array('udp://tracker.openbittorrent.com:80/announce','udp://tracker.publicbt.com:80/announce','udp://open.demonii.com:1337/announce','udp://tracker.leechers-paradise.org:6969/announce','udp://coppersurfer.tk:6969/announce'));

if($torrent){
	echo "<a href=\"torrent?hash=$hash&dl=1\" class=\"ptag\">Download torrent</a> ";
	
	foreach($torrent['announce-list'] as $tracker){
		$trackers[] = $tracker[0];
	}
	$magnet = "magnet:?xt=urn:btih:$hash&dn=".urlencode($title).'&tr='.implode('&tr=',$trackers);
}

echo "<a href=\"$magnet\" class=\"ptag\">Magnet</a>";

?>
</p>

<?php

echo "<table class=\"rtable\">";

echo "<tr><td class=\"top_row\" width=\"800px\">$title ~ $count sources</td></tr>";

foreach($cdata as $data){
	echo "<tr class=\"row\"><td><img src=\"https://plus.google.com/_/favicon?domain={$sources[$data['source_id']]}\" class=\"icon\"> ".$sources[$data['source_id']];
	if($data['url']){
		echo  " &middot; <a href=\"{$data['url']}\" target=\"blank\">{$data['title']}</a>";
	}
	echo " &middot; ".Rivr::getTypeInt($data['category'])."</td></tr>";
}

echo "</table><br/>";


if($torrent){

	echo "<table class=\"table\"><tr><td width=\"405px\" align=\"left\" valign=\"top\">";

	//trackers info

	echo "<td width=\"405px\" align=\"right\" valign=\"top\"><table class=\"rtable_sm\">";

	echo "<tr><td class=\"top_row\" width=\"390px\">Trackers</td></tr>";

	foreach($trackers as $tracker){
		echo "<tr class=\"row small\"><td>$tracker</td></tr>";
	}

	echo "</table></td></tr></table><br/>";



	//torrent contents

	function &getArrayPath($path, &$array){
		$path = explode('/',$path);
		$folder = array_shift($path);
		$path = implode('/',$path);
		if($folder=='') return $array;
		return getArrayPath($path, $array[$folder]);
	}

	foreach($torrent['info']['files'] as $file){
		$lfile = $file['path'][count($file['path'])-1];
		unset($file['path'][count($file['path'])-1]);
		$size = bytesToStr($file['length']);
		$file['path'] = array_reverse($file['path']);
	
		$path = &getArrayPath(implode('/',$file['path']), $paths);
		$path[] = array($lfile,$size);
		$total_size += $file['length'];
	}
	if(!$paths){
		$paths[] = array($torrent['info']['name'],bytesToStr($torrent['info']['length']));
		$total_size = $torrent['info']['length'];
	}

	function printFiles($files, $level, $fid){
		foreach($files as $folder => $file){
			echo "<div class=\"$fid $cf";
			if($fid) echo ' hidden';
			echo "\">";
			if(!is_numeric($folder)){
				$nfid = md5($folder.$level);
				echo "<div class=\"row\" onclick=\"cdir('$nfid')\">";
				echo str_repeat('&nbsp;&nbsp;&nbsp;',$level);
				echo "<img src=\"images/ext/folder.png\" class=\"icon\"> $folder</div>";
				printFiles($file, $level+1, $nfid);
			}else{
				$ext = explode('.',$file[0]);
				$ext = $ext[count($ext)-1];
				echo "<div class=\"row\">";
				echo str_repeat('&nbsp;&nbsp;&nbsp;',$level);
				echo "<img src=\"images/ext/file_extension_$ext.png\" class=\"icon\"> {$file[0]} - {$file[1]}</div>";
			}
			echo "</div>";
		}
	}


	echo "<table class=\"rtable\">";

	echo "<tr><td class=\"top_row\" width=\"800px\">Torrent contents ~ ".bytesToStr($total_size)."</td></tr>";

	echo "<tr><td>";
	
	printFiles($paths, 0, '', '');

	echo "</td></tr></table>";	
}

?>
</div>

<?php include("includes/footer.php"); ?>

</div>
</body>

</html>