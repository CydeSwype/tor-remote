# tor-remote
part server (searching l337x from a personal web server and storing a magnet link) and part client (fetching new magnet links from a plex media box and feeding them to a tor app to download)

* clone this repo to your public web server (this service will use the server.php script)
* clone this repo to your home media server (this service will use the client.php script)
* run the built-in php server on your home machine (or any web server): php -S localhost:8686
* schedule a task (i.e. crontab) on your home media machine to hit the client script at a regular interval (i.e. '* * * * * wget -O - localhost:8686/client.php?action=parse_queue > /dev/null 2>&1')

Notes:
* Mac crontab requires a bit more setup than linux.  You'll want to tell crontab how to get to things like wget by setting path.  These lines at the top of your crontab will help:

```
#!/bin/sh
PATH=/usr/local/bin:/usr/local/sbin:~/bin:/usr/bin:/bin:/usr/sbin:/sbin
```

* Mac doesn't usually have wget installed by default, so your crontab won't work with wget until you install it.  "brew install wget" solves this.