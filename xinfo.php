<?php

require_once('includes/config.php');
require_once('includes/rivr.php');
require_once('includes/functions.php');

$type = $_GET['type'];
$q = urldecode($_GET['q']);

if($q && ($type==0||$type==1)){
	
	$imdb_id = Rivr::findImdbId($q);
	if($imdb_id>0){
		$imdb_id = 'tt'.str_pad($imdb_id, 7, '0', STR_PAD_LEFT);
		$mdata = json_decode(gethtml("http://www.omdbapi.com/?i=$imdb_id&plot=short&r=json"), true);
		$poster = "cache/imdb_posters/$imdb_id.jpg";
		if(!file_exists($poster)){
			$ch = curl_init($mdata['Poster']);
			$fp = fopen($poster, 'wb');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
		}
		echo "<a class=\"image\" href=\"$poster\"><img src=\"$poster\" class=\"poster\"></a><span class=\"title\">{$mdata['Title']} ({$mdata['Year']}) &middot; {$mdata['Type']}</span><br/><br/><a class=\"ptag\" href=\"http://www.imdb.com/title/$imdb_id/\" target=\"blank\">IMDB link</a><a class=\"ptag\" href=\"#\" id=\"trailer\">Watch trailer</a><br/><br/><b>IMDB rating:</b> {$mdata['imdbRating']} ({$mdata['imdbVotes']} votes)<br/>{$mdata['Rated']} &middot; {$mdata['Runtime']} &middot; {$mdata['Genre']} &middot; {$mdata['Released']}<br/><br/><div id='plot'><b>Plot:</b> {$mdata['Plot']}</div><br/><b>Actors:</b> {$mdata['Actors']}";
?>

<script>
$(document).ready(function(){
	var yt_url='https://content.googleapis.com/youtube/v3/search?part=snippet&type=video&videoEmbeddable=true&lr=en&orderby=viewCount&maxResults=1&hl=en&key=AIzaSyB0Jm1M4z4ffP3yFdEPFk-sd9XU5JabZLM&q=<?php echo urlencode(urldecode($mdata['Title'])); ?>+official+trailer';
	$.ajax({
		type: 'GET',
		url: yt_url,
		dataType: 'jsonp',
		success: function(response){
			if(response.items){
				$.each(response.items, function(i,data){
					var video_id=data.id.videoId;
					$('#trailer').attr('href','https://www.youtube.com/watch?v='+video_id);
					$('#trailer').magnificPopup({
						type:'iframe',
						disableOn: 700,
						mainClass: 'mfp-fade',
						removalDelay: 160,
						preloader: false,
						fixedContentPos: false
					});
				});
			}
		}
	});
	$('.image').magnificPopup({type:'image',mainClass:'mfp-fade',removalDelay: 160});
});
</script>

<?php
	}
}

?>