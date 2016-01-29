<?php

require_once('includes/config.php');
require_once('includes/rivr.php');
require_once('includes/functions.php');

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="images/favicon.png">
<title><?php echo 'Browse ~ '. ENGINE_NAME; ?></title>
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
</div>

<script>
function cdir(nfid){
	$('.'+nfid).slideToggle();
}
</script>

<div id="results">

<?php

$date = time();

// TOP

echo "<table class=\"rtable\">";
echo "<tr class=\"top_row\"><td colspan=\"4\">Top</td></tr>";

	$query = $sdb->query("SELECT *, (seeds+0.2*peers) AS score FROM rtindex0,rtindex1,rtindex2,rtindex3 ORDER BY score DESC LIMIT 7");

	while($data = $query->fetch_assoc()){
		$iquery = $db->query("SELECT title FROM rtindex WHERE id = '{$data['id']}';");
		$idata = $iquery->fetch_assoc();
		$data['title'] = $idata['title'];
		if($data['category']!=8){
			$rtype = "<img src=\"images/".Rivr::getTypeInt($data['category']).".png\" class=\"icon\"/>";
		}else{
			$rtype = '';
		}
		echo "<tr class=\"row\"><td style=\"width:700px;\">$rtype<a href=\"{$data['hash']}\">{$data['title']}</a></td><td width=\"70px\" class=\"seeds\">{$data['seeds']}</td><td width=\"70px\" class=\"leech\">{$data['peers']}</td><td width=\"90px\">".bytesToStr($data['size']*1024)."</td></tr>";
	}

echo "</table><br/>";

$categories = array(0=>'Movies',1=>'TV',3=>'Games',2=>'Software',4=>'Music',6=>'Anime',7=>'Books',5=>'Porn');

foreach($categories as $key => $val){
	echo "<table class=\"rtable\">";
	echo "<tr class=\"top_row\"><td colspan=\"4\">$val</td></tr>";

	$query = $sdb->query("SELECT *, (seeds+0.2*peers) AS score FROM rtindex0,rtindex1,rtindex2,rtindex3 WHERE category = $key ORDER BY score DESC LIMIT 7");

	while($data = $query->fetch_assoc()){
		$iquery = $db->query("SELECT title FROM rtindex WHERE id = '{$data['id']}';");
		$idata = $iquery->fetch_assoc();
		$data['title'] = $idata['title'];
		if($data['category']!=8){
			$rtype = "<img src=\"images/".Rivr::getTypeInt($data['category']).".png\" class=\"icon\"/>";
		}else{
			$rtype = '';
		}
		echo "<tr class=\"row\"><td style=\"width:700px;\">$rtype<a href=\"{$data['hash']}\">{$data['title']}</a></td><td width=\"70px\" class=\"seeds\">{$data['seeds']}</td><td width=\"70px\" class=\"leech\">{$data['peers']}</td><td width=\"90px\">".bytesToStr($data['size']*1024)."</td></tr>";
	}

	echo "</table><br/>";
}

?>

</div>

<?php include("includes/footer.php"); ?>

</div>
</body>

</html>