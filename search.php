<?php

/*
 * Author: Chris Samarinas (https://algoprog.com)
 * Licensed under: GPL v.3
 */

require_once('includes/config.php');
require_once('includes/rivr.php');
require_once('includes/functions.php');

$q = trim(htmlspecialchars(urldecode($_GET['q'])));
$page = $_GET['p'];
$offset = $page*PERPAGE;

$query = $sdb->query("SELECT *, WEIGHT() AS weight FROM rtindex0,rtindex1,rtindex2,rtindex3 WHERE MATCH('$q') OPTION ranker=proximity_bm25, max_matches=1;");

$query->data_seek(0);
$qdata = $query->fetch_assoc();
$maxWeight = 1;
if($query->num_rows){
    $maxWeight = $qdata['weight'];
}


$query = $sdb->query("SELECT *, (WEIGHT()/$maxWeight+1)*(seeds+0.2*peers) AS score FROM rtindex0,rtindex1,rtindex2,rtindex3 WHERE MATCH('$q') ORDER BY score DESC LIMIT $offset, ".PERPAGE." OPTION ranker=proximity_bm25, max_matches=500;");

$mquery = $sdb->query("SHOW META;");

$mquery->data_seek(0);
$info = $mquery->fetch_assoc();
$rcount = $info['Value'];

$mquery->data_seek(1);
$info = $mquery->fetch_assoc();
$total = $info['Value'];

$mquery->data_seek(2);
$info = $mquery->fetch_assoc();
$time = round($info['Value'],4);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="images/favicon.png">
<title><?php echo $q .' ~ '. ENGINE_NAME; ?></title>
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

<div id="results">

<?php
if(!$total){
	echo "<div class=\"clearfix\" style=\"height:200px;padding-top:40px;\"><img src=\"images/angry.png\"/ style=\"float:left;margin-right:20px;\"><br/>No results found for <b>$q</b><br/><br/>Tips:<br/><ul><li>Check the spelling of your keywords</li><li>Try differrent keywords</li><li>Try more general keywords</li></ul></div>";
}else{
	while($data = $query->fetch_assoc()){
		$count[$data['category']] = $count[$data['category']]+1;
		
		$tquery = $db->query("SELECT title FROM rtindex WHERE id = '{$data['id']}';");
		$tdata = $tquery->fetch_assoc();
		$data['title'] = $tdata['title'];
		
		$quality = Rivr::getQuality($data['title']);
		$data['quality'] = $quality;
		$hasQuality[$quality] = 1;
		
		$cdata[] = $data;
	}
	arsort($count);
	$k = array_keys($count);
	$type = $k[0];
	if($type==8 && count($k)>1) $type = $k[1];
	
	$date = time();
	
	$qq = $db->query("SELECT id FROM queries WHERE query LIKE '$q%';");
	if($qq->num_rows){
		$d = $qq->fetch_assoc();
		$id = $d['id'];
		$db->query("UPDATE queries SET count = count + 1, type = $type, last_date = $date WHERE id = $id;");
	}else{
		$eq = mysql_real_escape_string($q);
		$db->query("INSERT INTO queries SET query = '$eq', count = 1, type = $type, first_date = $date, last_date = $date;");
	}
	
	if($type==0||$type==1){
		
		echo "<div id=\"xinfo\" class=\"clearfix\"><p align=\"center\"><img src=\"images/xload.gif\"/></p></div><script>".'$("#xinfo").load("xinfo?type='.$type.'&q='.urlencode($q).'");'."</script>";
		
		$qtypes = array('1080p','720p','BRRIP','HDRIP','HDTV','DVDSCR','HDTS','CAM','3D');
		$tags = "<span class=\"tag tag_selected\">ALL</span>";
		foreach($qtypes as $qtype){
			if($hasQuality[$qtype]){
				$tags .= "<span class=\"tag\">$qtype</span>";
			}
		}
		echo '<div id="tags">'.$tags.'</div>'.$data;
	}
	
	echo "<table class=\"rtable\">";
	
	echo "<tr class=\"top_row\"><td colspan=\"4\">&nbsp;<b>$total</b> results for <b>$q</b> in $time seconds</td></tr>";

	foreach($cdata as $data){
		if($data['category']!=8){
			$rtype = "<img src=\"images/".Rivr::getTypeInt($data['category']).".png\" class=\"icon\"/>";
		}else{
			$rtype = '';
		}
		echo "<tr class=\"row {$data['quality']}\"><td style=\"width:700px;\">$rtype<a href=\"{$data['hash']}\">{$data['title']}</a></td><td width=\"70px\" class=\"seeds\">{$data['seeds']}</td><td width=\"70px\" class=\"leech\">{$data['peers']}</td><td width=\"90px\">".bytesToStr($data['size']*1024)."</td></tr>";
	}

	echo "</table><br/>";

	$pages = ceil($rcount/PERPAGE);

	echo "<p align=\"center\">";
	
	for($i=0;$i<$pages;$i++){
		if($i==$page){
			echo "<a class=\"ptag tag_selected\" href='search.php?q=$q&p=$i'>".($i+1)."</a> ";
		}else{
			echo "<a class=\"ptag\" href='search.php?q=$q&p=$i'>".($i+1)."</a> ";
		}
	}
	
	echo "</p>";
}
?>
<br/><br/>
</div>

<div id="ad">
</div>

<?php include("includes/footer.php"); ?>

</div>
</body>

</html>