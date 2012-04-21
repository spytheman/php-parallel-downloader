#!/usr/bin/env php
<?php

/*
The MIT License (MIT)
Copyright © 2012 <Delyan Angelov (delian66@gmail.com)>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

include("paralleldownloader.php");


function usage(){
    echo "Usage: \n";
    echo "   filedownloader.php  --proxiesfile=proxies.txt --urlsfile=urls.txt --verbose=100 --skipalreadyexistingfiles\n";
    echo "   filedownloader.php  --proxiesfile=proxies.txt --urlsfile=urls.txt -v\n";
    echo "   filedownloader.php  --proxiesfile=proxies.txt --urlsfile=urls.txt -v  --referrer=http://www.dir.bg/\n";
    echo "   filedownloader.php  --proxiesfile=proxies.txt --urlsfile=urls.txt -v  --referrer=http://www.dir.bg/ --chunksize=20 #### download maximum 20 urls at once\n";
    echo "   filedownloader.php  -p=proxies.txt -u=urls.txt\n";
}

$proxyfile = "";
$urlsfile  = "";
$sparsity = 1000; // do not show anything by default
$referrer = "http://www.google.com/";
$chunksize = 10;
$chunksleepmin = 2;
$chunksleepmax = 10;
$skipalreadyexistingfiles = false;

$shortopts  = "";
$shortopts .= "u:";
$shortopts .= "p:";
$shortopts .= "v";
$shortopts .= "r";
$shortopts .= "c";
$longopts  = array(
                   "urlsfile:",
                   "proxiesfile:",
                   "verbose::",
                   "referrer::",
                   "chunksize::",
                   "chunksleepmin::",
                   "chunksleepmax::",
                   "skipalreadyexistingfiles::",
                   );
$options = getopt($shortopts, $longopts);
if(isset($options['u']))$urlsfile = $options['u'];
if(isset($options['urlsfile']))$urlsfile = $options['urlsfile'];
if(isset($options['p']))$proxyfile = $options['p'];
if(isset($options['proxiesfile']))$proxyfile = $options['proxiesfile'];
if(isset($options['v']))$sparsity = 100;
if(isset($options['verbose']))$sparsity = $options['verbose'];
if(isset($options['r']))$referrer = $options['r'];
if(isset($options['referrer']))$referrer = $options['referrer'];
if(isset($options['c']))$chunksize = $options['c'];
if(isset($options['chunksize']))$chunksize = $options['chunksize'];
if(isset($options['chunksleepmin']))$chunksleepmin = $options['chunksleepmin'];
if(isset($options['chunksleepmax']))$chunksleepmax = $options['chunksleepmax'];
if(isset($options['skipalreadyexistingfiles'])){
    $skipalreadyexistingfiles = true;
    __tlog("Skipping already existing files is ON.");
}

if(!file_exists($proxyfile)){__tlog("File '{$proxyfile}' does not exist."); usage(); exit(1);}
if(!file_exists($urlsfile)){__tlog("File '{$urlsfile}' does not exist."); usage(); exit(1);}

$zproxies = file($proxyfile);
$proxies = array();
foreach($zproxies as $p)  {
    if($p[0]==='#')continue; // ignore comments
    $p=str_replace("\n", "", $p);
    $proxies[]=$p;
}

$urls = file($urlsfile);

$upaths = array();
foreach($urls as $line){
    list($url, $path) = explode("||", str_replace("\n","",$line));

    if($skipalreadyexistingfiles && file_exists($path)){
        __tlog("Skipping file: $path .");
        continue;
    }
    $upaths[$url]=$path;
}

//exit();

/////////////////////////////////////////////////////// Setup the downloader ///////////////////////////////////////////////////////

$chunks = array_chunk( $upaths, $chunksize, true);


// Unfortunately the PHP garbage collector does not handle well 
// long running processes :-|, // and a single process will leak memory.
//
// To solve this, just fork a new child process for each chunk, then wait 
// for the download of the urls in the chunk by the child process.
//
// This ensures that the used resources by the child process
// (memory, sockets and so on) are returned at its exit to the OS, 
// and thus will be recycled.
foreach($chunks as $c){
    
    $pid = pcntl_fork();
    if ($pid == -1) {
        die('could not fork');
    } else if ($pid) {
        __tlog("PARENT: waiting for child with pid: $pid");
        pcntl_waitpid($pid, $status, 0);
    } else {

        $mypid = posix_getpid();
        __tlog("CHILD PID {$mypid}: start");        
        if(1){
            $d = new ParallelProxyDownloader($proxies);
            $d->referrer = $referrer;
            $d->minimumdbglevel = $sparsity;
            $d->add_many_urls($c);
            $d->start_all();
            unset( $d );
        }        
        $s = rand( $chunksleepmin, $chunksleepmax );
        __tlog("CHILD PID {$mypid}: Sleeping for $s seconds between chunks");
        usleep( $s*1000000 );
        __tlog("CHILD PID {$mypid}: exiting");
        exit();
        
    }    
}
