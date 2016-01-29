<?php

require_once(dirname(dirname(__FILE__)).'/includes/functions.php');
require_once(dirname(dirname(__FILE__)).'/includes/config.php');
require_once(dirname(dirname(__FILE__)).'/includes/tscraper/udptscraper.php');
require_once(dirname(dirname(__FILE__)).'/includes/tscraper/httptscraper.php');

ini_set('max_execution_time', 3*86400); // 3 days
ini_set('memory_limit', '256M');

$trackers =
array(
'udp://tracker.openbittorrent.com:80',
'udp://tracker.coppersurfer.tk:6969',
'udp://9.rarbg.com:2710',
'udp://9.rarbg.me:2710',
'udp://glotorrents.pw:6969',
'udp://tracker.trackerfix.com:80',
'udp://tracker.leechers-paradise.org:6969');

$from = 0;

$query = $db->query("SELECT DISTINCT(hash) FROM rtindex WHERE hash!='' LIMIT $from, 73;");
$num = $query->num_rows;
while($num){
	unset($hashes);
	unset($seeds);
	unset($leech);
	while($data = $query->fetch_assoc()){
		$hashes[] = $data['hash'];
	}
	foreach($trackers as $tracker){
		unset($scraper);
		try{
			$timeout = 2;
			$scraper = new udptscraper($timeout);
			$ret = $scraper->scrape($tracker,$hashes);
			foreach($ret as $hash => $val){
				$seeds[$hash] = max($val['seeders'],$seeds[$hash]);
				$leech[$hash] = max($val['leechers'],$leech[$hash]);
			}
		}catch(ScraperException $e){}
	}
	foreach($hashes as $hash){
		$db->query("UPDATE rtindex SET seeds = {$seeds[$hash]}, peers = {$leech[$hash]} WHERE hash = '$hash';");
		$sdb->query("UPDATE rtindex0,rtindex1,rtindex2,rtindex3 SET seeds = {$seeds[$hash]}, peers = {$leech[$hash]} WHERE hash = '$hash';");
	}
	$from+=73;
	$query = $db->query("SELECT DISTINCT(hash) FROM rtindex WHERE hash!='' LIMIT $from, 73;");
	$num = $query->num_rows;
}

