![alt tag](https://lh3.googleusercontent.com/-0JULKfbRHrA/Vq0aRRzEdfI/AAAAAAAAAaw/iB7-4qd4y0k/s920-Ic42/promo.png)
# RIVR
RIVR is the world's first open source torrent search engine. It scrapes torrent websites (Kickass, ThePirateBay, isoHunt, Limetorrents, 1337x), and gives fast results with a ranking based on torrent hash, seeders and leechers. For each torrent it provides a magnet link and when available a torrent file displaying its contents. RIVR is a personal project I decided to make open source with the hope of finding people to build a distributed torrent search engine with high quality results.

# Features
* Scrapers for Kickass, ThePirateBay, isoHunt, Limetorrents, 1337x
* Fast search using Sphinx
* Autocomplete suggestions
* Movie & TV series info when the search query is relevant
* View torrent contents

![alt tag](https://lh3.googleusercontent.com/fq6PcaMIfOEj1T27hHU9EtZgujSwiNMFZhoqoFhIYm4SnAkbGLMaTbLHfvObsw1XClrq4irViCg=w1920-h1152-no)

# Installation
* Create a MySQL database and import schema.sql, sources.sql and trackers.sql from install folder.
* Download and import the MySQL dump from https://goo.gl/vW0kqd. It contains ~13M torrents.
* Edit includes/config.php with your MySQL host, username, password and database name.
* Edit install/sphinx.conf.in (mysql_all source) with your MySQL host, username, password and database name and change all the file paths to yours.
* Use install/sphinx.conf.in to create the Sphinx indexes:

  > sudo -u sphinx indexer --config sphinx.conf.in --all
  
* Access Sphinx indexes with MySQL client:

  > mysql -h 0 -P 9306
 
* Execute the following commands to attach regular indexes to real-time indexes:

  > ATTACH INDEX orig TO RTINDEX rtindex;
 
  > ATTACH INDEX mysql0 TO RTINDEX rtindex0;
 
  > ATTACH INDEX mysql1 TO RTINDEX rtindex1;
 
  > ATTACH INDEX mysql2 TO RTINDEX rtindex2;
 
  > ATTACH INDEX mysql3 TO RTINDEX rtindex3;
 
* Start the Sphinx search daemon.
 
* Create a cron job to call admin/indexer.php for example every 12 or 24 hours.

* admin/tupdater.php updates the seeds/leech of all torrents in groups of 73. The final numbers are the maximum among those found in all the trackers. If we have 10M torrents this makes ~960,000 requests in total to the 7 torrent trackers, so it is not recommended to run it at the moment until we define some criteria to limit the number of torrents we need to get stats every time (for example we don't want recently (re)indexed links).

# To Do
* Limit the torrents we need to update the seeds and peers in admin/tupdater.php
* Improve the ranking formula
* Add more search options
* Make speed optimizations
* Add user features (sign up, sign in, comments, votes)
* Make the interface responsive


# Donate
The search engine is at an early stage and many optimizations and expensive hardware are needed to put it live. The main problem is the refreshing of the torrents seeders and leechers that needs to be done for millions of torrents every 12 or 24 hours. That's why we need a few donations.

bitcoin:3EtjNVUcEc3fSab2U9JH9uHmr7aCoKcP5K

# License
![alt tag](https://camo.githubusercontent.com/0e71b2b50532b8f93538000b46c70a78007d0117/68747470733a2f2f7777772e676e752e6f72672f67726170686963732f67706c76332d3132377835312e706e67)

You can redistribute and/or modify RIVR under the terms of the GNU General Public License v.3 as published by the Free Software Foundation.
