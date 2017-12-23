<?php

//////
// this script will live on your home media server (this server will perform downloads, 
// likely the same server that runs plex media server for you) 

require('config.php');

function parse_queue(){
	$queue_file = QUEUE_FILE_WEB_ADDRESS;
	$result = file_get_contents($queue_file);
	$urls = explode("\n", $result);

	foreach ($urls as $url){
		if ($url){
			add_torrent_url($url);
		}
	}
	$result = file_put_contents($queue_file, '');
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
    $result = system('transmission-cli ' . $magnet_url . ' -w ' . $save_path, $retval);
  }
}

if ($action == 'parse_queue'){
	echo 'parsing download queue, then deleting';
	parse_queue();
}

