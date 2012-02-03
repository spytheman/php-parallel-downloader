<?php
include_once("paralleldownloader.php");

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// This is a syntetic test, used during development. It needs some setup beforehand, like starting node instances with the 
/// slowproxy.js script on ports 3120-3124 with different big delay times, and installing and configuring tinyproxy to be 
/// present on port 8888.
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$startproxies = array(
                      "",                     //No proxy at all (just for completeness).                      
                      "127.0.0.1:8888",       // a fast noncaching 'tinyproxy' instance.
                      
                      /////////////////// some 'slowproxy' nodejs instances follow below:
                      "127.0.0.1:3120", 
                      "127.0.0.1:3121", 
                      "127.0.0.1:3122", 
                      "127.0.0.1:3123", 
                      "127.0.0.1:3124", 
                      );
$starturls = array(
                   "http://127.0.0.1/",
                   "http://127.0.0.1/test.txt",
                   );

// Add some more URLS:
for($i=0;$i<100;$i++){
    $starturls[]= "http://127.0.0.1/random.php?id=$i";
}

// Instantiate the downloader and run it:
$d = new DownloadAndShow();
$d->tlog("Start the downloading...");
$d->parallel_download_using_proxies($startproxies, $starturls);
$d->tlog("Downloading finished.");
