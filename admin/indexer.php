<?php

require_once(dirname(dirname(__FILE__)).'/includes/sites/kickass.indexer.php');
require_once(dirname(dirname(__FILE__)).'/includes/sites/thepiratebay.indexer.php');
require_once(dirname(dirname(__FILE__)).'/includes/sites/isohunt.indexer.php');
require_once(dirname(dirname(__FILE__)).'/includes/sites/1337x.indexer.php');
require_once(dirname(dirname(__FILE__)).'/includes/sites/limetorrents.indexer.php');

require_once(dirname(dirname(__FILE__)).'/includes/functions.php');
require_once(dirname(dirname(__FILE__)).'/includes/config.php');

ini_set('max_execution_time', 3*86400); // 3 days
ini_set('memory_limit', '256M');

$t1 = microtime(true);

$kickass = new Kickass(1);
$torrents['kickass'] = $kickass->start();

$tpb = new Thepiratebay(1);
$torrents['tpb'] = $tpb->start();

$isohunt = new Isohunt(1);
$torrents['isohunt'] = $isohunt->start();

$_1337x = new _1337x();
$torrents['1337x'] = $_1337x->start();

$limetorrents = new Limetorrents(1);
$torrents['limetorrents'] = $limetorrents->start();

$time = microtime(true)-$t1;

$added = $torrents['kickass'][0]+$torrents['tpb'][0]+$torrents['isohunt'][0]+$torrents['1337x'][0];
$updated = $torrents['kickass'][1]+$torrents['tpb'][1]+$torrents['isohunt'][1]+$torrents['1337x'][1];
$db->query("INSERT INTO crawls SET source_id = '0', added_torrents = '$added', updated_torrents = '$updated', time = '$time';");

$db->query("UPDATE sources SET torrents = (SELECT COUNT(*) FROM rtindex WHERE source_id = sources.id);");

echo json_encode($torrents);

?>