<?php
include_once("paralleldownloader.php");

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// This is a test using real proxies (working as of 2012/02/01), and some real URLs
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$startproxies = array(
                      "", // this is a direct connection, without proxification
                      "113.53.252.131:3128", 
                      "189.3.225.99:3128", 
                      "110.139.150.155:80", 
                      "173.15.50.1:80", 
                      "203.66.188.248:80",
                      "190.82.89.154:3128",
                      "190.207.78.234:8080",
                      "41.134.32.131:3129",
                      );
$starturls = array(
                   "http://www.google.com/",
                   "http://www.dir.bg/",
                   "http://www.catcar.info/",
                   "http://www.facebook.com/",
                   "http://www.yahoo.com/",
                   "http://www.pozdravi.net/",
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
