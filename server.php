<?php

/////
// this script will live on your web host on a server that you can access anywehere

if (file_exists('my_config.php')){
	require('my_config.php'); // if you'd like to keep personal settings apart from the defaults
  } else {
	require('config.php');
  }
  
function write_torrent_url_to_queue($url){
	$queue_file = QUEUE_FILE_NAME;
	$result = file_put_contents($queue_file, $url."\n", FILE_APPEND);
	return $result;
}

function fetch_leetx_torrent_url($asset_detail_url){
	// takes a 1337x asset (aka movie) detail page URL and scrapes the download/magnet URL from it
	$result = file_get_contents('http://1337x.to' . $asset_detail_url);
	
	// split on the string "magnet:" as this is the href that matters
	$temp = explode('"magnet:', $result);

	// then after finding the magnet: string, split on '"' since the page has to end the URL with a quote to get back to HTML
	$magnet_url = explode('"', $temp[1]);

	// getting the content between the "magnet:" and the following quotes should yield the magnet URL, now we just need to prepend the "magnet:" back on (since we split on it)
	return 'magnet:' . $magnet_url[0];
}

function get_between($start, $end, $str){
	$temp = explode($start, $str);
	$temp = explode($end, $temp[1]);
	$result = $temp[0];

	return $result;
}

function search_leetx($search){
	$url = 'http://1337x.to/search/' . urlencode($search) . '/1/';
	$result = file_get_contents($url);

	$result = strstr($result, '"box-info-detail');

	$result_array = explode('<tr>', $result);
	$i = 0;

	foreach ($result_array as $row){
		// get the detail page url
		$temp = explode('<a href="', $row);
		$temp = explode('">', $temp[2]);
		@$search_results_array[$i]->asset_detail_url = $temp[0];

		// get the seeder count
		$temp = get_between('<td class="coll-2 seeds">', '</td>', $row);
		$search_results_array[$i]->seed_count = strip_tags($temp);

		// get the leecher count
		$temp = get_between('<td class="coll-3 leeches">', '</td>', $row);
		$search_results_array[$i]->leech_count = strip_tags($temp);

		// get the filesize
		$temp = get_between('<td class="coll-4 size mob-vip">', '<span', $row);
		if ($temp == ''){
			$temp = get_between('<td class="coll-4 size mob-user">', '<span', $row);			
		}
		if ($temp == ''){
			$temp = get_between('<td class="coll-4 size mob-uploader">', '<span', $row);			
		}
		$search_results_array[$i]->file_size = strip_tags($temp);

		if ($search_results_array[$i]->asset_detail_url){
			$i++;
		}
	}

	return $search_results_array;
}

$action = $_REQUEST['action'];
$pass = $_REQUEST['pass'];

if ($pass != PASSWORD){
	die();
}

if ($action == 'reset_queue'){
	file_put_contents(QUEUE_FILE_NAME, '');
}

if ($action == 'search'){
	$search_results_array = search_leetx($search);
}

if ($action == 'get_magnet_url'){
	$torrent_url = fetch_leetx_torrent_url($asset_detail_url);
	$write_result = write_torrent_url_to_queue($torrent_url);
	echo "<a href=\"$torrent_url\">$torrent_url</a><br/>Result: $write_result<br/>\n<b>URL written to download queue</b><hr/>\n\n";
}

?>

<form action="index.php" method="get">
<input type="text" name="search" value="<?php echo $search; ?>" style="width:80%; height:10%; font-size:48px"/>
<input type="hidden" name="action" value="search"/>
<input type="submit" value="search" style="width:15%; height:10%; font-size:48px"/>
</form>

<?php

if ($search_results_array){
	echo "<table><tr><td>Page path</td><td>S/L</td><td>filesize</td></tr>";
	foreach ($search_results_array as $search_result){
		echo "<tr><td><a href='index.php?action=get_magnet_url&asset_detail_url=$search_result->asset_detail_url'>$search_result->asset_detail_url</a></td><td>$search_result->seed_count/$search_result->leech_count</td><td>$search_result->file_size</td></tr>";
	}
	echo "</table>";
}

?>


