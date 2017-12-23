# tor-remote
part server (searching l337x from a personal web server and storing a magnet link) and part client (fetching new magnet links from a plex media box and feeding them to a tor app to download)

* clone this repo to your public web server (this service will use the server.php script)
* clone this repo to your home media server (this service will use the client.php script)
* run the built-in php server on your home machine (or any web server): php -S localhost:8000
* schedule a task (i.e. crontab) on your home media machine to hit the client script at a regular interval (i.e. 'wget localhost:8080/client.php?action=parse_queue > /dev/null')
