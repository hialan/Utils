<?php
require_once __DIR__ . '/getsong_core.php';

echo "好樂迪排行榜："; 
foreach($menu as $key => $str)
{
	if($key == $kind && $source == "holiday")
		$str = "<b>$str</b>";

	echo "<a href=\"?kind=$key&source=holiday\">$str</a> | ";
}
echo "<br>\n";
echo "星聚點排行榜："; 
foreach($menu as $key => $str)
{
	if($key == $kind && $source == "newcb")
		$str = "<b>$str</b>";

	echo "<a href=\"?kind=$key&source=newcb\">$str</a> | ";
}

echo "<hr>\n";

// default: first song
if(empty($song_name))
{
  $song = $total_songs[0];
  $song_name = "{$song['singer']} {$song['song']}";
}

if(!empty($song_name))
{
  $song_hash_key = YOUTUBE_CACHE_PREFIX . md5($song_name);
  if(apc_exists($song_hash_key))
  {
    $video_id = apc_fetch($song_hash_key);
  }
  else
  {
    $song_list = SearchYoutube("$song_name MV");
    $video_id = $song_list['query']['results']['video'][0]['id'];
    apc_store($song_hash_key, $video_id, SONG_CACHE_SECOND); 
  }
?>
<H1><?php echo $song_name; ?></H1>

  <script type="text/javascript" src="swfobject.js"></script> 
  <div id="ytapiplayer">
    You need Flash player 8+ and JavaScript enabled to view this video.
  </div>
  <script type="text/javascript">
    var videoId = "<?php echo $video_id; ?>";

    var params = { allowScriptAccess: "always" };
    var atts = { id: "myytplayer" };
    swfobject.embedSWF("http://www.youtube.com/v/"+ videoId +"?enablejsapi=1&playerapiid=ytplayer&version=3",
                     "ytapiplayer", "425", "356", "8", null, null, params, atts);

    function onYouTubePlayerReady(playerId) {
      var videoId = "<?php echo $video_id; ?>";
      play(videoId);
    }

    function play(videoId)
    {
      ytplayer = document.getElementById("myytplayer");
      ytplayer.loadVideoById(videoId, 0, "large");
      ytplayer.playVideo();
    }

  </script>
<?php
}


function renderHTML(array $songs)
{
	global $kind;
	global $source;

	echo "<ul>";
	foreach($songs as $song)
	{
		$query = array(
				"search_query" => "{$song['singer']} {$song['song']} MV",
			      );

		$link = "https://www.youtube.com/results?" . http_build_query($query);
		$songUrl = urlencode("{$song['singer']} {$song['song']}");

		$params = array(
				"song" => "{$song['singer']} {$song['song']}",
				"kind" => $kind,
				"source" => $source,
		);

		echo "<li><a href=\"?" . http_build_query($params) . "\">" . implode(' ', $song) . "</a> [<a href=\"{$link}\" target=\"_blank\">Search YouTube</a>]</li>\n";
	}

	echo "</ul>";
}

renderHTML($total_songs);

$apcInfo = apc_cache_info( 'user' );
$apcInfo = $apcInfo['cache_list'];
foreach($apcInfo as $info)
{
	if($info['info'] == $songlist_cache_key)
	{
		echo "<p>最後更新: " . date('Y-m-d H:i:s', $info['mtime']) . "</p>";
		break;
	}
}

?>
