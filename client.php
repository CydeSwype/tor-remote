<?php

//////
// this script will live on your home media server (this server will perform downloads, 
// likely the same server that runs plex media server for you) 

//error_reporting(E_ALL); ini_set('display_errors', '1');

if (file_exists('my_config.php')){
  require('my_config.php'); // if you'd like to keep personal settings apart from the defaults
} else {
  require('config.php');
}

function parse_queue(){
	$queue_file = QUEUE_FILE_WEB_ADDRESS;
	$result = file_get_contents($queue_file);
	echo "<textarea style=\"font-size:12px; width:100%; height:200px\">$result</textarea>";

	// store the queue file in local cache in case server becomes unavailable and we're not successful in adding to torrent client (i.e. if the client isn't running)
	if (!$result) {
		// if we can't get the remote queue file, try getting the local cached queue instead
		$result = file_get_contents('cache.txt');
	}
	if ($result) {
		echo "storing in local cache, in case adding to torrent client fails and we need to retry later<br/>";
		file_put_contents('cache.txt', $result);

		$urls = explode("\n", $result);

		foreach ($urls as $url){
			if ($url){
				add_torrent_url($url);
			}
		}
  
		sleep(5);

		// reset remote download queue
		$result = file_get_contents(QUEUE_RESET_WEB_ADDRESS);
	}
}

function add_torrent_url($magnet_url){
	// takes a torrent download URL and adds it to qbtorrent via WebUI API (assumes WebUI is enabled and auth is not required for localhost)
  // ONLY SUPPORTS MAGNET LINKS AT THIS TIME
  if (!$magnet_url){
		return false;
	}

  if (TORRENT_CLIENT == 'qbtorrent'){
    $postdata = http_build_query(
      array(
        'urls' => $magnet_url
      )
    );

    $opts = array('http' =>
      array(
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => $postdata
      )
    );

    $context = stream_context_create($opts);

    $result = file_get_contents('http://localhost:8081/command/download', false, $context);
  }

  if (TORRENT_CLIENT == 'transmission'){
    // transmission-cli magnet:?xt=urn:btih:e249fe4dc957be4b4ce3ecaac280fdf1c71bc5bb&dn=ubuntu-mate-16.10-desktop-amd64.iso -w ~/Downloads
    $system_cmd = TRANSMISSION_BIN_PATH . ' ' . TRANSMISSION_AUTH . ' -a "' . $magnet_url . '" -w "' . SAVE_PATH . '"';
    
    $result = system($system_cmd, $retval);
  }

	// if we get any result, let's assume we were successful in adding the torrent to the client, so now we can kill the cache
	if (file_exists('cache.txt') && $result){
		unlink('cache.txt');
		echo "adding file to torrent client successful, deleting local cache<br/>";
	} elseif (!$result){
		echo "adding file to torrent client unsuccessful<br/>";
	}
}

$action = $_REQUEST['action'];

if ($action == 'parse_queue'){
	echo 'parsing download queue, then deleting';
	parse_queue();
}