//$tracker = 'http://glotorrents.com:6969/announce';
//$tracker = 'http://mgtracker.org:2710/announce';
/*
try{
	$timeout = 2;
	//Read only 8MiB of the scrape response
	$maxread = 1024 * 8;
	
	$scraper = new httptscraper($timeout,$maxread);
	$ret = $scraper->scrape($tracker,array('260648f78582b3127867b28bde1b42e91e949e2f','53aebbbb8f2343d7ce9a3cbae793d9921283bf52','871978b8dddd1c4718e7ed99817a2a9ead7e669c','93d7eede890ba8d2d8b63cb9b2ef5bb85c63b83c','06b82416136ad52063287ae9300e72c755760895','0eb290078b085480e4d8597fc734b5efd548af56','1bcc5925bc4949c0fa41ea54e3e9982461f17e81','1e10ab8480ed6a930af2409c18addeed86f05fa3','5aef97311d6922332a390fe2b82e12527db7af5a','a2ca9d977654a8ea966e010adf26c2f12668f52f','0f2bd4522334d4cf33ac440b89420600ad4aec3c','54589c375c66fbcda99c73658aac0800a29e8235','73d7f431ce1ae0a7be77e8cc7d71ad37224b16ad','34f9d1d8cacea6d8e860a8edac7baff981b2e8f5','35925733f92369c61aa3fc6c2263f27cd60381a9','9c84e7d37dbf73f1169f1543b497bfbd77807803','2c67521d4df3e4ff0ae1fcd05323a19269f63c18','75f2580d88f7c332276d042f6698fb065f5e1c82','92956e3d662691e89d1adb9fd755350886a4f26a','93a304279c73579873abdabba126f79efed5a86a','528aa372f06651e4f2fb6d0b9f40e105bbb35f7e','5362d0319ec69767bb339861316fd3db45be69d2','0a175ff75b5b62564b2b2c797c126303354c84ee','0ba5b111b7652e43d3bc714fd5b2b14d4d22a9b8','171765be4ac23b2f99b4215a42c1036f8c92f9a3','5daa2cca170ddd4e70860e3a8f31b6bebc5a62fd','a3b224a6576a39f6364a34d8b05d54a55c002f87','08335b2aaa84a3516b5127ed2514460b20786f70','76892eeb53cecabbeda60d037eefa4b89b8e7e29','85f840117a2a942335086b45bd6588557ad50f5e','1381cb13922c032ae8cba0046f5c3c4a677d90d5','7d621f61ce0eb099f72bc24d61c7d415d3fc0291','a686bb2c39a3ba873c045b26910f9b650aeeb7bb','49ab2f7e5bd9d9b2d8f86f3edc031287271e7549','558a7e857457ef932184f76404b1f9156c1cf72e','16359f9b52983b37ff06fb83056b0ba8db5b6643','3534b6b2a2a7a74d70f51dd9741d077283fe88dd','3d77859ece25a792e2574e256c1be4c5b2a4648b','55505a8569e97cc9b657fa7cb6fa5be9f5c89a27','919d6c3e35128f019f918eab0928ba12df51e1f3','651d46bdb168d82f23007d6d6efcb34d7a90e705','13ecae39b2887d7a049de1a34e41cb0f092d33ed','2aa8fc9a486d94b3e21ac3c774b064789765fdc8','73a91373fd30bcd4b15de881e38b2b6f2c1edbad','491e1501d77bb8726df8353d0818ed3c57c2150d','7b2568dda7cfb6345aabd12259906c9a589d88ee','3ea63bc30e5b5daf0db079df3c0b901dfbd3839b','4b62f5e90775abb58b90e7aa4dab2c553def84c8','5d155e2eb55dde2141e4c63ce98d703fa4b34274','61c7d532b89ef0360114d3100c129cbb0f5c2a80','0477c03a7c27555bee24f123d774662ade08d23f','0d9b3292a81fc36e362ade3fda98851f2fe73ac2','11b4f87abe155e61f09bc120b012a8113002a7c5','47999cf96bc1ddc34a8514723d6fcd545b75a26d','7b2f1109e18f3de43acfe008f6e376842c2a4b4a','7fb7413b75d42175a17202c5858762166de2cfd7','9730b1d3474cf7f26e35b5d889d8bcf66a983e67','1d6906e80d2e0dbdbf1fce97460aac6fd3fc557d','3ae122a2ac094190fba651ee1822b231ce8e4768','4d3d85f5e68ed9e9b7537ec1e0f6620a33f2a0e0','7211426c34f38ef6dc93b62545f8f23f159071f9','81b9756813263046ffad56d12c67102152fce994','9be1347a36f374056e5b2d36153bc35f1c36b9cc','a71e8a74c081de08235c294683c06e03be89fb38','1cc93b9ca8439571bc83e8b257b3326d63eb40b7','3099c7460f53fcc699b56f2722baeabdcc4663f2','5f9a64dfb54f5839c7bfaec6b0a91975ed9ab8fe','78fad59b8aa3e60d050680c152b501ce12ddd1ef','8cb31bf8249194ff83eedf407beb1d33dd316444','292ea88e00ee42e4a960f31ecb8fb544cfd7d85b','2fe2fb67750ce217d63a1fe0a490b2e329386d83','4d3b87b4ff225303de276a29a0396930174c2ef8','4fb860bb75254b7d4f716a73f50f9bd419a812c3','5543fc94e3ad02b905264ebc0b07bc354400c77e'));
	
	print_r($ret);
}catch(ScraperException $e){
	echo('Error: ' . $e->getMessage() . "<br />\n");
	echo('Connection error: ' . ($e->isConnectionError() ? 'yes' : 'no') . "<br />\n");
}
*/

?>