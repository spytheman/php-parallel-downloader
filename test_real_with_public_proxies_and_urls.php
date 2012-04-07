<?php
include_once("paralleldownloader.php");

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// This is a test using real proxies (working as of 2012/02/01), and some real URLs
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$startproxies = array(
                      "", // this is a direct connection, without proxification
                      "190.82.89.154:3128",
                      "216.250.115.44:80",
                      "212.118.224.154:80",
                      "113.53.252.131:3128",
                      "8.28.19.74:80",
                      "8.28.19.76:443",
                      "57.90.36.24:80",
                      "209.51.184.6:80",
                      "95.168.218.193:53",
                      "85.232.57.198:80",
                      "85.214.23.161:8081",
                      );
$starturls = array(
                   "http://www.google.com/",
                   "http://www.dir.bg/",
                   "http://www.catcar.info/",
                   "http://www.facebook.com/",
                   "http://www.yahoo.com/",
                   "http://www.pozdravi.net/test.txt",
                   "http://www.paribg.com/admin.css",
                   "http://jaltici.com/static/website.js",
                   "http://www.tejkari.com/style.css",
                   "http://tbox7.com/",
                   "http://vps6.bgwebart.com/",
                   );

// Instantiate the downloader and run it:
$t1 = microtime(true);
$d = new DownloadAndShow();
$d->debug = false;
$d->tlog("Start the downloading...");
$d->parallel_download_using_proxies($startproxies, $starturls);
$d->tlog("Downloading finished.");
$t2 = microtime(true);
$tdiff = sprintf("%8.3f", (float) round(1000*($t2 - $t1))/1000);
$d->tlog("Total time: {$tdiff} sec");
