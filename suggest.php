<?php

require_once('includes/config.php');
require_once('includes/rivr.php');
require_once('includes/functions.php');

$q = clean_text(mb_strtolower(htmlspecialchars(urldecode($_GET['q']))));

$date = time();

$query = $db->query("SELECT query, type FROM queries WHERE query LIKE '$q%' ORDER BY count+count/POW(2,($date-GREATEST(first_date,$date - 300000))/21600) DESC;");

while($data = $query->fetch_assoc()){
	$suggestions[] = $data['query'];
}

echo "[\"$q\", ".json_encode($suggestions).']';

?>
