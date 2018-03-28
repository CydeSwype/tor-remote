<?php

define('PASSWORD', '1234567890abcdefghijklmnoetc'); // <-- set me to something random!  for helping to keep your server from getting inauthentic requests

define('TORRENT_CLIENT', 'transmission'); // Currently supports qbtorrent or transmission
define('SAVE_PATH', '/Volumes/videos/movies');

define('QUEUE_FILE_NAME', 'queue.txt');
define('QUEUE_RESET_WEB_ADDRESS', 'http://foo.com/tor_remote/server.php?pass=' . PASSWORD. '&action=reset_queue');
define('QUEUE_FILE_WEB_ADDRESS', 'http://foo.com/tor_remote/' . QUEUE_FILE_NAME);

// if using transmission as torrent client
define('TRANSMISSION_BIN_PATH', '/usr/local/bin/transmission-remote');

// if using transmission auth
define('TRANSMISSION_AUTH', '--auth user:pass');
