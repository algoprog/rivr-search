<?php

require_once('includes/config.php');

$query = $sdb->query("SELECT COUNT(*) AS total FROM rtindex0,rtindex1,rtindex2,rtindex3");
$d = $query->fetch_assoc();
$total = number_format($d['total']);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="description" content="Torrents search engine">
<meta name="keywords" content="torrents, search engine, rivr">
<link rel="icon" type="image/png" href="images/favicon.png">
<title><?php echo ENGINE_NAME; ?> ~ Torrents Search Engine</title>
<link rel="search" href="/opensearch.xml" type="application/opensearchdescription+xml" title="Rivr"/>
<link rel="stylesheet" type="text/css" href="css/main.css"/>
<link rel="stylesheet" type="text/css" href="css/jquery-ui.css"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="js/search.js"></script>
</head>

<body>

<img src="images/loading.png" id="loading"/>

<div id="container">

<div id="home">

<img src="images/logo.png"/><br/><br/>

<form action="search" method="GET" id="search">
<input type="search" name="q" id="q" class="q" autofocus required/><span id="sbt"></span>
<br/><br/>
<p>search <?php echo $total; ?> active torrents &middot; <a href="browse">Browse</a></p>
</form>

</div>

<?php include("includes/footer.php"); ?>

</div>
</body>

</html>